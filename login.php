<?php
// Inicia a sessão
session_start();

// Inclui o ficheiro de configuração da base de dados
include 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados do formulário
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica se o email e password foram fornecidos
    if (!empty($email) && !empty($password)) {
        // Consulta SQL para verificar o utilizador e obter o ID e outros dados
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $liga->prepare($sql);
        $stmt->bind_param("s", $email); // Substitui o placeholder com o email
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Utilizador encontrado, agora verifica a password
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) { 
                // Autenticação bem-sucedida, guarda os dados na sessão

                // Regenera a sessão para evitar sessão fixa e garantir que um novo sessao_id seja gerado
                session_regenerate_id(true);

                // Guarda as informações do utilizador na sessão
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['sessao_id'] = session_id(); // Guarda o ID da nova sessão

                // Redireciona para o index.php
                header("Location: index.php");
                exit();
            } else {
                // Password incorreta, redireciona de volta para a página de login com mensagem de erro
                $_SESSION['error_message'] = "Palavra-passe errada. Tente novamente.";
                header("Location: login.html");
                exit();
            }
        } else {
            // Utilizador não encontrado, redireciona de volta para a página de login com mensagem de erro
            $_SESSION['error_message'] = "Utilizador não encontrado. Tente novamente.";
            header("Location: login.html");
            exit();
        }
    } else {
        // Campos não foram preenchidos, redireciona de volta para a página de login com mensagem de erro
        $_SESSION['error_message'] = "Por favor, preencha todos os campos.";
        header("Location: login.html");
        exit();
    }
}
?>
