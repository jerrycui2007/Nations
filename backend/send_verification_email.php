<?php
require_once 'db_connection.php';
require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';
require_once 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

function sendVerificationEmail($email, $token) {
    $site_url = "https://jerry.rhhscs.com";
    $verification_link = "$site_url/frontend/verify_email.php?token=$token";
    
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'justincheng432@gmail.com'; // Your Gmail address
        $mail->Password = 'khgausnjnryzrjor'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Nations Game');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify your Nations account';
        $mail->Body = "
            <h2>Welcome to Nations!</h2>
            <p>Please click the following link to verify your email address:</p>
            <p><a href='$verification_link'>$verification_link</a></p>
            <p>This link will expire in 24 hours.</p>
            <p>If you didn't create an account, please ignore this email.</p>
        ";
        $mail->AltBody = "Welcome to Nations!\n\n"
            . "Please click the following link to verify your email address:\n"
            . "$verification_link\n\n"
            . "This link will expire in 24 hours.\n"
            . "If you didn't create an account, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
