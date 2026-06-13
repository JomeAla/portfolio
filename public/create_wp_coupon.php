<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Coupon;

echo "<h1>Create Product-Specific Coupon</h1>";

// Get WordPress Starter Kit product
$product = Product::where('slug', 'wordpress-starter-kit')->first();
if ($product) {
    echo "<p>Product: {$product->title} - Price: {$product->price}</p>";
    
    // Create coupon specifically for this product
    $coupon = Coupon::create([
        'code' => 'WPSAVE30',
        'description' => '30% off WordPress Starter Kit',
        'discount_type' => 'percentage',
        'discount_value' => 30,
        'min_order_amount' => 5000,
        'max_discount' => 5000,
        'usage_limit' => 50,
        'used_count' => 0,
        'valid_from' => now(),
        'valid_until' => now()->addMonths(3),
        'is_active' => true,
        'product_id' => $product->id,
    ]);
    
    echo "<p>Created coupon: WPSAVE30 - 30% off (max 5000)</p>";
    
    // Calculate what the discount would be
    $price = $product->sale_price ?? $product->price;
    $discount = $coupon->calculateDiscount($price);
    $final = $price - $discount;
    echo "<p>Original: {$price} | Discount: {$discount} | Final: {$final}</p>";
} else {
    echo "<p>Product not found</p>";
}

echo "<p>DONE!</p>";