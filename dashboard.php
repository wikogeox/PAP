<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') exit;

// Total de usuários
$result = $liga->query("SELECT COUNT(*) FROM users");
$totalUsers = $result->fetch_row()[0];

// Usuários online (últimos 5 minutos)
$onlineThreshold = time() - 300;
$result = $liga->query("SELECT COUNT(*) FROM users WHERE last_active >= $onlineThreshold");
$onlineUsers = $result->fetch_row()[0];

// Lucro total do Plinko
$result = $liga->query("SELECT SUM(bet_amount - win_amount) FROM plinko_logs");
$plinkoLucro = $result->fetch_row()[0] ?? 0;

// Lucro total da Roleta
$result = $liga->query("SELECT SUM(aposta - valor_ganho) FROM roleta_logs");
$roletaLucro = $result->fetch_row()[0] ?? 0;

// Lucro total do Mines
$result = $liga->query("SELECT SUM(bet_amount - winnings) FROM mines_logs");
$minesLucro = $result->fetch_row()[0] ?? 0;

// Soma total dos lucros
$totalLucro = $plinkoLucro + $roletaLucro + $minesLucro;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/dashboard.css">
    <title>Dashboard</title>
</head>

<body>
    <div class="main-dashboard">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <h2>Admin</h2>
            <ul>
                <li onclick="mostrarSecao('estatisticas')">📊 Estatísticas</li>
                <li onclick="mostrarSecao('chat')">💬 Chat de Suporte</li>
            </ul>
        </div>

        <!-- Conteúdo principal -->
        <div class="admin-content">
            <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            <!-- Estatísticas -->
            <div id="secao-estatisticas" class="secao-visivel">
                <h2>Estatísticas do Sistema</h2>
                <div class="estatisticas-container">
                    <div class="card"><h3>Usuários Totais</h3><p><?= $totalUsers ?></p></div>
                    <div class="card"><h3>Online (últimos 5 min)</h3><p><?= $onlineUsers ?></p></div>
                    <div class="card"><h3>Lucro Total</h3><p>€<?= number_format($totalLucro, 2, ',', '.') ?></p></div>
                    <div class="card"><h3>Lucro Plinko</h3><p>€<?= number_format($plinkoLucro, 2, ',', '.') ?></p></div>
                    <div class="card"><h3>Lucro Roleta</h3><p>€<?= number_format($roletaLucro, 2, ',', '.') ?></p></div>
                    <div class="card"><h3>Lucro Mines</h3><p>€<?= number_format($minesLucro, 2, ',', '.') ?></p></div>
                </div>
            </div>

            <!-- Chat -->
            <div id="secao-chat" class="secao-oculta">
                <h2>Chat de Suporte</h2>
                <div class="dashboard-container">
                    <div class="sidebar">
                        <h3>Usuários</h3>
                        <ul id="usuarios-list"></ul>
                    </div>
                    <div class="chat-area">
                        <div id="chat-box" class="chat-box"></div>
                        <form id="chat-form">
                            <input type="text" id="mensagem" placeholder="Digite sua resposta..." autocomplete="off" required>
                            <button type="submit">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer">
            <div class="footer-content">
                <div class="footer-rights">
                    &copy; <span id="year"></span> BetKingdom. Todos os direitos reservados.
                </div>
            </div>
        </div>
    </footer>

    <script>
        function mostrarSecao(secao) {
            document.getElementById('secao-estatisticas').classList.add('secao-oculta');
            document.getElementById('secao-chat').classList.add('secao-oculta');

            if (secao === 'estatisticas') {
                document.getElementById('secao-estatisticas').classList.remove('secao-oculta');
            } else {
                document.getElementById('secao-chat').classList.remove('secao-oculta');
            }
        }

        function carregarUsuarios() {
            fetch('carregar_usuarios.php')
                .then(res => res.json())
                .then(data => {
                    let ul = document.getElementById('usuarios-list');
                    ul.innerHTML = '';
                    let notificacao = false;

                    data.forEach(user => {
                        let li = document.createElement('li');
                        li.innerText = 'Usuário ' + user.user_id;

                        if (parseInt(user.novas) > 0) {
                            li.innerHTML += ' <span class="notificacao"></span>';
                            notificacao = true;
                        }

                        li.onclick = () => carregarChat(user.user_id);
                        ul.appendChild(li);
                    });

                    // Atualiza a bolinha da sidebar
                    const suporteItem = document.querySelector('.admin-sidebar li:nth-child(2)');
                    if (notificacao) {
                        if (!suporteItem.querySelector('.notificacao')) {
                            suporteItem.innerHTML += ' <span class="notificacao"></span>';
                        }
                    } else {
                        const notif = suporteItem.querySelector('.notificacao');
                        if (notif) notif.remove();
                    }
                });
        }


        function carregarChat(user_id) {
            console.log("Carregando mensagens do user:", user_id); 
            fetch('carregar_mensagens_admin.php?user_id=' + user_id)
                .then(res => res.text())
                .then(data => {
                    const chatBox = document.getElementById('chat-box');
                    chatBox.innerHTML = data;
                    chatBox.setAttribute('data-user-id', user_id);
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }


        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const mensagem = document.getElementById('mensagem').value;
            const user_id = document.getElementById('chat-box').getAttribute('data-user-id');

            if (!user_id) {
                alert("Nenhum usuário selecionado!");
                return;
            }

            console.log("Enviando mensagem para user_id:", user_id); 

            fetch('enviar_mensagem_admin.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'mensagem=' + encodeURIComponent(mensagem) + '&user_id=' + user_id
            }).then(() => {
                document.getElementById('mensagem').value = '';
                carregarChat(user_id);
            });
        });

        setInterval(carregarUsuarios, 2000); // Atualiza a lista a cada 2 segundos
        setInterval(() => {
            const user_id = document.getElementById('chat-box').getAttribute('data-user-id');
            if (user_id) {
                carregarChat(user_id); // Atualiza o chat com o usuário atual
            }
        }, 2000); // A cada 2 segundos

        window.onload = carregarUsuarios;
    </script>

</body>
</html>
