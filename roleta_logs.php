<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $aposta = $_POST['aposta'];
    $cor_apostada = $_POST['cor_apostada'];
    $numero_sorteado = $_POST['numero_sorteado'];

    // Determinar o resultado
    $resultado = "lose";
    $valor_ganho = 0;

    if (($cor_apostada == 'vermelho' && $numero_sorteado >= 1 && $numero_sorteado <= 18) ||
        ($cor_apostada == 'preto' && $numero_sorteado >= 19 && $numero_sorteado <= 36) ||
        ($cor_apostada == 'verde' && $numero_sorteado == 0)) {
        $resultado = "win";

        // Multiplicador de ganhos
        if ($cor_apostada == 'vermelho' || $cor_apostada == 'preto') {
            $valor_ganho = $aposta * 2; // Ganha o dobro
        } elseif ($cor_apostada == 'verde') {
            $valor_ganho = $aposta * 14; // Verde paga 14x
        }
    }

    // Registrar no banco de dados
    $stmt = $conn->prepare("INSERT INTO roleta_logs (user_id, aposta, cor_apostada, resultado, valor_ganho, numero_sorteado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssid", $user_id, $aposta, $cor_apostada, $resultado, $valor_ganho, $numero_sorteado);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["resultado" => $resultado, "valor_ganho" => $valor_ganho]);
}
?>
