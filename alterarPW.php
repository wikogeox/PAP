<?php
// Inicia a sessão
session_start();

// Inclui o ficheiro de configuração da base de dados
include 'config.php';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados do formulário
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];
    $email = $_POST['email']; // O email do utilizador do formulário

    // Verifica se as passwords coincidem
    if (!empty($new_password) && $new_password === $confirm_password) {
        // Verifica se o utilizador existe na base de dados pelo email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $liga->prepare($sql);

        if ($stmt) { // Verifica se a preparação foi bem-sucedida
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Atualiza a palavra-passe na base de dados
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT); // Criptografa a nova senha
                $sql_update = "UPDATE users SET password_hash = ? WHERE email = ?";
                $stmt_update = $liga->prepare($sql_update);

                if ($stmt_update) {
                    $stmt_update->bind_param("ss", $hashedPassword, $email); // Substitui os placeholders com a nova password e o email
                    $stmt_update->execute();

                    if ($stmt_update->affected_rows > 0) {
                        // Redireciona com a mensagem de sucesso
                        header("Location: alterarPW.html?email=$email&mensagem=success&texto=A sua palavra-passe foi alterada com sucesso!");
                        exit();
                    } else {
                        // Redireciona com a mensagem de erro
                        header("Location: alterarPW.html?email=$email&mensagem=error&texto=Erro ao alterar a palavra-passe. Tente novamente.");
                        exit();
                    }

                    $stmt_update->close(); // Fecha a declaração da atualização
                } else {
                    // Erro na preparação
                    header("Location: alterarPW.html?email=$email&mensagem=error&texto=Erro na preparação da atualização da palavra-passe.");
                    exit();
                }
            } else {
                // Utilizador não encontrado
                header("Location: alterarPW.html?email=$email&mensagem=error&texto=Utilizador não encontrado. Tente novamente.");
                exit();
            }

            $stmt->close();
        } else {
            // Erro na preparação da consulta
            header("Location: alterarPW.html?email=$email&mensagem=error&texto=Erro na preparação da consulta.");
            exit();
        }
    } else {
        // As passwords não coincidem
        header("Location: alterarPW.html?email=$email&mensagem=error&texto=As palavras-passe não coincidem. Tente novamente.");
        exit();
    }

    // Fecha a conexão
    $liga->close();
}

?>
