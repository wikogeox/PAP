<?php
session_start();

// Função para calcular o multiplicador
function calcularMultiplicador($bombs, $moves) {
    $multiplicadores = [
        1 => [1.00, 0.05],
        2 => [1.05, 0.10],
        3 => [1.10, 0.15],
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

// Gera o tabuleiro aleatório
if (isset($_POST['bombs'])) {
    $totalSquares = 25; // Total de quadrados
    $numBombs = (int)$_POST['bombs'];

    // Valida o número de bombas
    if ($numBombs > $totalSquares) {
        $numBombs = $totalSquares;
    }

    // Cria o tabuleiro
    $board = array_fill(0, $totalSquares, 'diamond');
    $bombPositions = array_rand(array_flip(range(0, $totalSquares - 1)), $numBombs);
    foreach ((array)$bombPositions as $pos) {
        $board[$pos] = 'bomb';
    }
    shuffle($board);

    $_SESSION['game_board'] = $board;
    $_SESSION['bet'] = $_POST['bet']; // Regista o montante apostado
    $_SESSION['bombs'] = $numBombs; // Regista o número de bombas
    $_SESSION['successful_moves'] = 0; // Reseta os movimentos bem-sucedidos
    $_SESSION['status'] = 'playing'; // Marca o estado como jogando
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
if (isset($_POST['action']) && $_POST['action'] === 'cashout') {
    $bet = (float)$_SESSION['bet'] ?? 0;
    $bombs = (int)$_SESSION['bombs'] ?? 0;
    $successfulMoves = (int)$_SESSION['successful_moves'] ?? 0;

    $winMultiplier = calcularMultiplicador($bombs, $successfulMoves); // Calcula o multiplicador
    $winnings = $bet * $winMultiplier;

    // Limpa apenas o tabuleiro e o estado do jogo
    unset($_SESSION['game_board'], $_SESSION['successful_moves']);
    $_SESSION['status'] = 'waiting'; // Reseta para estado inicial

    echo json_encode(['winnings' => $winnings, 'multiplier' => $winMultiplier]); // Retorna ganhos e multiplicador
    exit;
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo Mines</title>
    <link rel="stylesheet" href="css/mines.css">
</head>
<body>
    <div class="mines-game">
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
            <button>+100</button>
            <button>1/2</button>
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
            const minValue = 1;
            betInput.value = minValue;
        }

        function updateBet(amount) {
            const betInput = document.getElementById("bet-amount");
            let currentBet = parseFloat(betInput.value) || 0;
            currentBet += amount;
            betInput.value = currentBet >= 1 ? currentBet : 0;
        }

        function halfBet() {
            const betInput = document.getElementById("bet-amount");
            let currentBet = parseFloat(betInput.value) || 0;
            betInput.value = Math.floor(currentBet / 2);
        }

        function doubleBet() {
            const betInput = document.getElementById("bet-amount");
            let currentBet = parseFloat(betInput.value) || 0;
            betInput.value = currentBet * 2;
        }

        function maxBet() {
            const betInput = document.getElementById("bet-amount");
            const maxValue = 10000;
            betInput.value = maxValue;
        }

        // Adiciona eventos aos botões de apostas
        document.querySelectorAll(".bet-controls button").forEach((button, index) => {
            button.addEventListener("click", () => {
                switch (index) {
                    case 0: minBet(); break;
                    case 1: updateBet(10); break;
                    case 2: updateBet(100); break;
                    case 3: halfBet(); break;
                    case 4: doubleBet(); break;
                    case 5: maxBet(); break;
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
            startGameButton.disabled = !(betValue > 0 && bombsValue > 0);
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
                }).then(() => {
                    resetBoard();
                    isGameActive = true;
                    startGameButton.textContent = "Cashout (1.00x)";
                });
            } else {
                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=cashout`
                })
                .then(response => response.json())
                .then(({ winnings, multiplier }) => {
                    alert(`Você ganhou: ${winnings.toFixed(2)}! (Multiplicador: ${multiplier.toFixed(2)}x)`);
                    resetBoard();
                    isGameActive = false;
                    startGameButton.textContent = "Iniciar Jogo";
                });
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
    });
    </script>
</body>
</html>
