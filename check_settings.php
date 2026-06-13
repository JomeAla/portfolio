<?php
/**
 * Check settings table structure
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE settings");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Settings table columns:<br>";
    print_r($cols);
    
    // Check sample data
    echo "<br>Sample data:<br>";
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}