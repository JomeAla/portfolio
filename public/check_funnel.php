<?php
header('Content-Type: text/plain');
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT id, name, slug, description, goal, updated_at FROM funnels WHERE id = 2");
    $stmt->execute();
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Current funnel 2 in DATABASE:\n";
    print_r($funnel);
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}