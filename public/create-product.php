<?php
// Simple product creation script
// Upload to: public/create-product.php
// Access via: yourdomain.com/create-product.php

error_reporting(0);
$requestHost = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($requestHost, 'joala') === false && $requestHost !== 'localhost') {
    die('Access denied');
}

echo "Starting...<br>";

// Database config
$db = getenv('DB_DATABASE') ?: 'joala_portfolio';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$host = getenv('DB_HOST') ?: 'localhost';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Check if product exists
$stmt = $pdo->prepare("SELECT id FROM products WHERE title LIKE ?");
$stmt->execute(['%Email Sequence Templates%']);
if ($stmt->fetch()) {
    die("Product already exists! <a href='/store'>View Store</a>");
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

echo "Product created successfully! <a href='/store'>View in Store</a>";