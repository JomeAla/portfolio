<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "Testing Brevo SMTP...<br>";

$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");
$res = $conn->query("SELECT `key`, value FROM settings WHERE `key` LIKE 'mail_%'");
$settings = [];
while ($r = $res->fetch_assoc()) { $settings[$r['key']] = $r['value']; }
$conn->close();

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $settings['mail_host'] ?? 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = $settings['mail_username'] ?? '';
    $mail->Password = $settings['mail_password'] ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $settings['mail_port'] ?? 587;
    $mail->setFrom($settings['mail_from_address'] ?? 'noreply@joala.com.ng', 'JoAla Test');
    $mail->addAddress('test@example.com');
    $mail->Subject = 'Test Email';
    $mail->Body = '<h1>Test from Brevo!</h1><p>This is a test email.</p>';
    $mail->isHTML(true);
    
    if ($mail->send()) {
        echo "✓ Email sent successfully!";
    } else {
        echo "✗ Failed: " . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage();
}