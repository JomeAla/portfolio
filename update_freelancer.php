<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating Freelancer Toolkit file path...\n\n";

$result = DB::table('products')
    ->where('title', 'LIKE', '%Freelancer%')
    ->update(['file_path' => 'uploads/products/files/freelancer-toolkit.zip']);

echo "Updated {$result} product(s)\n\n";

$product = DB::table('products')
    ->where('title', 'LIKE', '%Freelancer%')
    ->first();

echo "Updated Product Details:\n";
echo "=======================\n";
echo "ID: {$product->id}\n";
echo "Title: {$product->title}\n";
echo "File Path: {$product->file_path}\n";
echo "Price: " . number_format($product->price) . " NGN\n";
echo "Sale Price: " . number_format($product->sale_price) . " NGN\n\n";

echo "Automated delivery is now configured!\n";
echo "Customers will receive a download link after purchase.\n";