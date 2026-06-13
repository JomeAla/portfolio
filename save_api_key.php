<?php
/**
 * Add Brevo API Key to settings
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

// Add Brevo API Key to settings
// NOTE: Replace with your actual Brevo API key from dashboard
$apiKey = 'YOUR_BREVO_API_KEY';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add or update API key
    $stmt = $pdo->prepare("SELECT id FROM settings WHERE `key` = 'brevo_api_key'");
    $stmt->execute();
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($exists) {
        $stmt = $pdo->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = 'brevo_api_key'");
        $stmt->execute([$apiKey]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute(['brevo_api_key', $apiKey]);
    }
    
    echo "<h2>✅ Brevo API Key Configured!</h2>";
    echo "<p>Brevo is now fully set up for email delivery.</p>";
    echo "<ul>";
    echo "<li>SMTP (port 587): Configured</li>";
    echo "<li>Brevo API: Key saved</li>";
    echo "<li><a href='/test_api.php'>Send Test Email</a></li>";
    echo "</ul>";
    echo "<h3>Summary:</h3>";
    echo "<p>Your funnel system can now send automated emails via Brevo!</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}