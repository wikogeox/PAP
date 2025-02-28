<?php
require 'vendor/autoload.php';

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

$paymentId = $_GET['paymentId'];
$payerId = $_GET['PayerID'];

$payment = Payment::get($paymentId, $apiContext);

$execution = new PaymentExecution();
$execution->setPayerId($payerId);

try {
    // Executa o pagamento
    $result = $payment->execute($execution, $apiContext);

    // Obter o valor do depósito
    $quantia = $_GET['quantia'];  // Passando o valor via GET ou POST, dependendo de como você estrutura

    // Atualize o saldo do utilizador na base de dados
    $sql = "UPDATE users SET saldo = saldo + ? WHERE id = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("di", $quantia, $_SESSION['user_id']);
    $stmt->execute();

    // Redirecionar de volta para a carteira
    header("Location: carteira.php");
    exit;
} catch (Exception $ex) {
    echo "Erro ao executar o pagamento: " . $ex->getMessage();
}
?>