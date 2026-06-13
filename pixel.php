<?php
/**
 * Pixel Handler - serves tracking pixels for funnels
 * URL: /pixel.php?type=fb|ga&funnel=slug
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$type = $_GET['type'] ?? '';
$slug = $_GET['funnel'] ?? '';

if (!$slug || !in_array($type, ['fb', 'ga'])) {
    http_response_code(404);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $col = $type === 'fb' ? 'facebook_pixel' : 'google_pixel';
    $stmt = $pdo->prepare("SELECT $col, name FROM funnels WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$funnel || empty($funnel[$col])) {
        http_response_code(404);
        echo "No pixel configured";
        exit;
    }
    
    // Update pixel with actual pixel IDs (replace placeholders)
    $pixel = $funnel[$col];
    $pixel = str_replace('YOUR_PIXEL_ID_HERE', '1234567890', $pixel);
    $pixel = str_replace('GA_MEASUREMENT_ID', 'G-ABC123DEF', $pixel);
    
    header('Content-Type: text/html');
    echo $pixel;
    
} catch(Exception $e) {
    http_response_code(500);
    echo "Error";
}