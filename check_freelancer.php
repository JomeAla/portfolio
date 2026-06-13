<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$product = DB::table('products')
    ->where('title', 'LIKE', '%Freelancer%')
    ->first();

echo "Freelancer Toolkit Product:\n";
echo "==========================\n";

if ($product) {
    echo "ID: {$product->id}\n";
    echo "Title: {$product->title}\n";
    echo "Price: " . number_format($product->price) . " NGN\n";
    echo "Sale Price: " . ($product->sale_price ? number_format($product->sale_price) . " NGN" : "None") . "\n";
    echo "File Path: " . ($product->file_path ?: "NOT SET") . "\n";
    echo "Is Active: " . ($product->is_active ? "Yes" : "No") . "\n";
    
    // Check if file exists
    if ($product->file_path) {
        $fullPath = storage_path('app/public/' . $product->file_path);
        if (file_exists($fullPath)) {
            echo "\nStatus: File EXISTS - Automated delivery will work!\n";
        } else {
            echo "\nStatus: File NOT FOUND - Needs to be uploaded!\n";
            echo "Expected path: {$fullPath}\n";
        }
    } else {
        echo "\nStatus: No file path set - needs configuration!\n";
    }
} else {
    echo "Product NOT FOUND in database!\n";
}