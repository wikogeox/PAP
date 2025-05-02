<?php
session_start();
require 'config.php';

if (isset($_SESSION['user_id']) && isset($_POST['mensagem'])) {
    $user_id = $_SESSION['user_id'];
    $mensagem = trim($_POST['mensagem']);
    if ($mensagem !== '') {
        $stmt = $liga->prepare("INSERT INTO mensagens_suporte (user_id, remetente, mensagem) VALUES (?, 'user', ?)");
        $stmt->bind_param("is", $user_id, $mensagem);
        $stmt->execute();
    }
}
