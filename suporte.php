<?php
require 'config.php'; // Conexão com a base de dados

session_start();

// Definir variáveis padrão
$email_input_disabled = "";
$assunto_disabled = "";
$mensagem_disabled = "";

// Definir mensagem inicial
$mensagem = "";

// Verifica se o utilizador está logado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Recuperar o email do utilizador logado
    $stmt = $liga->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_email);
    $stmt->fetch();
    $stmt->close();
} else {
    // Se não estiver logado, define a mensagem e desativa o formulário
    $mensagem = "Você precisa estar logado para enviar um pedido.";
    $email_input_disabled = "readonly";
    $assunto_disabled = "disabled";
    $mensagem_disabled = "disabled";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $email = $_POST['email'] ?? '';
    $assunto = $_POST['assunto'] ?? '';
    $mensagemTexto = $_POST['mensagem'] ?? '';

    // Buscar o ID do usuário com base no email
    $stmtUser = $liga->prepare("SELECT id FROM users WHERE email = ?");
    $stmtUser->bind_param("s", $email);
    $stmtUser->execute();
    $stmtUser->bind_result($user_id);
    $stmtUser->fetch();
    $stmtUser->close();

    if ($user_id && !empty($assunto) && !empty($mensagemTexto)) {
        $status = 'Pendente'; // Definir status inicial
        $data_envio = date('Y-m-d H:i:s'); // Data e hora do envio

        // Inserir os dados na tabela suporte
        $stmt = $liga->prepare("INSERT INTO suporte (user_id, assunto, mensagem, status, data_envio) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $assunto, $mensagemTexto, $status, $data_envio);
        
        if ($stmt->execute()) {
            $mensagem = "Pedido enviado com sucesso!";
        } else {
            $mensagem = "Erro ao enviar pedido.";
        }
        $stmt->close();
    } else {
        $mensagem = "Preencha todos os campos corretamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte</title>
    <link rel="stylesheet" href="css/suporte.css">
</head>
<body>
    <header>
        <h1 class="suporte-title">Suporte</h1>
    </header>
    <main class="suporte-container">
        <form action="suporte.php" method="post" class="suporte-form">
            <label for="email">Email:</label>
            <!-- O email será preenchido automaticamente com o email do utilizador logado -->
            <input type="email" value="<?= isset($user_email) ? htmlspecialchars($user_email) : '' ?>" disabled>
            <input type="hidden" name="email" value="<?= isset($user_email) ? htmlspecialchars($user_email) : '' ?>">

            <label for="assunto">Assunto:</label>
            <input type="text" name="assunto" id="assunto" <?= $assunto_disabled ?> required>
            
            <label for="mensagem">Mensagem:</label>
            <textarea name="mensagem" id="mensagem" <?= $mensagem_disabled ?> required></textarea>
            
            <button type="submit" <?= ($email_input_disabled == "readonly") ? 'disabled' : '' ?>>Enviar</button>
        </form>
    </main>

    <!-- Modal para mostrar a mensagem de sucesso ou erro -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="modalMensagem"><?= $mensagem ?></p>
            <button onclick="window.location.href='index.php';">Ok</button>
        </div>
    </div>

    <script>
        // Mostrar o modal se houver mensagem
        const modal = document.getElementById("modal");
        const modalMensagem = document.getElementById("modalMensagem");
        const closeModal = document.querySelector(".close");

        <?php if (!empty($mensagem)): ?>
            modal.style.display = "block";
            modalMensagem.textContent = "<?= $mensagem ?>";
        <?php endif; ?>

        // Fechar o modal
        closeModal.onclick = function() {
            modal.style.display = "none";
            window.location.href = 'index.php'; // Redireciona para a página principal
        }

        // Fechar o modal se o usuário clicar fora da caixa de diálogo
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
                window.location.href = 'index.php'; // Redireciona para a página principal
            }
        }

        // Mostrar o modal de login quando o utilizador tentar interagir com os campos, caso não esteja logado
        const inputs = document.querySelectorAll('input, textarea');

        inputs.forEach(function(input) {
            input.addEventListener('click', function(event) {
                <?php if (empty($user_id)): ?>
                    modal.style.display = "block";
                    modalMensagem.textContent = "Você precisa estar logado para enviar um pedido.";
                    event.preventDefault();
                <?php endif; ?>
            });
        });
    </script>
</body>
</html>
