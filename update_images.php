<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Update Product Images ===\n\n";

// Update images with correct path
DB::table('products')->where('slug', 'email-marketing-premium-bundle')->update([
    'image' => '/uploads/products/premium-bundle-cover.svg'
]);

DB::table('products')->where('slug', 'done-for-you-email-automation')->update([
    'image' => '/uploads/products/done-for-you-cover.svg'
]);

echo "Updated image paths\n";

// Show all products
$products = DB::table('products')->where('is_active', 1)->orderBy('order')->get();
echo "\nProducts:\n";
foreach($products as $p) {
    echo "  - {$p->title}\n";
    echo "    Image: " . ($p->image ?: 'NONE') . "\n";
}

echo "\nDone!\n";
echo "</pre>";