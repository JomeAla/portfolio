<?php
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get recent posts as the homepage would
    $stmt = $pdo->query("SELECT id, title, slug, featured_image, is_published, published_at FROM blog_posts WHERE is_published = 1 AND (published_at IS NULL OR published_at <= NOW()) ORDER BY published_at DESC LIMIT 3");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Recent Posts (Homepage) ===\n";
    foreach ($posts as $post) {
        echo "ID: " . $post['id'] . "\n";
        echo "Title: " . $post['title'] . "\n";
        echo "Slug: " . $post['slug'] . "\n";
        echo "Image: " . ($post['featured_image'] ?? 'NULL') . "\n";
        echo "Published: " . ($post['is_published'] ? 'Yes' : 'No') . "\n";
        echo "Published at: " . $post['published_at'] . "\n";
        
        // Check if file exists
        if ($post['featured_image']) {
            $fullPath = '/home/joalacom/public_html/public' . $post['featured_image'];
            echo "Full path: $fullPath\n";
            echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        }
        echo "---\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}