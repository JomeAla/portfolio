<?php
/**
 * Fallback Funnel Router
 * Upload to public_html/f.php
 * This handles /f/{funnel} routes from database
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

// Get the funnel slug from URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

if (!preg_match('|^f/([^/]+)|', $path, $matches)) {
    http_response_code(404);
    echo "Funnel not found";
    exit;
}

$funnelSlug = $matches[1];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get funnel
    $stmt = $pdo->prepare("SELECT * FROM funnels WHERE slug = ? AND is_active = 1");
    $stmt->execute([$funnelSlug]);
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$funnel) {
        http_response_code(404);
        echo "Funnel not found: $funnelSlug";
        exit;
    }
    
    // Get product if linked
    $productHtml = '';
    if ($funnel['product_id']) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$funnel['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $original = $product['price'];
            $sale = $product['sale_price'];
            $productHtml = <<<HTML
<div class="text-center py-8">
    <h2 class="text-3xl font-bold mb-4">{$product['name']}</h2>
    <p class="text-lg mb-4">{$product['description']}</p>
    <div class="text-2xl">
        <span class="line-through text-gray-400">₦{$original}</span>
        <span class="text-green-600 font-bold">₦{$sale}</span>
    </div>
    <a href="/buy/{$product['slug']}" class="inline-block bg-green-600 text-white px-8 py-4 rounded-lg font-bold mt-4">Buy Now</a>
</div>
HTML;
        }
    }
    
    // Get stages
    $stmt = $pdo->prepare("SELECT * FROM funnel_stages WHERE funnel_id = ? ORDER BY `order`");
    $stmt->execute([$funnel['id']]);
    $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stagesHtml = '';
    foreach ($stages as $stage) {
        $content = json_decode($stage['content'] ?? '{}', true);
        $url = $content['url'] ?? '#';
        $stagesHtml .= '<div class="mb-2"><a href="' . $url . '?funnel=' . $funnel['id'] . '" class="text-blue-600 hover:underline">' . htmlspecialchars($stage['name']) . '</a></div>';
    }
    
    // Render funnel page
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$funnel['name']}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-2xl mx-auto py-20 px-4">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">{$funnel['name']}</h1>
        <p class="text-lg text-gray-600 mb-8">{$funnel['description']}</p>
        
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Funnel Stages</h2>
            $stagesHtml
        </div>
        
        $productHtml
        
        <div class="text-center mt-8">
            <a href="/store" class="text-blue-600 hover:underline">Browse All Products</a>
        </div>
    </div>
</body>
</html>
HTML;
    
} catch(PDOException $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}