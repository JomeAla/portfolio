<?php
/**
 * Fix popup columns and enable
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add just the missing column
    try {
        $pdo->exec("ALTER TABLE landing_pages ADD COLUMN popup_content TEXT AFTER popup_delay");
    } catch (Exception $e) {
        // ignore
    }
    
    // Enable popup with JSON content
    $popupContent = json_encode([
        'title' => 'Wait! Don\'t miss this...',
        'offer' => 'Get my Email Templates Pack (worth ₦15,000) for just ₦12,000',
        'features' => ['6 ready-to-use sequences', '24 tested templates', 'Launch, welcome, follow-up sequences'],
        'cta' => 'Get The Templates Pack',
        'link' => '/email-templates'
    ]);
    
    $stmt = $pdo->prepare("UPDATE landing_pages SET popup_enabled = 1, popup_content = ?, popup_discount = 12000 WHERE slug = 'free-email-checklist'");
    $stmt->execute([$popupContent]);
    
    echo "<h2>✅ Exit Intent Enabled!</h2>";
    echo "<p>Popup will show when mouse exits upward from page</p>";
    echo "<p>Offer: Email Templates Pack at ₦12,000</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}