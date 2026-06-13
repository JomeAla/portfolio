<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Add product_id to coupons</h1>";

try {
    DB::statement('ALTER TABLE coupons ADD COLUMN product_id BIGINT UNSIGNED NULL AFTER is_active');
    echo "<p>Added product_id column</p>";
} catch (Exception $e) {
    echo "<p>Column may already exist: " . $e->getMessage() . "</p>";
}

// Now create the coupon
use App\Models\Product;
use App\Models\Coupon;

$product = Product::where('slug', 'wordpress-starter-kit')->first();
if ($product) {
    // Check if coupon exists
    $existing = Coupon::where('code', 'WPSAVE30')->first();
    if (!$existing) {
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
        echo "<p>Created WPSAVE30 coupon - 30% off</p>";
        
        $price = $product->sale_price ?? $product->price;
        $discount = $coupon->calculateDiscount($price);
        $final = $price - $discount;
        echo "<p>Price: {$price} | Discount: {$discount} | Final: {$final}</p>";
    } else {
        echo "<p>Coupon already exists</p>";
    }
}

echo "<p>DONE!</p>";