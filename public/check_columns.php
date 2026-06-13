<!DOCTYPE html>
<html>
<head><title>Check Funnel Columns</title></head>
<body>
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Funnel Table Columns</h1>";
    
    // Get all columns
    $stmt = $pdo->query("SHOW COLUMNS FROM funnels");
    $columns = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $row['Field'];
    }
    
    echo "<h2>Database Columns (" . count($columns) . ")</h2>";
    echo "<pre>" . implode(", ", $columns) . "</pre>";
    
    // Try direct update test
    echo "<h2>Test Direct Update</h2>";
    $testData = [
        'name' => 'WordPress Starter Kit Launch',
        'goal' => 'download',
        'is_active' => 1,
        'welcome_sequence_id' => 21,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $cols = implode(', ', array_map(function($k) { return "$k = ?"; }, array_keys($testData)));
    $stmt = $pdo->prepare("UPDATE funnels SET $cols WHERE id = 2");
    $stmt->execute(array_values($testData));
    echo "<p>✅ Direct update test passed!</p>";
    
    // Check updated data
    echo "<h2>Updated Funnel Data</h2>";
    $stmt = $pdo->query("SELECT id, name, goal, is_active, welcome_sequence_id FROM funnels WHERE id = 2");
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($funnel, true) . "</pre>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>