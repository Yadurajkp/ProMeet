<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function sendOtpEmail($toEmail, $otp) {
    $mail = new PHPMailer(true);
    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yadurajkp2004@gmail.com'; // your Gmail
        $mail->Password   = 'vwxk vnuj ktio bdqt';     // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email headers
        $mail->setFrom('yadurajkp2004@gmail.com', 'Promeet Support');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your OTP Code - Promeet";
        $mail->Body    = "<p>Your OTP for password reset is: <b>$otp</b></p><p>It will expire in 5 minutes.</p>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Mailer Error: " . $mail->ErrorInfo
        ]);
        return false;
    }
}
?>
