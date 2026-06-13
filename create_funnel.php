<?php
/**
 * Create Test Funnel
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get product ID for WordPress Starter Kit
    $stmt = $pdo->query("SELECT id FROM products WHERE slug = 'wordpress-starter-kit' LIMIT 1");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo "❌ WordPress Starter Kit product not found";
        exit;
    }
    
    $productId = $product['id'];
    
    // Create funnel (6 placeholders + 3 hardcoded values + 2 placeholders + 2 NOW())
    // Fields: name, slug, description, funnel_type, goal, product_id, is_active, countdown_enabled, countdown_hours, thank_you_title, thank_you_message, created_at, updated_at
    $stmt = $pdo->prepare("INSERT INTO funnels (name, slug, description, funnel_type, goal, product_id, is_active, countdown_enabled, countdown_hours, thank_you_title, thank_you_message, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, 1, 24, ?, ?, NOW(), NOW())");
    $stmt->execute([
        'WordPress Starter Kit Launch',
        'wordpress-starter-kit',
        'Free download + upsell to full kit',
        'lead_magnet',
        'download',
        $productId,
        'Check Your Email!',
        'I sent the checklist to your email. Check your inbox (and spam folder) for the download link.'
    ]);
    
    $funnelId = $pdo->lastInsertId();
    
    // Create funnel stages
    $stages = [
        ['Landing Page', 'landing_page', '/l/wordpress-starter-kit'],
        ['Download Page', 'content', '/l/wordpress-starter-kit/download'],
        ['Thank You', 'thankyou', '/f/wordpress-starter-kit/thank-you'],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO funnel_stages (funnel_id, name, type, content, `order`) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($stages as $i => $stage) {
        $content = json_encode(['url' => $stage[2]]);
        $stmt->execute([$funnelId, $stage[0], $stage[1], $content, $i + 1]);
    }
    
    echo "<h2>✅ Funnel Created!</h2>";
    echo "<p>Funnel ID: $funnelId</p>";
    echo "<p>Visit: <a href='https://joala.com.ng/f/wordpress-starter-kit' style='color:blue;'>https://joala.com.ng/f/wordpress-starter-kit</a></p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}