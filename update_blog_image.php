<?php
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("UPDATE blog_posts SET featured_image = '/uploads/blog/test-image.png' WHERE id = 3");
    $stmt->execute();
    
    echo "Updated!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}