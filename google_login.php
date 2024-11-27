<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('929281643992-rl96q8t6rntehvr47890srodr3j4meok.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/PAP/callback.php');
$client->addScope('email');
$client->addScope('profile');

$authUrl = $client->createAuthUrl(); // Gera a URL de autenticação
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL)); // Redireciona o utilizador
exit();
?>
