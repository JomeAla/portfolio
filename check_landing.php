<?php
$host = 'localhost';
$db   = 'joala_portfolio';
$user = 'root';
$pass = 'Mylordhelpme12';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, title, slug FROM landing_pages WHERE slug LIKE '%wordpress%' OR title LIKE '%wordpress%'");
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Landing pages found:\n";
    print_r($pages);
    
    echo "\n\nAll landing page slugs:\n";
    $stmt = $pdo->query("SELECT id, title, slug FROM landing_pages ORDER BY id DESC LIMIT 20");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- $row[slug]: $row[title]\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}