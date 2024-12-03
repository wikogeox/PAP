<?php
session_start();

// Verifica se o utilizador está autenticado
$autenticado = isset($_SESSION['user_id']); // Assume que 'user_id' é a chave usada para identificar o utilizador
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BetKingdom</title>
    <!-- Importação do Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <!-- Barra de Navegação -->
    <header class="navbar">
        <div class="logo">
            <img src="../imagens/logo.png" alt="BetKingdom Logo"> 
        </div>
        <nav class="nav-links">
            <a href="#"><i class="fas fa-dice"></i> Casino</a>
            <a href="#"><i class="fas fa-chart-bar"></i> Estatísticas</a>
            <a href="#"><i class="fas fa-question-circle"></i> FAQ</a>
            <a href="#"><i class="fas fa-headset"></i> Suporte</a>
        </nav>
        <div class="nav-buttons">
            <?php if ($autenticado): ?>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            <?php else: ?>
                <button class="login-btn" onclick="window.location.href='login.html'">Login</button>
                <button class="register-btn" onclick="window.location.href='registo.html'">Registar</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Barra Lateral com Botão de Expansão -->
    <aside class="icon-sidebar" onmouseover="expandSidebar()" onmouseout="collapseSidebar()">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i> 
        </div>
        <div class="icon">
            <i class="fas fa-trophy"></i> 
            <span class="icon-label">Minhas Vitórias</span>
        </div>
        <div class="icon">
            <i class="fas fa-gift"></i>
            <span class="icon-label">Ofertas</span>
        </div>
        <div class="icon">
            <i class="fas fa-chart-line"></i>
            <span class="icon-label">Torneios</span>
        </div>
        <div class="icon">
            <i class="fas fa-gamepad"></i>
            <span class="icon-label">Jogos Exclusivos</span>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <!-- Secção de Promoções e Jogos -->
        <section class="promotions">
            <!-- Banner de Convite -->
            <div class="background-style">
                <div class="invite-banner">
                    <h2>Convide um amigo e ganhe <span>dinheiro extra!</span></h2>
                    <p>Coloque um código de um amigo e também ganhe um extra.</p>
                    <button class="cod-btn">Código referencial</button>
                    <button class="share-btn">Partilhar</button>
                </div>
                <!-- Efeito de luz -->
                <div class="light-effect"></div>
            </div>

            <!-- Grelha de Jogos -->
            <h2>Jogos Exclusivos</h2>
            <div class="games-grid">
                <a href="roleta.html" class="game-card" style="background-image: url('imagens/roleta.png');">
                    <span>ROLETA</span>
                </a>
                <a href="mines.html" class="game-card" style="background-image: url('imagens/mines.png');">
                    <span>MINES</span>
                </a>
                <div class="game-card" style="background-image: url('image3.jpg');">
                    <span>PVP MINES</span>
                </div>
                <div class="game-card" style="background-image: url('image4.jpg');">
                    <span>PVP MINES</span>
                </div>
                <div class="game-card" style="background-image: url('image5.jpg');">
                    <span>PVP MINES</span>
                </div>
                <div class="game-card" style="background-image: url('image6.jpg');">
                    <span>PVP MINES</span>
                </div> 
            </div>
        </section>
    </div>

    <!-- Rodapé -->
    <footer>
        <div class="logo">
            <img src="../imagens/logo.png" alt="BetKingdom Logo"> <!-- Atualiza com o caminho correto -->
        </div>
        <div class="footer-info">
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <p>Vivamus posuere suscipit odio a suscipit. Vivamus elit arcu venenatis eget.</p>
        </div>
        <div class="footer-links">
            <ul>
                <li><a href="#" style="color: white;">Lorem Ipsum</a></li>
                <li><a href="#" style="color: white;">Lorem Ipsum</a></li>
                <li><a href="#" style="color: white;">Lorem Ipsum</a></li>
                <li><a href="#" style="color: white;">Lorem Ipsum</a></li>
            </ul>
        </div>
        <div class="footer-rights">
            &copy; All rights reserved 2021 - 2024
        </div>
    </footer>

    <!-- Códigos em JavaScript -->
    <script>
        function expandSidebar() {
            const sidebar = document.querySelector('.icon-sidebar');
            sidebar.style.width = '200px'; // Expande a barra lateral

            const mainContent = document.querySelector('.main-content');
            mainContent.style.marginLeft = '200px'; // Ajusta o conteúdo principal
        }

        function collapseSidebar() {
            const sidebar = document.querySelector('.icon-sidebar');
            sidebar.style.width = '60px'; // Retrai a barra lateral

            const mainContent = document.querySelector('.main-content');
            mainContent.style.marginLeft = '60px'; // Ajusta o conteúdo principal
        }
    </script>

</body>
</html>
