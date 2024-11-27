<?php
// Conexão à base de dados
$liga = mysqli_connect('localhost', 'root', 'root');
if (!$liga) {
    echo "<h2>ERROR!!! Falha na ligação ao Servidor!</h2>";
    exit;
}
mysqli_select_db($liga, 'pap');

// Verifica se o email existe
if (isset($_POST['email'])) {  // Certifica-te de que o campo 'username' existe
    $email = $_POST['email'];

    // Verifica se o utilizador existe na base de dados
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $liga->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Retorna um JSON com a verificação
    echo json_encode(['exists' => $result->num_rows > 0]);
}
?>
