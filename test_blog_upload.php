<?php
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Upload a test file
    $testContent = file_get_contents('/home/joalacom/public_html/uploads/products/done-for-you-cover.svg');
    file_put_contents('/home/joalacom/public_html/uploads/blog/test-blog-image.svg', $testContent);
    chmod('/home/joalacom/public_html/uploads/blog/test-blog-image.svg', 0644);
    
    // Update database
    $stmt = $pdo->prepare("UPDATE blog_posts SET featured_image = '/uploads/blog/test-blog-image.svg' WHERE id = 3");
    $stmt->execute();
    
    echo "Test image uploaded to: /home/joalacom/public_html/uploads/blog/test-blog-image.svg\n";
    echo "Database updated!\n";
    
    // Verify file exists
    echo "File exists: " . (file_exists('/home/joalacom/public_html/uploads/blog/test-blog-image.svg') ? 'YES' : 'NO') . "\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}