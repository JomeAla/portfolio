<!DOCTYPE html>
<html>
<head><title>Activate WP Funnel</title></head>
<body>
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("UPDATE funnels SET is_active = 1, welcome_sequence_id = 21 WHERE id = 2");
    
    echo "<h1 style='color:green'>✅ WordPress Starter Kit Funnel is now ACTIVE!</h1>";
    
    $stmt = $pdo->query("SELECT id, name, is_active, welcome_sequence_id FROM funnels WHERE id = 2");
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>is_active: " . ($funnel['is_active'] ? 'Yes' : 'No') . "</p>";
    echo "<p>welcome_sequence_id: " . $funnel['welcome_sequence_id'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>