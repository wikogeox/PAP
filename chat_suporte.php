<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>
        alert('Você precisa estar logado para acessar esta funcionalidade.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Chat de Suporte</title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
<div class="chat-container">
    <div id="chat-box" class="chat-box"></div>
    <form id="chat-form">
        <input type="text" id="mensagem" placeholder="Digite sua mensagem..." autocomplete="off" required>
        <button type="submit">Enviar</button>
    </form>
</div>

<script>
function carregarMensagens() {
    fetch('carregar_mensagens.php')
        .then(res => res.text())
        .then(data => {
            document.getElementById('chat-box').innerHTML = data;
            document.getElementById('chat-box').scrollTop = document.getElementById('chat-box').scrollHeight;
        });
}

document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const mensagem = document.getElementById('mensagem').value;
    fetch('enviar_mensagem.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'mensagem=' + encodeURIComponent(mensagem)
    }).then(() => {
        document.getElementById('mensagem').value = '';
        carregarMensagens();
    });
});

setInterval(carregarMensagens, 3000);
carregarMensagens();
</script>
</body>
</html>
