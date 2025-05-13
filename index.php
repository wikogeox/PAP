<?php
session_start();
include 'config.php';

// Verifica se o utilizador está autenticado
$autenticado = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null; // Obtém o ID do utilizador autenticado

$referral_code = ''; // Valor padrão caso não tenha código

if ($autenticado) {
    $stmt = $liga->prepare("SELECT referral_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $referral_code = $user['referral_code'];
    }
}

if (isset($_SESSION['user_id'])) {
    $stmt = $liga->prepare("UPDATE users SET last_active = ? WHERE id = ?");
    $stmt->execute([time(), $_SESSION['user_id']]);
}
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
            <img src="imagens/logo.png" alt="BetKingdom Logo"> 
        </div>
        <nav class="nav-links">
            <a href="index.php"><i class="fas fa-dice"></i> Casino</a>
            <a href="estatisticas.php"><i class="fas fa-chart-bar"></i> Estatísticas</a>
            <a href="faq.html"><i class="fas fa-question-circle"></i> FAQ</a>
            <a href="chat_suporte.php"><i class="fas fa-headset"></i> Suporte</a>
        </nav>
        <div class="nav-buttons">
            <?php if ($autenticado): ?>
                <button class="open-modal-btn" onclick="openModal()"><i class="fas fa-wallet"></i></button>
                <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            <?php else: ?>
                <button class="login-btn" onclick="window.location.href='login.html'">Login</button>
                <button class="register-btn" onclick="window.location.href='registo.html'">Registar</button>
            <?php endif; ?>
        </div>
    </header>


    <!-- Fundo escuro -->
    <div class="modal-backdrop" id="modal-backdrop" onclick="closeModal()"></div>

    <!-- Modal -->
    <div class="modal" id="modal">
        <iframe src="carteira.php" frameborder="0" style="width: 100%; height: 300px;"></iframe>
        <button class="close-modal-btn" onclick="closeModal()">Fechar</button>
    </div>

    <!-- Barra Lateral com Botão de Expansão -->
    <aside class="icon-sidebar" onmouseover="expandSidebar()" onmouseout="collapseSidebar()">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i> 
        </div>
        <div class="icon">
            <i class="fas fa-trophy"></i> 
            <a href="estatisticas.php">
                <span class="icon-label">Minhas Vitórias</span>
            </a>
        </div>

        <div class="icon dropdown-toggle" onclick="toggleDropdown()"> 
            <div class="dropdown-header">
                <i class="fas fa-gamepad"></i>
                <span class="icon-label">Jogos Exclusivos</span>
                <i class="fas fa-chevron-down dropdown-arrow"></i>
            </div>

            <div class="dropdown-menu" id="dropdown-menu">
                <a href="roleta.php">
                    <span>Roleta</span>
                </a>
                <a href="mines.php">
                    <span>Mines</span>
                </a>
                <a href="plinko.php">
                    <span>Plinko</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <!-- Secção de Promoções e Jogos -->
        <section class="promotions">
            <!-- Banner de Convite -->
            <div class="background-style">
            <!-- Banner de Convite -->
                <div class="invite-banner">
                    <h2>Convide um amigo e ganhe <span>dinheiro extra!</span></h2>
                    <p>Coloque um código de um amigo e também ganhe um extra.</p>
                    <button class="cod-btn" onclick="openReferralModal()">Código referencial</button>
                    <button class="share-btn" onclick="openReferralModal2()">Partilhar</button>
                    </div>
                <!-- Efeito de luz -->
                <div class="light-effect"></div>
            </div>

            <!-- Grelha de Jogos -->
            <h2>Jogos Exclusivos</h2>
            <div class="games-grid">
                <a href="roleta.php" class="game-card" style="background-image: url('imagens/roleta.png');">
                    <span>ROLETA</span>
                </a>
                <a href="mines.php" class="game-card" style="background-image: url('imagens/mines.png');">
                    <span>MINES</span>
                </a>
                <a href="plinko.php" class="game-card" style="background-image: url('imagens/plinko.png');">
                    <span>PLINKO</span>
                </a>

            </div>
        </section>
    </div>

    <!-- Rodape -->
    <footer>
    <div class="footer-container">
        <!-- Logo no canto esquerdo -->
        <div class="footer-logo">
            <img src="imagens/logo.png" alt="BetKingdom Logo">
        </div>

        <!-- Conteúdo centralizado -->
        <div class="footer-content">
            <!-- Links úteis -->
            <div class="footer-section footer-links">
                <h3>Links Úteis</h3>
                <ul>
                    <li><a href="sobre.html" target="_blank">Sobre nós</a></li>
                    <li><a href="termos.html" target="_blank">Termos de Serviço</a></li>
                    <li><a href="politicaDePrivacidade.html" target="_blank">Política de Privacidade</a></li>
                </ul>
            </div>

            <!-- Redes Sociais --> 
            <!-- From Uiverse.io by javierBarroso --> 
            <div class="social-login-icons">
                <div class="socialcontainer">
                    <a href="https://x.com/" class="icon social-icon-1-1" target="_blank">
                        <svg
                            viewBox="0 0 512 512"
                            height="1.7em"
                            xmlns="http://www.w3.org/2000/svg"
                            class="svgIcontwit"
                            fill="white"
                        >
                            <path
                            d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"
                            ></path>
                        </svg>
                    </a>
                    <a href="https://x.com/" class="social-icon-1" target="_blank">
                        <svg
                            viewBox="0 0 512 512"
                            height="1.7em"
                            xmlns="http://www.w3.org/2000/svg"
                            class="svgIcontwit"
                            fill="white"
                        >
                            <path
                            d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"
                            ></path>
                        </svg>
                    </a>
                </div>

                <div class="socialcontainer">
                    <a href="https://www.instagram.com/ivan_almeida_07/" class="icon social-icon-2-2" target="_blank">
                        <svg
                            fill="white"
                            class="svgIcon"
                            viewBox="0 0 448 512"
                            height="1.5em"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                            d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"
                            ></path>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/ivan_almeida_07/" class="social-icon-2" target="_blank">
                        <svg
                            fill="white"
                            class="svgIcon"
                            viewBox="0 0 448 512"
                            height="1.5em"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                            d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"
                            ></path>
                        </svg>
                    </a>
                </div>
            
                <div class="socialcontainer">
                    <a href="https://facebook.com/" target="_blank">
                        <div class="icon social-icon-3-3">
                            <svg 
                                viewBox="0 0 384 512" 
                                fill="white" 
                                height="1.6em" 
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path 
                                d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"
                                ></path>
                            </svg>
                        </div>
                    </a>
                    <a href="https://facebook.com/" target="_blank">
                        <div class="social-icon-3">
                            <svg 
                                viewBox="0 0 384 512" 
                                fill="white" 
                                height="1.6em" 
                                xmlns="http://www.w3.org/2000/svg">
                                <path 
                                d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"
                                ></path>
                            </svg>
                        </div>
                    </a>
                </div>

                <div class="socialcontainer">
                    <a href="https://github.com/wikogeox" target="_blank">
                        <div class="icon social-icon-4-4">
                            <svg 
                                fill="white" 
                                viewBox="0 0 496 512" 
                                height="1.6em">
                                <path 
                                d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8z"
                                ></path>
                            </svg>
                        </div>
                    </a>
                    <a href="https://github.com/wikogeox" target="_blank">
                        <div class="social-icon-4">
                            <svg 
                                fill="white" 
                                viewBox="0 0 496 512" 
                                height="1.6em">
                                <path 
                                d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8z"
                                ></path>
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Contato -->
            <div class="footer-section footer-contact">
                <h3>Contato</h3>
                <p>Email: suporte@betkingdom.com</p>
                <p>Telefone: +351 913 765 591</p>
            </div>
        </div>
    </div>

    <!-- Direitos autorais -->
    <div class="footer-rights">
        &copy; <span id="year"></span> BetKingdom. Todos os direitos reservados.
    </div>
