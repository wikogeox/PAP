<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') exit;

if (isset($_POST['mensagem']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $mensagem = trim($_POST['mensagem']);

    if ($mensagem !== '') {
        $stmt = $liga->prepare("INSERT INTO mensagens_suporte (user_id, remetente, mensagem) VALUES (?, 'admin', ?)");
        $stmt->bind_param("is", $user_id, $mensagem);
        $stmt->execute();
        echo "ok"; 
    }
}
?>
