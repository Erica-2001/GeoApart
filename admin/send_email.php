<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';
require_once __DIR__ . '/../phpmailer/Exception.php';

function sendEmail($toEmail, $toName, $subject, $bodyHtml) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use smtp.gmail.com if using Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'helpcenter.geoapart@gmail.com'; // Your SMTP email
        $mail->Password = 'YOUR_APP_PASSWORD_HERE'; // YOUR 16-digit App Password
        $mail->SMTPSecure = 'tls'; // Or ssl
        $mail->Port = 587; // 465 if ssl

        // Sender Info
        $mail->setFrom('helpcenter.geoapart@gmail.com', 'GeoApart');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>
