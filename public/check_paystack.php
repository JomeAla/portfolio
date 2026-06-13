<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Paystack Settings Check</h1>";

$keys = ['paystack_public_key', 'paystack_secret_key', 'paystack_merchant_email', 'paystack_public', 'paystack_secret'];

foreach($keys as $key) {
    $val = DB::table('settings')->where('key', $key)->first();
    if ($val) {
        echo "<p><strong>{$key}</strong>: " . (strlen($val->value) > 5 ? substr($val->value, 0, 15) . '...' : $val->value) . "</p>";
    } else {
        echo "<p><strong>{$key}</strong>: NOT SET</p>";
    }
}

echo "<h2>Orders with Payment Status</h2>";
$orders = DB::table('orders')->select('id', 'order_number', 'payment_status', 'payment_reference', 'created_at')->get();
foreach($orders as $o) {
    echo "<p>Order {$o->order_number}: {$o->payment_status} (ref: {$o->payment_reference})</p>";
}