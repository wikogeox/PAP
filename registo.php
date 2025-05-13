<?php
// Inicia a sessão
session_start();

// Inclui a configuração da base de dados
include 'config.php';

// Inclui a função de envio de email
include 'enviaremail.php';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    // Obtém os dados do formulário
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']); // Para confirmar que as passwords coincidem

    // Verifica se todos os campos foram preenchidos
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo "<script>alert('Todos os campos são obrigatórios.'); window.location.href = 'registo.html';</script>";
        exit();
    }

    // Verifica se o email é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('O email fornecido é inválido.'); window.location.href = 'registo.html';</script>";
        exit();
    }

    // Verifica se as passwords coincidem
    if ($password !== $confirmPassword) {
        echo "<script>alert('As passwords não coincidem.'); window.location.href = 'registo.html';</script>";
        exit();
    }

    // Verifica se o username ou email já existe na base de dados
    $sql_verificar = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt_verificar = $liga->prepare($sql_verificar);
    $stmt_verificar->bind_param("ss", $username, $email);
    $stmt_verificar->execute();
    $resultado = $stmt_verificar->get_result();

    if ($resultado->num_rows > 0) {
        echo "<script>alert('Erro: Username ou email já existe.'); window.location.href = 'registo.html';</script>";
        exit();
    }

    // Criptografa a password antes de armazená-la
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    function generateReferralCode($length = 8) {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
    }
    
    // Insere o novo utilizador na tabela `users`
    $referral_code = generateReferralCode();
    $sql_inserir = "INSERT INTO users (username, email, password_hash, referral_code) VALUES (?, ?, ?, ?)";
    $stmt_inserir = $liga->prepare($sql_inserir);
    $stmt_inserir->bind_param("ssss", $username, $email, $hashedPassword, $referral_code);

    if ($stmt_inserir->execute()) {
        // Chama a função para enviar o e-mail de boas-vindas
        $emailEnviado = enviarEmail($email, $username);

        if ($emailEnviado) {
            echo "<script>alert('Utilizador criado com sucesso! Um e-mail de boas-vindas foi enviado.'); window.location.href = 'login.html';</script>";
        } else {
            // Registra erro no console e no servidor
            $erroEmail = "Erro ao enviar e-mail para $email";
            error_log($erroEmail . PHP_EOL, 3, "log.txt");
            echo "<script>console.error('$erroEmail'); alert('Conta criada, mas houve um problema ao enviar o e-mail. Verifique sua caixa de entrada ou spam.'); window.location.href = 'login.html';</script>";
        }
    } else {
        echo "<script>alert('Erro ao criar o utilizador. Tente novamente.'); window.location.href = 'registo.html';</script>";
    }

?>
