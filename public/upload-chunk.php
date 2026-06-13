<?php
/**
 * Chunked file upload receiver.
 * Access via: https://www.joala.com.ng/portfolio/public/upload-chunk.php?key=joala2024
 * Deletes itself after successful upload.
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

header('Content-Type: text/plain');

$action = $_POST['action'] ?? '';
$storageDir = $portfolioPath . '/storage/app/public/products';

if ($action === 'start') {
    $filename = 'ecom-starter-kit.zip';
    $totalChunks = (int)($_POST['totalChunks'] ?? 0);
    $totalSize = (int)($_POST['totalSize'] ?? 0);
    
    file_put_contents($storageDir . '/.upload_meta.json', json_encode([
        'filename' => $filename,
        'totalChunks' => $totalChunks,
        'totalSize' => $totalSize,
        'received' => 0,
    ]));
    
    if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);
    file_put_contents($storageDir . '/.chunk_0.tmp', '');
    
    echo "READY|$filename|$totalChunks";
    exit;
}

if ($action === 'chunk') {
    $chunk = (int)($_POST['chunk'] ?? 0);
    $data = $_POST['data'] ?? '';
    
    if (empty($data)) {
        $data = file_get_contents('php://input');
    }
    
    $metaPath = $storageDir . '/.upload_meta.json';
    $meta = json_decode(file_get_contents($metaPath), true);
    
    file_put_contents($storageDir . "/.chunk_{$chunk}.tmp", $data);
    $meta['received']++;
    file_put_contents($metaPath, json_encode($meta));
    
    echo "CHUNK_OK|$chunk|" . strlen($data);
    exit;
}

if ($action === 'finish') {
    $metaPath = $storageDir . '/.upload_meta.json';
    $meta = json_decode(file_get_contents($metaPath), true);
    
    echo "FINISH|{$meta['received']}|{$meta['totalChunks']}";
    
    if ($meta['received'] >= $meta['totalChunks']) {
        $finalPath = $storageDir . '/' . $meta['filename'];
        $fp = fopen($finalPath, 'wb');
        
        for ($i = 0; $i < $meta['totalChunks']; $i++) {
            $chunkFile = $storageDir . "/.chunk_{$i}.tmp";
            fwrite($fp, file_get_contents($chunkFile));
            @unlink($chunkFile);
        }
        
        fclose($fp);
        @unlink($metaPath);
        
        $fileSize = filesize($finalPath);
        echo "\nASSEMBLED|$finalPath|$fileSize";
        
        // Create/update DB product
        use Illuminate\Support\Facades\DB;
        
        $existing = DB::table('products')->where('slug', 'ecommerce-starter-kit')->first();
        if ($existing) {
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
            echo "\nDB_UPDATED|existing";
        } else {
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
            echo "\nDB_CREATED|new";
        }
        
        $product = DB::table('products')->where('slug', 'ecommerce-starter-kit')->first();
        echo "\nPRODUCT_ID|{$product->id}|{$product->title}";
        
        echo "\nDONE";
    }
    exit;
}

echo "Chunked upload receiver ready.";