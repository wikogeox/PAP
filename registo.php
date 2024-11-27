<?php
// Inicia a sessão
session_start();

// Inclui a configuração da base de dados
include 'config.php';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados do formulário
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $confirmEmail = trim($_POST['confirmEmail']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']); // Para confirmar que as senhas coincidem

    // Verifica se todos os campos foram preenchidos
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($confirmEmail)) {
        echo "<script>alert('Todos os campos são obrigatórios.'); window.location.href = 'registo.html';</script>";
        exit();
    }

    // Verifica se o email é válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('O email fornecido é inválido.'); window.location.href = 'registo.html';</script>";
        exit();
    }

    // Verifica se os emails coincidem
    if ($email !== $confirmEmail) {
        echo "<script>alert('Os emails não coincidem.'); window.location.href = 'registo.html';</script>";
        exit();
    }

    // Verifica se as senhas coincidem
    if ($password !== $confirmPassword) {
        echo "<script>alert('As senhas não coincidem.'); window.location.href = 'registo.html';</script>";
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

    // Criptografa a senha antes de armazená-la
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insere o novo utilizador na tabela `users`
    $sql_inserir = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
    $stmt_inserir = $liga->prepare($sql_inserir);
    $stmt_inserir->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt_inserir->execute()) {
        echo "<script>alert('Utilizador criado com sucesso!'); window.location.href = 'login.html';</script>";
    } else {
        echo "<script>alert('Erro ao criar o utilizador. Tente novamente.'); window.location.href = 'registo.html';</script>";
    }
}
?>