</footer>

<!-- Códigos em JavaScript -->
<script>
    // Atualiza o ano automaticamente
    document.getElementById("year").textContent = new Date().getFullYear();

    const autenticado = <?= json_encode($autenticado) ?>;

        function expandSidebar() {
            const sidebar = document.querySelector('.icon-sidebar');
            sidebar.classList.add('expanded');
            sidebar.style.width = '200px';

            const mainContent = document.querySelector('.main-content');
            if (mainContent) mainContent.style.marginLeft = '200px';
        }

        function collapseSidebar() {
            const sidebar = document.querySelector('.icon-sidebar');
            sidebar.classList.remove('expanded');
            sidebar.style.width = '60px';

            const mainContent = document.querySelector('.main-content');
            if (mainContent) mainContent.style.marginLeft = '60px';
        }

        function toggleDropdown() {
            const dropdown = document.querySelector('.dropdown-toggle');
            dropdown.classList.toggle('open');
        }

        // Abre o modal e aplica o efeito "blur"
        function openModal() {
            document.getElementById('modal').style.display = 'block';
            document.getElementById('modal-backdrop').style.display = 'block';
            document.getElementById('content').style.filter = 'blur(5px)';
        }

        // Fecha o modal e remove o efeito "blur"
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('modal-backdrop').style.display = 'none';
            document.getElementById('content').style.filter = 'none';
        }

        function openReferralModal() {
            if (!autenticado) {
                alert("Para usar o código referencial, você precisa estar logado.");
                window.location.href = "index.php"; 
                return;
            }
            document.getElementById("refer-code-modal").style.display = "block";
            document.getElementById("modal-backdrop-referral").style.display = "block";
            document.getElementById('content').style.filter = 'blur(5px)';
        }

        function closeReferralModal() {
            document.getElementById("refer-code-modal").style.display = "none";
            document.getElementById("modal-backdrop-referral").style.display = "none";
            document.getElementById('content').style.filter = 'none';
        }

        function openReferralModal2() {
            if (!autenticado) {
                alert("Para partilhar o seu código, você precisa estar logado.");
                window.location.href = "index.php"; 
                return;
            }
            document.getElementById("referral-modal").style.display = "block";
            document.getElementById("modal-backdrop-referral").style.display = "block";
            document.getElementById('content').style.filter = 'blur(5px)';
        }

        function closeReferralModal2() {
            document.getElementById("referral-modal").style.display = "none";
            document.getElementById("modal-backdrop-referral").style.display = "none";
            document.getElementById('content').style.filter = 'none';
        }

        function applyReferralCode() {
            let code = document.getElementById("referral-code").value;
            
            if (code.trim() === "") {
                alert("Por favor, insira um código!");
                return;
            }

            fetch('validar_codigo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'codigo=' + encodeURIComponent(code)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.mensagem);
                if (data.sucesso) {
                    closeReferralModal();
                    location.reload(); // Atualiza saldo
                }
            })
            .catch(error => console.error('Erro:', error));
        }

        function copyReferralCode() {
            let copyText = document.getElementById("referral-code2");

            if (!copyText || !copyText.value.trim()) {
                alert("O código está vazio.");
                console.log("Valor do input:", copyText ? copyText.value : "Elemento não encontrado");
                return;
            }

            copyText.select();
            copyText.setSelectionRange(0, 99999);

            navigator.clipboard.writeText(copyText.value.trim()).then(() => {
                alert("Código copiado: " + copyText.value);
            }).catch(err => {
                console.error("Erro ao copiar: ", err);
                alert("Erro ao copiar o código.");
            });
        }

        function shareOnWhatsApp() {
            let referralCode = document.getElementById("referral-code2").value.trim();

            if (!referralCode) {
                alert("O código está vazio.");
                return;
            }

            // 🔗 Link para a página onde o código pode ser inserido
            let referralLink = "http://localhost/PAP/index.php/referral?code=" + referralCode;

            // 📩 Mensagem personalizada
            let message = encodeURIComponent(
                "Queres ganhar um bónus extra? Usa o meu código referencial ao registares-te! 🎉\n\n" +
                "📌 Código: " + referralCode + "\n" +
                "🔗 Aplica aqui: " + referralLink
            );

            let whatsappURL = "https://wa.me/?text=" + message;

            window.open(whatsappURL, "_blank");
        }

    </script>


    <!-- Modal do Código Referencial -->
    <div id="refer-code-modal" class="modal">
        <div class="modal-content">
            <h2>Inserir Código Referencial</h2>
            <input type="text" id="referral-code" name="referral-code" class="referral-input" placeholder="Insira o código">
            <button class="referral-apply-btn" onclick="applyReferralCode()">Aplicar</button>
        </div>
        <button class="close-modal-btn" onclick="closeReferralModal()">Fechar</button>
    </div>

    <!-- Fundo escuro para o modal -->
    <div id="modal-backdrop-referral" class="modal-backdrop" onclick="closeReferralModal()"></div>

    <div id="referral-modal" class="modal">
        <div class="modal-content2">
            <h2>O teu Código Referencial</h2>

            <label for="referral-code2" class="referral-label">Partilha com os teus amigos!</label>
            <input type="text" id="referral-code2" name="referral-code" class="referral-input" value="<?= htmlspecialchars($referral_code) ?>" readonly>

            <div class="referral-buttons">
                <button onclick="copyReferralCode()" class="copy-btn">
                    <i class="fa-solid fa-copy"></i> Copiar
                </button>

                <button onclick="shareOnWhatsApp()" class="whatsapp-btn">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </button>
            </div>
        </div>

        <button class="close-modal-btn" onclick="closeReferralModal2()">Fechar</button>
    </div>

</body>
</html>
