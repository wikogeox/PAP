<?php
session_start();
include 'config.php';
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

// 1) Verifica sessão válida
if (!isset($_SESSION['user_id'], $_SESSION['quantia_paypal'])) {
    header("Location: login.html");
    exit;
}

// 2) Configura PayPal SDK
$env    = new SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_SECRET);
$client = new PayPalHttpClient($env);

// 3) Obtém o ID da ordem (token) do query string
$orderId = $_GET['token'] ?? null;
if (!$orderId) {
    exit("Parâmetro de ordem inválido.");
}

try {
    // 4) Envia captura da Order
    $captureRequest  = new OrdersCaptureRequest($orderId);
    $captureRequest->prefer('return=representation');
    $captureResponse = $client->execute($captureRequest);

    // 5) Se sucesso, atualiza saldo
    if ($captureResponse->result->status === 'COMPLETED') {
        $valor = $_SESSION['quantia_paypal'];
        $stmt  = $liga->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
        $stmt->bind_param("di", $valor, $_SESSION['user_id']);
        $stmt->execute();
        unset($_SESSION['quantia_paypal']);
        header("Location: index.php");
        exit;
    } else {
        exit("Pagamento não concluído. Status: " . $captureResponse->result->status);
    }
} catch (Exception $ex) {
    exit("Erro ao capturar pagamento: " . $ex->getMessage());
}
