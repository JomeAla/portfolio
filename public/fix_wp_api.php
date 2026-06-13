<?php
error_reporting(0);
@ini_set('display_errors', 0);

define('DB_HOST', 'localhost');
define('DB_NAME', 'joalacom_joala');
define('DB_USER', 'joalacom_joala');
define('DB_PASS', 'J0ala@2024!');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $slug = 'free-wordpress-starter-kit';
    
    $content = json_encode([
        'headline' => 'WordPress Starter Kit',
        'subheadline' => 'Everything you need to build a professional WordPress site - themes, plugins, templates & setup guide. No coding required.',
        'items' => [
            'Premium WordPress Theme (worth ₦15,000)',
            'Essential Plugins Bundle', 
            '5 Ready-to-Use Page Templates',
            'Step-by-Step Setup Guide',
            'SEO Optimization Checklist',
            'Free Updates for Life'
        ],
        'cta' => 'Get My Free Kit'
    ]);
    
    $stmt = $pdo->prepare("UPDATE landing_pages SET title = ?, custom_html = ?, is_active = 1, show_popup = 0, updated_at = NOW() WHERE slug = ?");
    $stmt->execute(['WordPress Starter Kit - Free Download', $content, $slug]);
    $rows = $stmt->rowCount();
    
    if ($rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Landing page updated!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No rows updated']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}