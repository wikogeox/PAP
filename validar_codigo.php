<?php
session_start();
require 'config.php'; // Conexão com a base de dados

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Precisa de estar autenticado!"]);
    exit;
}

if (!isset($_POST['codigo']) || empty($_POST['codigo'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Código inválido!"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$codigo = trim($_POST['codigo']);

$liga->begin_transaction(); // Iniciar transação

try {
    // Verificar se o código pertence ao próprio utilizador
    $verificaProprioCodigo = $liga->prepare("SELECT id FROM users WHERE id = ? AND referral_code = ?");
    $verificaProprioCodigo->bind_param("is", $user_id, $codigo);
    $verificaProprioCodigo->execute();
    $resultadoProprioCodigo = $verificaProprioCodigo->get_result();

    if ($resultadoProprioCodigo->num_rows > 0) {
        throw new Exception("Não pode usar o seu próprio código!");
    }

    // Verificar se o código existe na tabela 'users'
    $stmt = $liga->prepare("SELECT id FROM users WHERE referral_code = ?"); 
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Código inválido!");
    }

    $codigoInfo = $result->fetch_assoc();
    $dono_codigo = $codigoInfo['id']; // O dono do código é o ID do user que tem esse código

    // Verificar se o utilizador já usou esse código antes na tabela 'referrals'
    $stmt = $liga->prepare("SELECT id FROM referrals WHERE referrer_id = ? AND referred_id = ?");
    $stmt->bind_param("ii", $dono_codigo, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("Já utilizou este código anteriormente!");
    }

    // Atualizar saldo do utilizador que usou o código (+20€)
    $stmt = $liga->prepare("UPDATE users SET saldo = saldo + 20 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Atualizar saldo do dono do código (+10€)
    $stmt = $liga->prepare("UPDATE users SET saldo = saldo + 10 WHERE id = ?");
    $stmt->bind_param("i", $dono_codigo);
    $stmt->execute();

    // Registrar a referência na tabela 'referrals'
    $stmt = $liga->prepare("INSERT INTO referrals (referrer_id, referred_id, bonus_given) VALUES (?, ?, 1)");
    $stmt->bind_param("ii", $dono_codigo, $user_id);
    $stmt->execute();

    // ✅ Confirmar a transação
    $liga->commit();

    echo json_encode(["sucesso" => true, "mensagem" => "Bônus de 20€ aplicado com sucesso!"]);

} catch (Exception $e) {
    $liga->rollback(); // ❌ Desfazer mudanças se der erro
    echo json_encode(["sucesso" => false, "mensagem" => $e->getMessage()]);
}

exit;
?>
