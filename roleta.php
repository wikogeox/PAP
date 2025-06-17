<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Você precisa estar logado para acessar esta funcionalidade.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Obter saldo atual
$stmt = $liga->prepare("SELECT saldo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

// Se for um fetch AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['aposta'], $data['cor_apostada'], $data['resultado'], $data['valor_ganho'], $data['numero_sorteado'])) {
        echo json_encode(["status" => "error", "message" => "Dados inválidos."]);
        exit;
    }

    $aposta = floatval($data['aposta']);
    $cor_apostada = $data['cor_apostada'];
    $resultado = $data['resultado'];
    $valor_ganho = floatval($data['valor_ganho']); // Aqui é a ODD
    $numero_sorteado = intval($data['numero_sorteado']);
    $data_hora = date('Y-m-d H:i:s');

    if ($saldo < $aposta) {
        echo json_encode(["status" => "error", "message" => "Saldo insuficiente."]);
        exit;
    }

    // Calcula novo saldo 
    if ($resultado === 'win') {
        $ganho_total = $valor_ganho; // Já é aposta * odd
        $saldo_final = $saldo + $ganho_total; 
        $valor_para_log = $ganho_total;
    } else {
        $saldo_final = $saldo - $aposta;
        $valor_para_log = 0;
    }


  // Atualiza o saldo
    $update = $liga->prepare("UPDATE users SET saldo = ? WHERE id = ?");
    $update->bind_param("di", $saldo_final, $user_id);
    $update->execute();
    $update->close();

    // Regista nas log 
    $stmt = $liga->prepare("INSERT INTO roleta_logs (user_id, aposta, cor_apostada, resultado, valor_ganho, numero_sorteado, data_hora) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssdis", $user_id, $aposta, $cor_apostada, $resultado, $valor_para_log, $numero_sorteado, $data_hora);

    file_put_contents("roleta_debug.txt", "Valores recebidos: " . print_r([
        'user_id' => $user_id,
        'aposta' => $aposta,
        'cor_apostada' => $cor_apostada,
        'resultado' => $resultado,
        'valor_para_log' => $valor_para_log,
        'numero_sorteado' => $numero_sorteado,
        'data_hora' => $data_hora
    ], true), FILE_APPEND);

    if (!$stmt->execute()) {
        file_put_contents("roleta_debug.txt", "Erro ao inserir log: " . $stmt->error . "\n", FILE_APPEND);
    } else {
        file_put_contents("roleta_debug.txt", "Log inserido com sucesso\n", FILE_APPEND);
    }

    $stmt->close();

    echo json_encode(["status" => "success", "novo_saldo" => $saldo_final]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Roleta</title>
    <link rel="stylesheet" href="css/roleta.css">
</head>
<body>
    <script>
        const userId = <?= json_encode($user_id); ?>;
        const initialBalance = <?= json_encode($saldo); ?>;
    </script>
    <script src="roleta.js"></script>
</body>
</html>
