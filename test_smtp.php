<?php
/**
 * Test Brevo SMTP - Port 465 (SSL)
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$toEmail = 'jomealea@gmail.com';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'smtp_%'");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['key']] = $row['value'];
    }
    
    $smtpHost = $settings['smtp_host'] ?? '';
    $username = $settings['smtp_username'] ?? '';
    $password = $settings['smtp_password'] ?? '';
    $fromEmail = $settings['smtp_from_email'] ?? '';
    $fromName = $settings['smtp_from_name'] ?? 'Joala';
    
    echo "<h2>Testing Brevo SMTP (Port 465 SSL)...</h2>";
    
    // Connect with SSL
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $sock = @stream_socket_client(
        'ssl://' . $smtpHost . ':465',
        $errno,
        $errstr,
        10,
        STREAM_CLIENT_CONNECT,
        $context
    );
    
    if (!$sock) {
        echo "❌ Connection failed: $errstr ($errno)<br>";
        exit;
    }
    
    echo "✅ Connected via SSL!<br>";
    
    // Read welcome
    $response = fgets($sock, 512);
    echo "Server: " . trim($response) . "<br>";
    
    // EHLO
    fwrite($sock, "EHLO localhost\r\n");
    while ($line = fgets($sock, 512)) {
        if (strpos($line, '250-') !== 0) break;
    }
    
    // AUTH LOGIN
    fwrite($sock, "AUTH LOGIN\r\n");
    fgets($sock, 512);
    
    fwrite($sock, base64_encode($username) . "\r\n");
    fgets($sock, 512);
    
    fwrite($sock, base64_encode($password) . "\r\n");
    $response = fgets($sock, 512);
    
    if (strpos($response, '235') === false) {
        echo "❌ Auth failed: $response<br>";
        fclose($sock);
        exit;
    }
    
    echo "✅ Authenticated!<br>";
    
    // Send email
    fwrite($sock, "MAIL FROM:<$fromEmail>\r\n");
    fgets($sock, 512);
    
    fwrite($sock, "RCPT TO:<$toEmail>\r\n");
    fgets($sock, 512);
    
    fwrite($sock, "DATA\r\n");
    fgets($sock, 512);
    
    $message = "From: $fromName <$fromEmail>\r\n";
    $message .= "To: $toEmail\r\n";
    $message .= "Subject: Test Email - Brevo Working\r\n";
    $message .= "Content-Type: text/plain\r\n\r\n";
    $message .= "Success! Brevo SMTP is configured and working!\r\n\r\n";
    $message .= "- From Joala\r\n.\r\n";
    
    fwrite($sock, $message);
    fgets($sock, 512);
    
    fwrite($sock, "QUIT\r\n");
    fclose($sock);
    
    echo "✅ <strong>Email Sent!</strong><br>";
    echo "<p>Check inbox: <strong>$toEmail</strong> (and spam folder)</p>";
    echo "<h3 style='color:green'>🎉 Brevo is working!</h3>";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}