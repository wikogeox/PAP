<?php
session_start();
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["success" => false, "error" => "Usuário não autenticado."]));
}

if ($data) {
    $user_id = intval($data['user_id']);
    $aposta = floatval($data['aposta']);
    $cor_apostada = $data['cor_apostada'];
    $resultado = $data['resultado'];
    $valor_ganho = floatval($data['valor_ganho']);
    $numero_sorteado = intval($data['numero_sorteado']);
    $data_hora = date('Y-m-d H:i:s');

    $stmt = $liga->prepare("INSERT INTO roleta_logs (user_id, aposta, cor_apostada, resultado, valor_ganho, numero_sorteado, data_hora) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $aposta, $cor_apostada, $resultado, $valor_ganho, $numero_sorteado, $data_hora]);

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Dados inválidos."]);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>Roleta</title>
	<meta name="title" content="Javascript Roulette">
	<meta name="description" content="A fully functioning roulette game made using Javascript and CSS">
	<meta name="author" content="ozboware">
	<meta property="og:url" content="/">
	<meta property="og:title" content="Javascript Roulette">
	<meta property="og:description" content="A fully functioning roulette game made using Javascript and CSS">
	<link rel="stylesheet" href="css/roleta.css" >
</head>

<body>
	<script src="roleta.js"></script>
</body>