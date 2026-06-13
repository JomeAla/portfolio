<?php
/**
 * Fix WordPress Starter Kit Landing Page Content
 * Run via: php fix_wp_landing.php
 */

$host = 'localhost';
$db   = 'joala_portfolio';
$user = 'root';
$pass = 'Mylordhelpme12';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
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
    
    echo "✅ Updated WordPress Starter Kit landing page\n";
    echo "Title: WordPress Starter Kit - Free Download\n";
    echo "Popup: Disabled\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}