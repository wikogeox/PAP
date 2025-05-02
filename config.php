<?php
// Conexão à base de dados
$liga = mysqli_connect('localhost', 'root', 'root');
if (!$liga) {
    echo "<h2>ERROR!!! Falha na ligação ao Servidor!</h2>";
    exit;
}
mysqli_select_db($liga, 'pap');

// Verifica se o email existe
if (isset($_POST['email'])) {  
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

//API do paypal
define('PAYPAL_CLIENT_ID', 'Abx3ripqyM5FFfzIZdIpakbH1qokW3W8AyVCwZQSpwcoc4yPh8_qUPokC3a1dtMKuZ2-mO1VpioBifuE');
define('PAYPAL_SECRET', 'EOFwOkdsHVryDLKddXWduqBbLVNQ5P0h0Xh8QPiwqQ7VF55CyIsGDESiVW7t5HltaApZ0aSugpmppcMx');
define('PAYPAL_MODE', 'sandbox'); 
?>
