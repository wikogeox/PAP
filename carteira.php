<?php
session_start();
include 'config.php'; // Conexão com a base de dados
require 'vendor/autoload.php';

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;

// Configuração do PayPal
$apiContext = new ApiContext(
    new OAuthTokenCredential(
        PAYPAL_CLIENT_ID,
        PAYPAL_SECRET
    )
);
$apiContext->setConfig([
    'mode' => PAYPAL_MODE,
]);

// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Obter saldo do utilizador
$sql = "SELECT saldo FROM users WHERE id = ?";
$stmt = $liga->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$saldo = $user['saldo'] ?? 0;

// Adicionar dinheiro na conta via PayPal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantia = floatval($_POST['quantia']);
    if ($quantia > 0) {
        // Criar o pagamento com o PayPal
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setCurrency('EUR')  // Definir a moeda, aqui usamos Euros
               ->setTotal($quantia);  // O valor a ser depositado

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setDescription('Depósito na carteira virtual');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl('http://localhost/PAP/retorno_api_paypal.php')  // URL após pagamento bem-sucedido
                     ->setCancelUrl('http://localhost/PAP/index.php');  // URL se o pagamento for cancelado

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction]) // Aqui é onde você deve passar um array com transações
                ->setRedirectUrls($redirectUrls);

        try {
            // Criar o pagamento com a API do PayPal
            $payment->create($apiContext);

            // Redirecionar para o PayPal para que o utilizador finalize o pagamento
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() == 'approval_url') {
                    header("Location: " . $link->getHref());
                    exit;
                }
            }
        } catch (Exception $ex) {
            echo "Erro ao criar o pagamento: " . $ex->getMessage();
            exit(1);
        }
    } else {
        $erro = "Insira uma quantia válida.";
    }
}


// Verifica se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Obter saldo do utilizador
$sql = "SELECT saldo FROM users WHERE id = ?";
$stmt = $liga->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$saldo = $user['saldo'] ?? 0;

// Adicionar dinheiro na conta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantia = floatval($_POST['quantia']);
    if ($quantia > 0) {
        $sql = "UPDATE users SET saldo = saldo + ? WHERE id = ?";
        $stmt = $liga->prepare($sql);
        $stmt->bind_param("di", $quantia, $user_id);
        $stmt->execute();
        header("Location: carteira.php");
        exit;
    } else {
        $erro = "Insira uma quantia válida.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carteira Virtual</title>
    <link rel="stylesheet" href="css/carteira.css">
</head>
<body>
    <h1>Carteira Virtual</h1>
    <p>Saldo atual: <strong>€<?php echo number_format($saldo, 2, ',', '.'); ?></strong></p>
    <form action="carteira.php" method="POST">
        <label for="quantia">Adicionar dinheiro:</label>
        <input type="number" id="quantia" name="quantia" step="0.01" placeholder="0.00" required>
        <script src="https://www.paypal.com/sdk/js?client-id=BAAGzTmGJJlvT3fu0jXEvMuJyWdmZxgeD6ZYgyrbME4o0klJNg_PTcAmS1PV1f4fkQcN_6_cw7dIpKnHEs&components=hosted-buttons&disable-funding=venmo&currency=EUR"></script>
<div id="paypal-container-LSP93GFU35MG8"></div>
<script>
  paypal.HostedButtons({
    hostedButtonId: "LSP93GFU35MG8",
  }).render("#paypal-container-LSP93GFU35MG8")
</script>
    </form>
    <?php if (isset($erro)): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
    <?php endif; ?>
</body>
</html>