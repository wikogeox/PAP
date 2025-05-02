<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !isset($_GET['user_id'])) exit;

$user_id = intval($_GET['user_id']);

// Marcar como visualizadas as mensagens do user
$liga->query("UPDATE mensagens_suporte SET visualizada = 1 WHERE remetente = 'user' AND user_id = $user_id");

// Buscar todas as mensagens deste chat
$result = $liga->query("SELECT remetente, mensagem, data_envio FROM mensagens_suporte WHERE user_id = $user_id ORDER BY data_envio ASC");

while ($row = $result->fetch_assoc()) {
    $classe = $row['remetente'] === 'admin' ? 'mensagem-admin' : 'mensagem-user';
    echo "<div class='$classe'><strong>" . htmlspecialchars($row['remetente']) . ":</strong> " . 
         htmlspecialchars($row['mensagem']) . "<br><small>" . htmlspecialchars($row['data_envio']) . "</small></div>";
}
?>
    