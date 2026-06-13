<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$products = DB::table('products')
    ->where('is_active', 1)
    ->orderBy('price', 'DESC')
    ->get();

echo "Active Products in Store (Price in NGN):\n";
echo "==========================================\n\n";

foreach ($products as $p) {
    echo "ID: {$p->id}\n";
    echo "Title: {$p->title}\n";
    echo "Price: " . number_format($p->price) . " NGN\n";
    echo "Sale Price: " . ($p->sale_price ? number_format($p->sale_price) . " NGN" : "None") . "\n";
    echo "---\n";
}