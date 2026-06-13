<!DOCTYPE html>
<html>
<head><title>Debug Funnel Update</title></head>
<body>
<?php
// Debug what's happening with funnel update

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Debug Funnel ID 2</h1>";
    
    // Get funnel with fillable
    $stmt = $pdo->query("DESCRIBE funnels");
    echo "<h2>Funnel Table Columns</h2>";
    echo "<table border='1' cellpadding='5'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td></tr>";
    }
    echo "</table>";
    
    // Get funnel data for update
    $stmt = $pdo->query("SELECT * FROM funnels WHERE id = 2");
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Funnel Data</h2>";
    echo "<pre>";
    print_r($funnel);
    echo "</pre>";
    
    // Test update
    echo "<h2>Testing Update</h2>";
    $stmt = $pdo->prepare("UPDATE funnels SET name = ?, updated_at = NOW() WHERE id = 2");
    $stmt->execute(['WordPress Starter Kit Launch']);
    echo "<p>✅ Update test passed!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>