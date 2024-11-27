<?php
include 'config.php';
require 'vendor/autoload.php';
session_start();

// Limpa tokens do Google armazenados na sessão
if (isset($_SESSION['google_token'])) {
    unset($_SESSION['google_token']);
    session_destroy(); // Reinicia a sessão
    session_start();
}

$client = new Google_Client();
$client->setClientId('929281643992-dembrs2r5b7p3uorvgnoidai32th8dop.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/PAP/google_register_callback.php');

if (isset($_GET['code'])) {
    try {
        // Obter o token de acesso e informações do utilizador
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        // Salva o token na sessão (opcional)
        $_SESSION['google_token'] = $token;

        $oauth = new Google\Service\Oauth2($client);
        $userInfo = $oauth->userinfo->get();

        // Verificar se o email já existe na base de dados
        $email = $userInfo->email;
        $name = $userInfo->name;
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $liga->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Utilizador já existe
            echo "<script>alert('Este email já está registado! Faça login.'); window.location.href = 'login.html';</script>";
            exit();
        } else {
            // Regista o novo utilizador
            $hashedPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT); // Gerar uma senha aleatória
            $sql_insert = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
            $stmt_insert = $liga->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt_insert->execute()) {
                echo "<script>alert('Registo efetuado com sucesso! Faça login.'); window.location.href = 'login.html';</script>";
                include 'enviaremail.php';
                enviarEmail($email, $name);
                exit();
            } else {
                echo "<script>alert('Erro ao registar o utilizador.'); window.location.href = 'registo.html';</script>";
                exit();
            }
        }
    } catch (Exception $e) {
        echo 'Erro ao autenticar com o Google: ' . $e->getMessage();
        exit();
    }
} else {
    echo 'Erro: Código de autenticação não encontrado.';
    exit();
}
?>
