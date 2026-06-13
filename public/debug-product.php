<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Support\Facades\Log;

Log::info('DEBUG: Running all product queries');
error_log('DEBUG: Running all product queries');

$slug = 'ecommerce-starter-kit';

// Test 1: Exact slug
$product = Product::where('slug', $slug)->first();
Log::info('Test1: Product by slug', ['slug' => $slug, 'found' => $product ? ['id'=>$product->id, 'title'=>$product->title, 'price'=>$product->price] : null]);
error_log('Test1: ' . ($product ? $product->id : 'NULL'));

// Test 2: List all products
$all = Product::select(['id', 'title', 'slug'])->limit(10)->get();
Log::info('All products', ['count' => $all->count(), 'products' => $all->toArray()]);
error_log('Count: ' . $all->count());
foreach ($all as $p) {
    error_log('Product: ' . $p->id . ' - ' . $p->title . ' (' . $p->slug . ')');
}

header('Content-Type: application/json');
echo json_encode([
    'test1_slug' => $slug,
    'test1_found' => $product ? ['id' => $product->id, 'title' => $product->title, 'slug' => $product->slug] : null,
    'all_products_count' => $all->count(),
    'all_products' => $all->toArray(),
], JSON_PRETTY_PRINT);