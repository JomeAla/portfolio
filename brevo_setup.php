<?php
/**
 * Configure Brevo SMTP with correct key names
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

// Brevo SMTP Settings (matching app's expected keys)
// NOTE: Replace these with your actual Brevo credentials
$smtp = [
    'smtp_host' => 'smtp-relay.brevo.com',
    'smtp_port' => '587',
    'smtp_username' => 'YOUR_BREVO_USERNAME',  // e.g., your@email.com
    'smtp_password' => 'YOUR_BREVO_SMTP_KEY', // Get from Brevo dashboard
    'smtp_encryption' => 'tls',
    'smtp_from_email' => 'campaigns@joala.com.ng',
    'smtp_from_name' => 'Joala',
    'smtp_mailer' => 'smtp',
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    foreach ($smtp as $k => $v) {
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE `key` = ?");
        $stmt->execute([$k]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exists) {
            $stmt = $pdo->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = ?");
            $stmt->execute([$v, $k]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([$k, $v]);
        }
    }
    
    echo "<h2>✅ Brevo SMTP Configured!</h2>";
    echo "<p><strong>Settings saved (matching app keys):</strong></p>";
    echo "<table border='1' cellpadding='5'>";
    foreach ($smtp as $k => $v) {
        $display = ($k === 'smtp_password') ? '[HIDDEN]' : htmlspecialchars($v);
        echo "<tr><td>$k</td><td>$display</td></tr>";
    }
    echo "</table>";
    echo "<p><a href='/test-email'>Run Email Test</a></p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}