<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating WordPress Starter Kit product file path...\n\n";

// Update the product file_path to point to the correct zip file
$result = DB::table('products')
    ->where('title', 'LIKE', '%WordPress Starter Kit%')
    ->update(['file_path' => 'uploads/products/files/wordpress-starter-kit-premium.zip']);

echo "Updated {$result} product(s)\n\n";

// Verify
$product = DB::table('products')
    ->where('title', 'LIKE', '%WordPress Starter Kit%')
    ->first();

echo "Updated Product Details:\n";
echo "=======================\n";
echo "ID: {$product->id}\n";
echo "Title: {$product->title}\n";
echo "File Path: {$product->file_path}\n";
echo "Price: \${$product->price}\n";
echo "Sale Price: \${$product->sale_price}\n\n";

echo "Automated delivery is now configured!\n";
echo "Customers will receive a download link after purchase.\n";