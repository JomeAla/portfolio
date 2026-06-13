<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Funnel Checkout Integration Check</h1>";

// Check products for WordPress Starter Kit
$product = DB::table('products')->where('slug', 'wordpress-starter-kit')->first();
if ($product) {
    echo "<h2>Product Found!</h2>";
    echo "<p>ID: {$product->id}</p>";
    echo "<p>Title: {$product->title}</p>";
    echo "<p>Price: {$product->price}</p>";
    echo "<p>Sale Price: {$product->sale_price}</p>";
    echo "<p>File Path: {$product->file_path}</p>";
    echo "<p>Checkout URL: /buy/wordpress-starter-kit</p>";
} else {
    echo "<h2>Product NOT FOUND - Need to create it!</h2>";
}

// Check funnel 26 product link
$funnel = DB::table('funnels')->where('id', 26)->first();
echo "<h2>Funnel 26 Product Link</h2>";
echo "<p>Product ID: " . ($funnel->product_id ?? 'NOT SET') . "</p>";
echo "<p>Upsell Product ID: " . ($funnel->upsell_product_id ?? 'NOT SET') . "</p>";

// Check if we need to link product to funnel
if ($product && empty($funnel->product_id)) {
    echo "<h2>Linking Product to Funnel...</h2>";
    DB::table('funnels')->where('id', 26)->update(['product_id' => $product->id]);
    echo "<p>DONE - Product linked to funnel!</p>";
}

// Check sales page for buy link
echo "<h2>Sales Page Content</h2>";
$stages = DB::table('funnel_stages')->where('funnel_id', 26)->get();
foreach($stages as $s) {
    echo "<p><strong>{$s->name}</strong> ({$s->type}): {$s->content}</p>";
}