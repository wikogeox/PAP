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
        $mail->Password   = 'uzmp mfve ucuq krpl';    
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Remetente e destinatário
        $mail->setFrom('ivanalmeida02@gmail.com', 'Ivan Almeida'); // 
        $mail->addAddress($email, $username);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = 'Bem-vindo ao nosso site!';
        $mail->Body    = "
            <h3>Bem-vindo, $username!</h3>
            <p>Obrigado por se registar no nosso site. Esperamos que tenha uma ótima experiência.</p>
            <p>Atenciosamente, <br> Ivan Almeida</p>
        ";

        // Envia o email
        $mail->send();
    } catch (Exception $e) {
        echo "Erro ao enviar o email de boas-vindas: {$mail->ErrorInfo}";
    }
}
