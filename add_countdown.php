<?php
/**
 * Add countdown timer to landing page
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add countdown columns
    try { $pdo->exec("ALTER TABLE landing_pages ADD COLUMN countdown_enabled TINYINT(1) DEFAULT 0"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE landing_pages ADD COLUMN countdown_hours INT DEFAULT 24"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE landing_pages ADD COLUMN countdown_message VARCHAR(255)"); } catch (Exception $e) {}
    
    // Enable countdown (24 hours from now)
    $endTime = date('Y-m-d H:i:s', time() + 86400);
    $stmt = $pdo->prepare("UPDATE landing_pages SET countdown_enabled = 1, countdown_hours = 24, countdown_message = 'Offer expires in:', countdown_end = ? WHERE slug = 'free-email-checklist'");
    $stmt->execute([$endTime]);
    
    echo "<h2>✅ Countdown Timer Enabled!</h2>";
    echo "<p>24-hour countdown on landing page</p>";
    echo "<p>Message: 'Offer expires in: [timer]'</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}