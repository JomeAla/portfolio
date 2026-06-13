<?php
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, title, featured_image FROM blog_posts");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Blog Posts ===\n";
    foreach ($posts as $post) {
        echo "ID: " . $post['id'] . "\n";
        echo "Title: " . $post['title'] . "\n";
        echo "Image: " . ($post['featured_image'] ?? 'NULL') . "\n";
        
        if ($post['featured_image']) {
            $fullPath = '/home/joalacom/public_html/public' . $post['featured_image'];
            echo "Full path: $fullPath\n";
            echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        }
        echo "---\n";
    }
    
    // List files in blog uploads
    echo "\n=== Files in uploads/blog ===\n";
    $blogDir = '/home/joalacom/public_html/public/uploads/blog';
    if (is_dir($blogDir)) {
        $files = scandir($blogDir);
        foreach ($files as $f) {
            if ($f !== '.' && $f !== '..') {
                echo $f . "\n";
            }
        }
    } else {
        echo "Directory does not exist!\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}