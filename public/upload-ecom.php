<?php
/**
 * File upload endpoint for e-commerce starter kit.
 * Access via: https://www.joala.com.ng/portfolio/public/upload-ecom.php?key=joala2024
 * Delete after use for security!
 */

$host = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($host, 'joala.com.ng') === false) {
    die('Access denied');
}

$key = $_GET['key'] ?? '';
if ($key !== 'joala2024') {
    die('Invalid key');
}

$portfolioPath = '/home/joalacom/public_html';
require_once $portfolioPath . '/vendor/autoload.php';
$app = require $portfolioPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== E-commerce Starter Kit Upload Server ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Handle multipart file upload
if (!isset($_FILES['zipfile'])) {
    echo "No file uploaded. Use this endpoint to upload the zip.\n";
    echo "POST multipart/form-data with field name 'zipfile'.\n";
    echo "Max upload size: " . ini_get('upload_max_filesize') . "\n";
    exit;
}

$file = $_FILES['zipfile'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
    ];
    echo "Upload error: " . ($errors[$file['error']] ?? 'Unknown error') . "\n";
    exit;
}

echo "File received: {$file['name']}\n";
echo "Size: " . number_format($file['size'] / 1024, 2) . " KB\n";
echo "Type: {$file['type']}\n\n";

// Save zip
$storageDir = $portfolioPath . '/storage/app/public/products';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0755, true);
}

$zipPath = $storageDir . '/ecom-starter-kit.zip';
move_uploaded_file($file['tmp_name'], $zipPath);
echo "Saved to: $zipPath\n\n";

// Create or update product in DB
$existing = DB::table('products')->where('slug', 'ecommerce-starter-kit')->first();
if ($existing) {
    echo "Product exists (id: {$existing->id}), updating...\n";
    DB::table('products')->where('slug', 'ecommerce-starter-kit')->update([
        'title' => 'E-commerce Starter Kit (Laravel)',
        'slug' => 'ecommerce-starter-kit',
        'price' => 55000,
        'sale_price' => 55000,
        'description' => 'A complete Laravel 10 e-commerce application with admin panel, Paystack integration, product management, and order processing.',
        'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Build Your E-commerce Business Fast</h2>
<p class="text-lg text-slate-600 mb-6">A production-ready Laravel 10 e-commerce application with everything you need to start selling online in Nigeria.</p>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Features</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Admin Dashboard</strong> - Manage products, orders, and customers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Paystack Integration</strong> - Accept payments securely via Paystack</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Product Management</strong> - Full CRUD for products with images</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Order Processing</strong> - Track orders from purchase to delivery</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Customer Management</strong> - Customer profiles and order history</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Coupon System</strong> - Create and validate discount codes</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Email Notifications</strong> - Automatic purchase confirmations</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>SEO Friendly</strong> - Clean URLs for products and pages</span></li>
</ul>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Tech Stack</h3>
<ul class="space-y-2 mb-6">
<li>Laravel 10 (PHP 8.1+)</li>
<li>MySQL Database</li>
<li>Paystack Payment Gateway</li>
<li>Bootstrap 5 Frontend</li>
<li>Blade Templating</li>
</ul>
<div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded-r-lg">
<p class="text-teal-800 font-medium">&#128640; Download includes full source code, migrations, seeders, and documentation.</p>
</div>',
        'is_active' => true,
        'file_path' => 'public/products/ecom-starter-kit.zip',
        'updated_at' => now(),
    ]);
} else {
    echo "Creating new product...\n";
    DB::table('products')->insert([
        'title' => 'E-commerce Starter Kit (Laravel)',
        'slug' => 'ecommerce-starter-kit',
        'price' => 55000,
        'sale_price' => 55000,
        'description' => 'A complete Laravel 10 e-commerce application with admin panel, Paystack integration, product management, and order processing.',
        'full_description' => '<h2 class="text-2xl font-bold text-slate-900 mb-4">Build Your E-commerce Business Fast</h2>
<p class="text-lg text-slate-600 mb-6">A production-ready Laravel 10 e-commerce application with everything you need to start selling online in Nigeria.</p>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Features</h3>
<ul class="space-y-3 mb-6">
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Admin Dashboard</strong> - Manage products, orders, and customers</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Paystack Integration</strong> - Accept payments securely via Paystack</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Product Management</strong> - Full CRUD for products with images</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Order Processing</strong> - Track orders from purchase to delivery</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Customer Management</strong> - Customer profiles and order history</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Coupon System</strong> - Create and validate discount codes</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>Email Notifications</strong> - Automatic purchase confirmations</span></li>
<li class="flex items-start gap-3"><span class="text-emerald-500 mt-1">&#10003;</span><span><strong>SEO Friendly</strong> - Clean URLs for products and pages</span></li>
</ul>
<h3 class="text-xl font-semibold text-slate-800 mb-3">Tech Stack</h3>
<ul class="space-y-2 mb-6">
<li>Laravel 10 (PHP 8.1+)</li>
<li>MySQL Database</li>
<li>Paystack Payment Gateway</li>
<li>Bootstrap 5 Frontend</li>
<li>Blade Templating</li>
</ul>
<div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded-r-lg">
<p class="text-teal-800 font-medium">&#128640; Download includes full source code, migrations, seeders, and documentation.</p>
</div>',
        'is_active' => true,
        'file_path' => 'public/products/ecom-starter-kit.zip',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

$product = DB::table('products')->where('slug', 'ecommerce-starter-kit')->first();
echo "\n=== Product Ready ===\n";
echo "ID: {$product->id}\n";
echo "Title: {$product->title}\n";
echo "Price: NGN " . number_format($product->price, 2) . "\n";
echo "Slug: {$product->slug}\n";
echo "Active: " . ($product->is_active ? 'Yes' : 'No') . "\n";
echo "\nSales page: https://www.joala.com.ng/ecommerce-starter-kit.php\n";
echo "Sales page (local): /ecommerce-starter-kit.php\n";
echo "\n=== Done ===\n";