<?php
session_start();
include 'config.php';
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

header('Content-Type: application/json');

// ID da ordem vindo do JS
$orderId = $_GET['orderID'] ?? '';
if (!$orderId || !isset($_SESSION['user_id'], $_SESSION['quantia_paypal'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

// Inicializa PayPal SDK
$env    = new SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_SECRET);
$client = new PayPalHttpClient($env);

$request = new OrdersCaptureRequest($orderId);
$request->prefer('return=representation');

try {
    $resp = $client->execute($request);
    if ($resp->result->status === 'COMPLETED') {
        // Atualiza saldo na base de dados
        $valor = $_SESSION['quantia_paypal'];
        $stmt  = $liga->prepare("UPDATE users SET saldo = saldo + ? WHERE id = ?");
        $stmt->bind_param("di", $valor, $_SESSION['user_id']);
        $stmt->execute();
        unset($_SESSION['quantia_paypal']);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Status: ' . $resp->result->status]);
    }
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}
