<?php
/**
 * Add required columns
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add missing columns individually
    try { $pdo->exec("ALTER TABLE landing_pages ADD COLUMN popup_content TEXT"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE landing_pages ADD COLUMN popup_discount DECIMAL(10,2)"); } catch (Exception $e) {}
    
    // Enable popup
    $popupContent = json_encode([
        'title' => 'Wait! Don\'t miss this...',
        'offer' => 'Get my Email Templates Pack (worth ₦15,000) for just ₦12,000',
        'features' => ['6 ready-to-use sequences', '24 tested templates', 'Launch, welcome, follow-up sequences'],
        'cta' => 'Get The Templates Pack',
        'link' => '/email-templates'
    ]);
    
    $stmt = $pdo->prepare("UPDATE landing_pages SET popup_enabled = 1, popup_content = ? WHERE slug = 'free-email-checklist'");
    $stmt->execute([$popupContent]);
    
    echo "<h2>✅ Exit Intent Enabled!</h2>";
    echo "<p>Popup will show when mouse exits the page upward</p>";
    echo "<p>Offer: Email Templates Pack</p>";
    echo "<p>Test: Visit /l/free-email-checklist and move mouse up from top of page</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}