<?php
session_start();
session_destroy(); // Termina a sessão
header("Location: index.php"); // Redireciona para a página inicial
exit;
?>
