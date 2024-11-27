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
    <style>
        /* Estilo do rodapé */
        footer {
            background-color: #1c1c1c;
            color: #fff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }

        footer .logo img {
            max-height: 60px;
        }

        footer .footer-info {
            flex: 1;
            padding: 0 20px;
        }

        footer .footer-info p {
            margin: 5px 0;
        }

        footer .footer-rights {
            font-size: 12px;
            opacity: 0.7;
        }

        footer .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: right;
        }

        footer .footer-links ul li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <!-- Barra de Navegação -->
    <header class="navbar">
        <div class="logo">
            <img src="logo.png" alt="BetKingdom Logo"> <!-- Atualiza com o caminho correto -->
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

    <!-- Conteúdo Principal -->
    <div class="main-content">
    
        <!-- Barra Lateral com Botão de Expansão -->
        <aside class="icon-sidebar" onmouseover="expandSidebar()" onmouseout="collapseSidebar()">
            <div class="menu-toggle">
                <i class="fas fa-bars"></i> <!-- Ícone das três barrinhas -->
            </div>
            <div class="icon">
                <i class="fas fa-trophy"></i> <!-- Ícone representativo -->
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


        <!-- Secção de Promoções e Jogos -->
        <section class="promotions">
            <!-- Banner de Convite -->
            <div class="invite-banner">
                <h2>Invite a friend and earn <span>passive income!</span></h2>
                <p>Learn ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <button>Referral Code</button>
                <button>Share</button>
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
        </section>
    </div>

    <!-- Rodapé -->
    <footer>
        <div class="logo">
            <img src="logo.png" alt="BetKingdom Logo"> <!-- Atualiza com o caminho correto -->
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
        }

        function collapseSidebar() {
            const sidebar = document.querySelector('.icon-sidebar');
            sidebar.style.width = '50px'; // Retrai a barra lateral
        }
    </script>

</body>
</html>
