<?php
include 'config.php';
require 'vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setClientId('929281643992-rl96q8t6rntehvr47890srodr3j4meok.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/PAP/callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Obter informações do utilizador
    $oauth = new Google\Service\Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    // Verifica se o utilizador existe na base de dados
    $email = $userInfo->email;
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Utilizador existe, faz o login
        $user = $result->fetch_assoc();
        session_regenerate_id(true);
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['sessao_id'] = session_id();
        header('Location: index.php');
        exit();
    } else {
        // Utilizador não existe, redireciona de volta para o login com mensagem de erro
        header('Location: login.html?error=user_not_found');
        exit();
    }
} else {
    // Erro ao autenticar
    header('Location: login.html?error=auth_failed');
    exit();
}
?>
