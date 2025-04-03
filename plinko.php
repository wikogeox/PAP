<?php
session_start();
include 'config.php'; // Conexão com a base de dados

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["success" => false, "error" => "Usuário não autenticado."]));
}

$user_id = $_SESSION['user_id'];

// Buscar saldo atual do usuário
$sql_saldo = "SELECT saldo FROM users WHERE id = ?";
$stmt_saldo = $liga->prepare($sql_saldo);
$stmt_saldo->bind_param("i", $user_id);
$stmt_saldo->execute();
$result_saldo = $stmt_saldo->get_result();
$user = $result_saldo->fetch_assoc();
$stmt_saldo->close();

if (!$user) {
    die(json_encode(["success" => false, "error" => "Erro ao obter saldo."]));
}

$saldo_atual = $user['saldo'];

// Se a requisição for POST (ou seja, uma aposta foi feita)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bet_amount = isset($_POST['bet_amount']) ? floatval($_POST['bet_amount']) : null;

    if ($bet_amount === null || $bet_amount <= 0) {
        die(json_encode(["success" => false, "error" => "Valor da aposta inválido."]));
    }

    // Verifica saldo do usuário antes da aposta
    if ($saldo_atual < $bet_amount) {
        die(json_encode(["success" => false, "error" => "Saldo insuficiente."]));
    }

    // Atualiza o saldo do usuário e obtém o saldo atualizado da base de dados
    $sql_update = "UPDATE users SET saldo = saldo - ? WHERE id = ?";
    $stmt_update = $liga->prepare($sql_update);
    $stmt_update->bind_param("di", $bet_amount, $user_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Busca novamente o saldo atualizado
    $sql_saldo = "SELECT saldo FROM users WHERE id = ?";
    $stmt_saldo = $liga->prepare($sql_saldo);
    $stmt_saldo->bind_param("i", $user_id);
    $stmt_saldo->execute();
    $result_saldo = $stmt_saldo->get_result();
    $user = $result_saldo->fetch_assoc();
    $stmt_saldo->close();

    if (!$user) {
        die(json_encode(["success" => false, "error" => "Erro ao obter saldo atualizado."]));
    }

    $novo_saldo = $user['saldo'];

    echo json_encode(["success" => true, "new_balance" => number_format($novo_saldo, 2, '.', '')]);
    
    exit();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Javascript Plinko</title>
    <meta name="title" content="Javascript Plinko">
    <meta name="description" content="A fully functioning Plinko game made using Javascript and CSS">
    <meta name="author" content="ozboware">
    <meta property="og:url" content="/">
    <meta property="og:title" content="Javascript Plinko">
    <meta property="og:description" content="A fully functioning Plinko game made using Javascript and CSS">
    <link rel="stylesheet" href="css/plinko.css" >
</head>

<body>
<div class="controls">
    <div></div>
    <div>
    </div>
    <div></div>
</div>
    <div class="main-container">
        <div class="content-wrapper">
            <!-- Seção de Aposta -->
            <div class="bet-container">
                <label for="saldo">Saldo</label>
                <input type="text" id="saldo" value="<?= number_format($saldo_atual, 2, '.', '') ?>" readonly>

                <label for="bet-amount">Aposta</label>
                <input type="number" id="bet-amount" value="0.20" min="0.20" step="0.20">

                <div class="multiplier-buttons">
                    <button id="half-bet">½</button>
                    <button id="double-bet">2×</button>
                </div>

                <div class="drop-container">
                    <div class="drop">
                        <button id="drop-button" type="button">Jogar</button>
						<input id="checkbox" type="checkbox" />
						<label for="checkbox">
							<div class="box">
								<svg class="checked" xmlns="http://www.w3.org/2000/svg" width="8px" height="8px" viewBox="0 0 24 24">
									<path fill="#ffffff" d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z" />
								</svg>
							</div>
						</label>
					</div>
				</div>
			</div>

            <!-- Seção da Pirâmide -->
            <div class="canvas-container">
                <canvas id="canvas"></canvas>
            </div>
        </div>
    </div>
</body>

<div class="notes">
    <div type="button" class="note" id="note-0"></div>
    <div type="button" class="note" id="note-1"></div>
    <div type="button" class="note" id="note-2"></div>
    <div type="button" class="note" id="note-3"></div>
    <div type="button" class="note" id="note-4"></div>
    <div type="button" class="note" id="note-5"></div>
    <div type="button" class="note" id="note-6"></div>
    <div type="button" class="note" id="note-7"></div>
    <div type="button" class="note" id="note-8"></div>
    <div type="button" class="note" id="note-9"></div>
    <div type="button" class="note" id="note-10"></div>
    <div type="button" class="note" id="note-11"></div>
    <div type="button" class="note" id="note-12"></div>
    <div type="button" class="note" id="note-13"></div>
    <div type="button" class="note" id="note-14"></div>
    <div type="button" class="note" id="note-15"></div>
    <div type="button" class="note" id="note-16"></div>
</div>
    
<script src="https://cdnjs.cloudflare.com/ajax/libs/tone/14.8.49/Tone.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/matter-js/0.17.1/matter.min.js"></script>
<script src="plinko.js"></script>

</body>
</html>
