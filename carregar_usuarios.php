<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') exit;

$result = $liga->query("
    SELECT user_id, 
        SUM(CASE WHEN visualizada = 0 AND remetente = 'user' THEN 1 ELSE 0 END) AS novas
    FROM mensagens_suporte 
    GROUP BY user_id
");

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);
?>
