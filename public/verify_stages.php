<!DOCTYPE html>
<html>
<head><title>Verify WP Funnel Stages</title></head>
<body>
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>WordPress Funnel Stages - ACTUAL DATABASE</h1>";
    
    $stmt = $pdo->query("SELECT * FROM funnel_stages WHERE funnel_id = 2 ORDER BY `order`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<h2>Stage {$row['order']}: {$row['name']}</h2>";
        echo "<p><strong>Type:</strong> {$row['type']}</p>";
        echo "<p><strong>Content:</strong> " . htmlspecialchars($row['content'] ?? 'NULL') . "</p>";
        echo "<hr>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
</body>
</html>