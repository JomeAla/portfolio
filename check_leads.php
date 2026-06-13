<?php
/**
 * Check Leads
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM leads ORDER BY id DESC LIMIT 5");
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Leads in Database:</h2>";
    echo "<pre style='background:#1a202c;color:#e2e8f0;padding:20px;border-radius:8px;'>";
    
    if (empty($leads)) {
        echo "No leads yet";
    } else {
        foreach ($leads as $lead) {
            echo "ID: " . $lead['id'] . "\n";
            echo "Name: " . $lead['name'] . "\n";
            echo "Email: " . $lead['email'] . "\n";
            echo "Score: " . $lead['score'] . "\n";
            echo "Created: " . $lead['created_at'] . "\n";
            echo "---\n";
        }
    }
    echo "</pre>";
    
    echo "<p>Total leads: " . count($leads) . "</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}