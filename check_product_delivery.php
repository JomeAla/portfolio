<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$products = DB::table('products')
    ->where('title', 'LIKE', '%WordPress%')
    ->orWhere('title', 'LIKE', '%wordpress%')
    ->get();

echo "Checking WordPress Products:\n";
echo "============================\n\n";

foreach ($products as $p) {
    echo "Product ID: {$p->id}\n";
    echo "Title: {$p->title}\n";
    echo "Price: \${$p->price}\n";
    echo "Sale Price: " . ($p->sale_price ? "\${$p->sale_price}" : "None") . "\n";
    echo "File Path: " . ($p->file_path ?: "NOT SET - Needs to be configured!") . "\n";
    echo "Is Active: " . ($p->is_active ? "Yes" : "No") . "\n";
    echo "---\n";
    
    // Check if file actually exists in storage
    if ($p->file_path) {
        $fullPath = storage_path('app/public/' . $p->file_path);
        if (file_exists($fullPath)) {
            echo "File EXISTS at: {$fullPath}\n";
        } else {
            echo "File NOT FOUND at: {$fullPath}\n";
        }
    }
}

echo "\n\nHow automated delivery works:\n";
echo "==============================\n";
echo "1. Customer purchases product\n";
echo "2. Order is created with download token\n";
echo "3. Customer gets email with download link\n";
echo "4. Link expires after 24 hours\n";
echo "5. System reads file_path from product table\n";
echo "6. Serves file from storage/app/public/{file_path}\n";