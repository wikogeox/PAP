<?php
session_start();

include 'config.php'; // Inclui a conexão com a base de dados

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Você precisa estar logado para acessar esta funcionalidade.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Obter saldo do utilizador
$sql = "SELECT saldo FROM users WHERE id = ?";
$stmt = $liga->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$saldo = $user['saldo'] ?? 0;

// Função para calcular o multiplicador
function calcularMultiplicador($bombs, $moves) {
    $multiplicadores = [
        1 => [1.00, 0.02],
        2 => [1.05, 0.05],
        3 => [1.10, 0.10],
        5 => [1.25, 0.25],
        10 => [1.50, 0.50],
        15 => [2.25, 0.75],
        20 => [4.00, 1.00],
    ];

    if (!isset($multiplicadores[$bombs])) {
        return 1.0; // Retorna 1x se o número de bombas não estiver na lista
    }

    $inicial = $multiplicadores[$bombs][0];
    $incremento = $multiplicadores[$bombs][1];

    return $inicial + ($moves * $incremento);
}

// Início do jogo (Criação do tabuleiro e atualização do saldo)
if (isset($_POST['bombs'], $_POST['bet'])) {
    $totalSquares = 25;
    $numBombs = (int)$_POST['bombs'];
    $bet = (float)$_POST['bet'];
    $userId = $_SESSION['user_id'];

    // Validar número de bombas
    if ($numBombs > $totalSquares) {
        $numBombs = $totalSquares;
    }

    // Buscar saldo do utilizador
    $stmt = $liga->prepare("SELECT saldo FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($saldo);
    $stmt->fetch();
    $stmt->close();

    if ($bet > $saldo) {
        echo json_encode(['error' => 'Saldo insuficiente.']);
        exit;
    }

    // Criar tabuleiro aleatório
    $board = array_fill(0, $totalSquares, 'diamond');
    $bombPositions = array_rand(range(0, $totalSquares - 1), $numBombs);
    $bombPositions = is_array($bombPositions) ? $bombPositions : [$bombPositions];
    foreach ($bombPositions as $pos) {
        $board[$pos] = 'bomb';
    }
    shuffle($board);

    // Atualizar saldo do utilizador na base de dados
    $novoSaldo = $saldo - $bet;
    $stmt = $liga->prepare("UPDATE users SET saldo = ? WHERE id = ?");
    $stmt->bind_param("di", $novoSaldo, $userId);
    $stmt->execute();
    $stmt->close();

    // Armazenar jogo na sessão
    $_SESSION['game_board'] = $board;
    $_SESSION['bet'] = $_POST['bet']; // Regista o montante apostado
    $_SESSION['bombs'] = $numBombs; // Regista o número de bombas
    $_SESSION['successful_moves'] = 0; // Reseta os movimentos bem-sucedidos
    $_SESSION['status'] = 'playing'; // Marca o estado como jogando

    echo json_encode(['success' => 'Jogo iniciado!', 'saldo' => $novoSaldo]);
    exit;
}


// Verifica a posição clicada
if (isset($_POST['square'])) {
    $squareIndex = (int)$_POST['square'];
    if (isset($_SESSION['game_board'][$squareIndex])) {
        if ($_SESSION['game_board'][$squareIndex] === 'diamond') {
            $_SESSION['successful_moves']++; // Incrementa as jogadas bem-sucedidas
        }
        echo $_SESSION['game_board'][$squareIndex];
    } else {
        echo 'error';
    }
    exit;
}

// Finaliza o jogo
// Caso o jogador perca
if (isset($_POST['action']) && $_POST['action'] === 'lose') {
    $bet = (float)($_SESSION['bet'] ?? 0);
    $bombs = (int)($_SESSION['bombs'] ?? 0);
    $successfulMoves = (int)($_SESSION['successful_moves'] ?? 0);
    $winnings = 0; // Nenhum prêmio se o jogador perdeu

    // Inserção na base de dados (tabela mines_logs) caso o jogador perca
    try {
        $stmt = $liga->prepare("INSERT INTO mines_logs (user_id, bet_amount, bombs, successful_moves, winnings, multiplier, resultado) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], 
            $bet,
            $bombs,
            $successfulMoves,
            $winnings,
            1,  // Multiplicador de 1 (não há ganho)
            'lose'  // Resultado "lose" para derrota
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao registrar a derrota no log do jogo: " . $e->getMessage());
    }

    echo json_encode(['status' => 'lose']);
    exit;
}

//Caso o jogador vença
if (isset($_POST['action']) && $_POST['action'] === 'cashout') {
    ob_start(); // Inicia buffer para evitar saída indesejada

    $bet = (float)($_SESSION['bet'] ?? 0);
    $bombs = (int)($_SESSION['bombs'] ?? 0);
    $successfulMoves = (int)($_SESSION['successful_moves'] ?? 0);

    $winMultiplier = calcularMultiplicador($bombs, $successfulMoves); 
    $winnings = $bet * $winMultiplier;

    // Determina o resultado
    $resultado = $winnings > 0 ? 'win' : 'lose';  // Se o jogador ganhou alguma coisa, é "win", senão "lose"

    // Inserção na base de dados (tabela mines_logs) caso o jogador vença
    try {
        $stmt = $liga->prepare("INSERT INTO mines_logs (user_id, bet_amount, bombs, successful_moves, winnings, multiplier, resultado) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], 
            $bet,
            $bombs,
            $successfulMoves,
            $winnings,
            $winMultiplier,
            $resultado  
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao registrar o log do jogo: " . $e->getMessage());
    }

    try {
        $stmt = $liga->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
        $stmt->execute([$winnings, $_SESSION['user_id']]);
    } catch (PDOException $e) {
        error_log("Erro ao atualizar saldo após cashout: " . $e->getMessage());
    }

    // Limpa sessão do jogo
    unset($_SESSION['game_board'], $_SESSION['successful_moves'], $_SESSION['bet'], $_SESSION['bombs']);
    $_SESSION['status'] = 'waiting';

    try {
        // Obter saldo atualizado do utilizador
        $stmt = $liga->prepare("SELECT saldo FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $saldoAtualizado = $row['saldo'] ?? 0;

    
        ob_end_clean(); // Garante que não há saída antes do JSON
        echo json_encode([
            'winnings' => $winnings,
            'multiplier' => $winMultiplier,
            'saldo' => $saldoAtualizado
        ]);
        exit;
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }    
}

// Verifica saldo e valida aposta
if (isset($_POST['bet'])) {
    $bet = (float)$_POST['bet'];
    $userId = $_SESSION['user_id'];

    // Garante que a aposta seja no mínimo 0.50
    if ($bet <= 0.50) {
        echo json_encode(['error' => 'A aposta mínima é €0.50.']);
        exit;
    }

    // Obtém o saldo do jogador da base de dados
    try {
        $stmt = $liga->prepare("SELECT saldo FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($saldo);
        $stmt->fetch();
        $stmt->close();
    
        if (!isset($saldo)) {
            echo json_encode(['error' => 'Utilizador não encontrado.']);
            exit;
        }
    
        $saldoAtual = (float)$saldo;
    
        if ($bet > $saldoAtual) {
            echo json_encode(['error' => 'Saldo insuficiente.']);
            exit;
        }
    
        $novoSaldo = $saldoAtual - $bet;
        $stmt = $liga->prepare("UPDATE users SET saldo = ? WHERE id = ?");
        $stmt->bind_param("di", $novoSaldo, $userId);
        $stmt->execute();
        $stmt->close();
    
        $_SESSION['bet'] = $bet;
    
        echo json_encode(['success' => 'Aposta registada com sucesso!', 'saldo' => $novoSaldo]);
        exit;
    
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro na base de dados: ' . $e->getMessage()]);
        exit;
    }    
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mines</title>
    <link rel="stylesheet" href="css/mines.css">
</head>
<body>
    <div class="mines-game">

    <p id="saldo-atual">Saldo: €<?php echo number_format($saldo, 2, '.', thousands_separator: '.'); ?></p>

        <!-- Grelha do jogo -->
        <div class="grid">
            <script>
                for (let i = 0; i < 25; i++) {
                    document.write(`<div class="square" data-index="${i}">?</div>`);
                }
            </script>
        </div>

        <!-- Área de apostas -->
        <div class="bet-controls">
            <p>Aposta:</p>
            <input type="number" id="bet-amount" placeholder="Montante a apostar" min="1">
            <button>Min</button>
            <button>+10</button>
            <button>½</button>
            <button>x2</button>
            <button>Max</button>
        </div>

        <!-- Seleção de bombas e botão para começar -->
        <div class="game-settings">
            <label for="bombs">Número de Bombas:</label>
            <select id="bombs">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="20">20</option>
            </select>
        </div>

        <div class="start-game">
            <button id="start-game" disabled>Iniciar Jogo</button>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Funções de apostas
        function minBet() {
            const betInput = document.getElementById("bet-amount");
            const minValue = 0.50;
            betInput.value = minValue.toFixed(2); 
            betInput.dispatchEvent(new Event('input'));
        }

        function updateBet(amount) {
            const betInput = document.getElementById("bet-amount");
            let currentBet = parseFloat(betInput.value) || 0;
            currentBet += amount;
            betInput.value = (currentBet >= 0.50 ? currentBet : 0.50).toFixed(2);
            betInput.dispatchEvent(new Event('input'));
        }

        function halfBet() {
            const betInput = document.getElementById("bet-amount");
            let currentBet = parseFloat(betInput.value) || 0;

            if (currentBet > 0.50) {
                currentBet = currentBet / 2;
                if (currentBet < 0.50) {
                    currentBet = 0.50;
                }
            }

            betInput.value = currentBet.toFixed(2);  
            betInput.dispatchEvent(new Event('input'));
        }

        function doubleBet() {
            const betInput = document.getElementById("bet-amount");
            const saldoElement = document.getElementById("saldo-atual");
            
            let currentBet = parseFloat(betInput.value) || 0;
            let saldo = parseFloat(saldoElement.innerText.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;

            // Se a aposta já for igual ou maior ao saldo, não faz nada
            if (currentBet >= saldo) {
                return;
            }

            // Multiplica por 2, mas não pode ultrapassar o saldo
            let newBet = currentBet * 2;
            if (newBet > saldo) {
                newBet = saldo;
            }

            betInput.value = newBet.toFixed(2);
            betInput.dispatchEvent(new Event('input'));
        }

        function maxBet() {
            const betInput = document.getElementById("bet-amount");
            const saldoElement = document.getElementById("saldo-atual");

            // Extrai apenas os números do saldo
            let saldoTexto = saldoElement.textContent.trim();
            let saldoNumerico = parseFloat(saldoTexto.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;

            betInput.value = saldoNumerico.toFixed(2);
            betInput.dispatchEvent(new Event('input'));
        }


        // Adiciona eventos aos botões de apostas
        document.querySelectorAll(".bet-controls button").forEach((button, index) => {
            button.addEventListener("click", () => {
                switch (index) {
                    case 0: minBet(); break;
                    case 1: updateBet(10); break;
                    case 2: halfBet(); break;
                    case 3: doubleBet(); break;
                    case 4: maxBet(); break;
                }
            });
        });

        const startGameButton = document.getElementById("start-game");
        const betInput = document.getElementById("bet-amount");
        const bombsSelect = document.getElementById("bombs");
        let isGameActive = false;

        // Validações para habilitar o botão "Iniciar Jogo"
        function validateStartGame() {
            const betValue = parseFloat(betInput.value);
            const bombsValue = bombsSelect.value;
            startGameButton.disabled = !(betValue >= 0.50 && bombsValue > 0);
        }

        // Evento de validação
        betInput.addEventListener("input", validateStartGame);
        bombsSelect.addEventListener("change", validateStartGame);

        // Função para limpar o tabuleiro
        function resetBoard() {
            document.querySelectorAll('.square').forEach(square => {
                square.innerHTML = '?';
                square.style.backgroundColor = '';
            });
        }

        // Função para iniciar o jogo
        startGameButton.addEventListener("click", () => {
            if (!isGameActive) {
                const numBombs = bombsSelect.value;
                const betValue = betInput.value;

                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `bombs=${numBombs}&bet=${betValue}`
                }).then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        resetBoard();
                        isGameActive = true;
                        startGameButton.textContent = `Cashout`;

                        // Atualizar saldo na interface
                        document.getElementById("saldo-atual").textContent = `Saldo: €${data.saldo.toFixed(2)}`;
                    }
                });

            } else {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=cashout'
                })
                .then(response => response.text()) 
                .then(text => {
                    console.log("Resposta do servidor:", text); 
                    return JSON.parse(text); // Converter para JSON
                })
                .then(({ winnings, multiplier, saldo }) => {
                    if (winnings !== undefined) {
                        alert(`Você ganhou: €${winnings.toFixed(2)}! (Multiplicador: ${multiplier.toFixed(2)}x)`);
                        const saldoNumerico = parseFloat(saldo) || 0;
                        document.getElementById("saldo-atual").textContent = `Saldo: €${saldoNumerico.toFixed(2)}`;
                        resetBoard();
                        isGameActive = false;
                        startGameButton.textContent = "Iniciar Jogo";
                    } else {
                        alert("Erro ao processar cashout. Tente novamente.");
                    }
                })
                .catch(error => console.error("Erro ao processar cashout:", error));
            }
        });

        // Evento para cada quadrado no jogo
        document.querySelectorAll('.square').forEach(square => {
            square.addEventListener('click', () => {
                if (isGameActive) {
                    const squareIndex = square.dataset.index;

                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `square=${squareIndex}`
                    })
                    .then(response => response.text())
                    .then(result => {
                        if (result === 'bomb') {
                            square.innerHTML = '💣';
                            square.style.backgroundColor = 'red';
                            setTimeout(() => {
                                alert("Você perdeu! Tente novamente.");
                                resetBoard();
                                isGameActive = false;
                                startGameButton.textContent = "Iniciar Jogo";

                                // Registra a derrota na base de dados
                                fetch('', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: 'action=lose'
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.error) {
                                        console.error('Erro ao registrar derrota:', data.error);
                                    }
                                });
                            }, 300); // Delay para mostrar a bomba antes
                        } else {
                            square.innerHTML = '💎';
                            square.style.backgroundColor = 'green';

                            // Atualiza o multiplicador dinamicamente após cada movimento bem-sucedido
                            fetch('', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `action=getMultiplier`
                            })
                            .then(response => response.json())
                            .then(({ multiplier }) => {
                                // Atualiza o texto do botão com o multiplicador correto
                                startGameButton.textContent = `Cashout (${multiplier.toFixed(2)}x)`;
                            });
                        }
                    });
                }
            });

        function revealBoard() {
            document.querySelectorAll('.square').forEach(square => {
                let value = square.dataset.value; // Obtém o valor armazenado
                if (value === 'bomb') {
                    square.innerHTML = '💣';
                    square.style.backgroundColor = '#A00';
                } else if (value === 'diamond') {
                    square.innerHTML = '💎';
                    square.style.backgroundColor = '#0A0';
                }
                square.style.opacity = '0.5';
                square.style.cursor = 'not-allowed';
            });
        }
    });
    </script>
</body>
</html>