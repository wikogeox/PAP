<?php
session_start();
include 'config.php';

// Verifica autenticação
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Busca saldo
$user_id = $_SESSION['user_id'];
$stmt    = $liga->prepare("SELECT saldo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result  = $stmt->get_result()->fetch_assoc();
$saldo   = $result['saldo'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Carteira Virtual</title>
  <link rel="stylesheet" href="css/carteira.css">
</head>
<body>
  <div class="rectangle">
  <div class="rectangle-inner">
    <h1 class="title">Carteira Virtual</h1>
    <p class="subtitle">
      Saldo atual: <span class="saldo">€<?= number_format($saldo,2,',','.') ?></span>
    </p>
    <label for="quantia">Adicionar dinheiro:</label>
        <input type="number" id="quantia" step="1.00" placeholder="0.00" required>
        <div id="paypal-button-container"></div>
  </div>

  <script src="https://www.paypal.com/sdk/js?client-id=<?= PAYPAL_CLIENT_ID ?>&currency=EUR"></script>
  <script>
    paypal.Buttons({
      createOrder: (data, actions) => {
        const valor = parseFloat(document.getElementById('quantia').value);
        if (!valor || valor <= 0) {
          alert('Insira uma quantia válida');
          return actions.reject();
        }
        // Chama o endpoint para criar a ordem
        return fetch('create_order.php', {
          method: 'post',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ quantia: valor })
        })
        .then(res => res.json())
        .then(json => {
          if (json.orderID) return json.orderID;
          throw new Error(json.error || 'Falha ao criar ordem');
        });
      },
      onApprove: (data) => {
        // Captura a ordem assim que aprovada
        return fetch(`capture_order.php?orderID=${data.orderID}`)
          .then(res => res.json())
          .then(json => {
            if (json.status === 'success') {
              // Reload para mostrar o novo saldo
              window.location.reload();
            } else {
              alert('Erro no pagamento: ' + (json.error || 'desconhecido'));
            }
          });
      },
      onError: (err) => {
        console.error(err);
        alert('Erro no PayPal: ' + err);
      }
    }).render('#paypal-button-container');
  </script>
</body>
</html>
