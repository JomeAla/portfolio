<?php
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $stmt = $pdo->query("SELECT featured_image FROM blog_posts WHERE id = 3");
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Image path in DB: " . $post['featured_image'] . "\n";
    echo "Asset URL would be: https://joala.com.ng" . $post['featured_image'] . "\n";
    
    // Check if file exists at the expected location
    $fullPath = '/home/joalacom/public_html' . $post['featured_image'];
    echo "Full path: $fullPath\n";
    echo "File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}