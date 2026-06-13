<?php
/**
 * STANDALONE PRODUCT CREATOR
 * This file works without Laravel - pure PHP
 * Upload to: public_html/portfolio/public/cp.php
 * Access: yoursite.com/portfolio/public/cp.php
 */

echo "<pre style='background:#1a1a1a;color:#0f0;padding:20px;font-family:monospace;'>\n";

// Config - get from environment or set manually
$dbname = 'joala_portfolio';  // CHANGE: Your database name
$dbuser = 'joalacom';         // CHANGE: Your cPanel username  
$dbpass = '4fu359TgAMi-O+';   // CHANGE: Your cPanel password
$dbhost = 'localhost';

try {
    $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database\n\n";
} catch (PDOException $e) {
    die("✗ Connection failed: " . $e->getMessage() . "\n");
}

// Check if exists
$check = $pdo->prepare("SELECT id FROM products WHERE title LIKE ?");
$check->execute(['%Email Sequence Templates%']);

if ($check->fetch()) {
    echo "⚠ Product already exists!\n";
    echo "<a href='/store' style='color:#0ff;'>View Store →</a>\n";
    exit();
}

// Create product
$stmt = $pdo->prepare("INSERT INTO products (title, slug, short_description, description, type, price, sale_price, file_path, is_active, is_featured, `order`, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

$stmt->execute([
    'Email Sequence Templates Pack',
    'email-sequence-templates-pack',
    '6 ready-to-use email sequences with 24 tested templates',
    'Email Sequence Templates Pack - 6 Email Sequences (24 Templates)',
    'ebook',
    15000.00,
    12000.00,
    'uploads/products/files/email-sequence-templates-pack.html',
    1,
    1,
    1
]);

$newId = $pdo->lastInsertId();

echo "✓ SUCCESS! Product created with ID: $newId\n\n";
echo "<a href='/store' style='color:#0ff;font-size:18px;'>Click here to view Store →</a>\n";
echo "\n</pre>";