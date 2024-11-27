<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('929281643992-dembrs2r5b7p3uorvgnoidai32th8dop.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/PAP/google_register_callback.php');
$client->addScope('email');
$client->addScope('profile');

// Adiciona o parâmetro para forçar a seleção de uma nova conta Google
$client->setPrompt('select_account');

// Revoga qualquer token anterior antes de gerar o URL de autenticação
$client->revokeToken();

// Gera o URL de autenticação do Google
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();
?>
