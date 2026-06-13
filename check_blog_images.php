<?php
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, title, featured_image FROM blog_posts ORDER BY id DESC LIMIT 10");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Blog Posts ===\n";
    foreach ($posts as $post) {
        echo "ID: " . $post['id'] . "\n";
        echo "Title: " . $post['title'] . "\n";
        echo "Image: " . ($post['featured_image'] ?? 'NULL') . "\n";
        echo "---\n";
    }
    
    // Check uploads directory
    echo "\n=== Uploads/Blog Files ===\n";
    $files = glob('/home/joalacom/public_html/public/uploads/blog/*');
    foreach ($files as $file) {
        echo basename($file) . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}