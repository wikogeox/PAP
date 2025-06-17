<?php
session_start();
require 'vendor/autoload.php';
include 'config.php';

use GuzzleHttp\Client;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Não autenticado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$valor = floatval($data['valor'] ?? 0);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email || $valor <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos.']);
    exit;
}

// Verificar saldo do utilizador
$user_id = $_SESSION['user_id'];
$stmt = $liga->prepare("SELECT saldo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($saldo);
$stmt->fetch();
$stmt->close();

if ($valor > $saldo) {
    echo json_encode(['status' => 'error', 'message' => 'Saldo insuficiente.']);
    exit;
}

// Obter token de acesso OAuth2
$client = new Client();
try {
    $auth = $client->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
        'auth' => [PAYPAL_CLIENT_ID, PAYPAL_SECRET],
        'form_params' => ['grant_type' => 'client_credentials'],
    ]);

    $token = json_decode($auth->getBody(), true)['access_token'];
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao obter token PayPal: ' . $e->getMessage()]);
    exit;
}

// Criar payout
$payoutData = [
    "sender_batch_header" => [
        "sender_batch_id" => uniqid(),
        "email_subject"   => "Recebeste um pagamento!",
        "email_message"   => "Olá! Recebeste um levantamento da plataforma."
    ],
    "items" => [[
        "recipient_type" => "EMAIL",
        "amount"         => [
            "value"         => number_format($valor, 2, '.', ''),
            "currency"      => "EUR"
        ],
        "note"           => "Levantamento da conta",
        "receiver"       => $email,
        "sender_item_id" => uniqid("item_")
    ]]
];

try {
    $response = $client->post('https://api-m.sandbox.paypal.com/v1/payments/payouts', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json'
        ],
        'json' => $payoutData
    ]);

    // Atualiza saldo
    $stmt = $liga->prepare("UPDATE users SET saldo = saldo - ? WHERE id = ?");
    $stmt->bind_param("di", $valor, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Levantamento realizado com sucesso!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro no envio do pagamento: ' . $e->getMessage()]);
}
