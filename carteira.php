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
  <div class="rectangle" id="carteiraModal">
  <div class="rectangle-inner">
    <h1 class="title">Carteira Virtual</h1>
    <p class="subtitle">
      Saldo atual: <span class="saldo">€<?= number_format($saldo,2,',','.') ?></span>
    </p>

    <label for="quantia">Adicionar dinheiro:</label>
    <input type="number" id="quantia" step="1.00" placeholder="0.00" required>
  </div>

  <div id="paypal-button-container" style="margin: 10px 0;"></div>

  <button id="btn-levantar" class="btn-levantar">Levantar</button>
</div>

  <!-- Modal de levantamento -->
  <div id="modalLevantar" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Levantar Saldo</h2>
      <label for="valorLevantamento">Quantia (€):</label>
      <input type="number" id="valorLevantamento" step="1" min="1" placeholder="0.00" required>
      
      <label for="paypalEmail">E-mail do PayPal:</label>
      <input type="email" id="paypalEmail" placeholder="exemplo@paypal.com" required>
      
      <button id="confirmarLevantamento" class="btn-confirmar">Confirmar</button>
    </div>
  </div>
</div>

  <!--Script do PayPal-->
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

  //Script para o botão de levantamento
  document.getElementById("btn-levantar").onclick = function() {
    document.getElementById("carteiraModal").style.display = "none";  
    document.getElementById("modalLevantar").style.display = "flex";  
  };

  document.querySelector(".close").onclick = function() {
    document.getElementById("modalLevantar").style.display = "none";  
    document.getElementById("carteiraModal").style.display = "flex";  
  };


  document.getElementById("confirmarLevantamento").onclick = function () {
    const valor = parseFloat(document.getElementById("valorLevantamento").value);
    const email = document.getElementById("paypalEmail").value;

    if (!valor || valor <= 0 || !email) {
      alert("Preencha todos os campos corretamente.");
      return;
    }

    fetch('solicitar_levantamento.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ valor: valor, email: email })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message || "Pedido processado.");
      if (data.status === "success") window.location.reload();
    });
  };
  </script>
</body>
</html>
