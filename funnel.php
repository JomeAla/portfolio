<?php
/**
 * Funnel Handler - accessed via /funnel.php/slug
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

// Get slug from URL path: /funnel.php/wordpress-starter-kit -> slug = wordpress-starter-kit
$uri = $_SERVER['REQUEST_URI'] ?? '';
$parts = explode('/', trim($uri, '/'));
$slug = $parts[1] ?? '';

if (!$slug) {
    http_response_code(404);
    echo "404 - Funnel slug required";
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM funnels WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$funnel) {
        http_response_code(404);
        echo "Funnel not found: $slug";
        exit;
    }
    
    $product = null;
    if ($funnel['product_id']) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$funnel['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    $stmt = $pdo->prepare("SELECT * FROM funnel_stages WHERE funnel_id = ? ORDER BY `order`");
    $stmt->execute([$funnel['id']]);
    $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!DOCTYPE html><html><head><title>{$funnel['name']}</title>";
    echo "<script src='https://cdn.tailwindcss.com'></script>";
    echo "</head><body class='bg-gray-50 min-h-screen'>";
    echo "<div class='max-w-4xl mx-auto py-16 px-4'>";
    echo "<h1 class='text-4xl font-bold mb-4'>{$funnel['name']}</h1>";
    echo "<p class='text-lg text-gray-600 mb-8'>{$funnel['description']}</p>";
    echo "<div class='bg-white rounded-xl shadow p-6 mb-8'><h2 class='text-xl font-bold mb-4'>Stages</h2><ul>";
    foreach ($stages as $s) {
        $c = json_decode($s['content'] ?? '{}', true);
        echo "<li><a href='{$c['url']}?funnel={$funnel['id']}' class='text-blue-600'>{$s['name']}</a></li>";
    }
    echo "</ul></div>";
    
    if ($product) {
        echo "<div class='bg-white rounded-xl shadow p-6 mb-8 text-center'>";
        echo "<h2 class='text-2xl font-bold'>{$product['name']}</h2>";
        echo "<p class='text-gray-600'>{$product['description']}</p>";
        echo "<div class='text-3xl font-bold my-4'><span class='line-through text-gray-400'>₦{$product['price']}</span> <span class='text-green-600'>₦{$product['sale_price']}</span></div>";
        echo "<a href='/buy/{$product['slug']}' class='bg-green-600 text-white px-8 py-3 rounded-lg font-bold inline-block'>Buy Now</a>";
        echo "</div>";
    }
    
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}