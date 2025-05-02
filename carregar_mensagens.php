<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$result = $liga->query("SELECT remetente, mensagem, data_envio FROM mensagens_suporte WHERE user_id = $user_id ORDER BY data_envio ASC");

while ($row = $result->fetch_assoc()) {
    $classe = $row['remetente'] === 'admin' ? 'mensagem-admin' : 'mensagem-user';
    echo "<div class='$classe'><strong>{$row['remetente']}:</strong> " . htmlspecialchars($row['mensagem']) . "<br><small>{$row['data_envio']}</small></div>";
}
