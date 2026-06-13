<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Coupon;

echo "<h1>Check Coupons</h1>";

$coupons = Coupon::all();
echo "<p>Total coupons: " . $coupons->count() . "</p>";

if ($coupons->count() == 0) {
    echo "<h2>Creating test coupon...</h2>";
    
    // Create a test coupon
    Coupon::create([
        'code' => 'TEST20',
        'description' => '20% off for testing',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'min_order_amount' => 1000,
        'max_discount' => 5000,
        'usage_limit' => 100,
        'used_count' => 0,
        'valid_from' => now(),
        'valid_until' => now()->addMonths(3),
        'is_active' => true,
    ]);
    
    echo "<p>Created TEST20 coupon - 20% off!</p>";
} else {
    foreach ($coupons as $c) {
        echo "<p><strong>{$c->code}</strong>: {$c->discount_type} = {$c->discount_value}, Active: " . ($c->is_active ? 'Yes' : 'No') . "</p>";
    }
}

echo "<p>DONE!</p>";