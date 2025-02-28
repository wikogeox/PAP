<?php
// Inclui o autoload do Composer para PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmail($email, $username) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ivanalmeida02@gmail.com'; 
        $mail->Password   = 'xvnm kloz fxyq rxjo';    
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remetente e destinatário
        $mail->setFrom('ivanalmeida02@gmail.com', 'Ivan Almeida');
        $mail->addAddress($email, $username);

        // Configuração do email em HTML
        $mail->isHTML(true);
        $mail->Subject = 'Bem-vindo ao nosso site, ' . $username . '!';

        $mail->Body = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #0C0C0C;
                    color: white;
                    text-align: center;
                    padding: 20px;
                }
                .container {
                    background: linear-gradient(183deg, #050505 0%, #181818 100%);
                    border-radius: 10px;
                    padding: 20px;
                    max-width: 500px;
                    margin: auto;
                    border: 1px solid #AF1D1D;
                    box-shadow: 0px 0px 10px rgba(255, 0, 0, 0.5);
                }
                h2 {
                    color: #AF1D1D;
                }
                p {
                    color: #9B9B9B;
                }
                .btn {
                    display: inline-block;
                    padding: 10px 20px;
                    background-color: #AF1D1D;
                    color: white !important;
                    text-decoration: none;
                    font-weight: bold;
                    border-radius: 5px;
                    margin-top: 10px;
                }
                .btn:hover {
                    background-color: #EB4E4E;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Bem-vindo, ' . $username . '!</h2>
                <p>Obrigado por se registar no nosso site. Estamos muito felizes em tê-lo conosco! 🎉</p>
                <p>Explore tudo o que temos para oferecer e aproveite a sua experiência.</p>
                <a href="http://localhost/PAP/login.html" class="btn">Comece agora</a>
                <p>Atenciosamente,<br> <strong>Ivan Almeida</strong></p>
            </div>
        </body>
        </html>';

        // Envia o email e retorna verdadeiro se for bem-sucedido
        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log("Erro ao enviar email: " . $mail->ErrorInfo);
        return false;
    }
}

// Testando o envio
if (enviarEmail("a259823716@aeaquaalba.pt", "Ivan")) {
    echo "E-mail enviado com sucesso!";
} else {
    echo "Erro ao enviar o e-mail.";
}
?>
