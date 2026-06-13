<?php
header('Content-Type: text/html; charset=utf-8');
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

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
    
    echo "<h2>✅ Updated WordPress Starter Kit Landing Page</h2>";
    echo "<p><strong>Title:</strong> WordPress Starter Kit - Free Download</p>";
    echo "<p><strong>Headline:</strong> WordPress Starter Kit</p>";
    echo "<p><strong>Subheadline:</strong> Everything you need to build a professional WordPress site...</p>";
    echo "<p><strong>Popup:</strong> Disabled</p>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}