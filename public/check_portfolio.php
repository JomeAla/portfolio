<?php
$host = 'localhost';
$dbname = 'joala_portfolio';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Check existing projects
$projects = $pdo->query("SELECT id, title, slug FROM projects LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

echo "=== EXISTING PROJECTS ===\n";
if (count($projects) > 0) {
    foreach ($projects as $p) {
        echo "- {$p['title']} (slug: {$p['slug']})\n";
    }
} else {
    echo "No projects found\n";
}

echo "\n=== PORTFOLIO TABLES ===\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo "- $t\n";
}