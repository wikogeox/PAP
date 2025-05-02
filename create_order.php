<?php
session_start();
include 'config.php';
require 'vendor/autoload.php';

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

header('Content-Type: application/json');

// Lê o valor enviado pelo fetch()
$input = json_decode(file_get_contents('php://input'), true);
$valor = number_format(floatval($input['quantia'] ?? 0), 2, '.', '');

if ($valor <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Quantia inválida']);
    exit;
}

// Guarda na sessão para depois creditar no capture
$_SESSION['quantia_paypal'] = $valor;

// Inicializa PayPal SDK
$env    = new SandboxEnvironment(PAYPAL_CLIENT_ID, PAYPAL_SECRET);
$client = new PayPalHttpClient($env);

// Monta a ordem
$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
    "intent"         => "CAPTURE",
    "purchase_units" => [[
        "amount"      => ["currency_code" => "EUR", "value" => $valor],
        "description" => "Depósito na carteira virtual"
    ]],
];

try {
    $response = $client->execute($request);
    echo json_encode(['orderID' => $response->result->id]);
} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
}
