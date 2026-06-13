<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "<h1>System Analysis for Implementation Plan</h1>";

echo "<h2>1. Products Table Schema</h2>";
if (Schema::hasTable('products')) {
    $cols = DB::getSchemaBuilder()->getColumnListing('products');
    echo "<pre>" . print_r($cols, true) . "</pre>";
    echo "<p>Product count: " . DB::table('products')->count() . "</p>";
} else {
    echo "<p>Products table not found</p>";
}

echo "<h2>2. Orders & Checkout Tables</h2>";
$tables = ['orders', 'order_items', 'carts', 'cart_items', 'transactions', 'payments'];
foreach ($tables as $table) {
    echo "<li>{$table}: " . (Schema::hasTable($table) ? 'EXISTS' : 'NOT FOUND') . "</li>";
}

echo "<h2>3. Current Payment Settings</h2>";
$paymentSettings = DB::table('settings')->where('key', 'like', '%payment%')->orWhere('key', 'like', '%gateway%')->orWhere('key', 'like', '%paystack%')->orWhere('key', 'like', '%flutterwave%')->get();
if ($paymentSettings->count() > 0) {
    foreach ($paymentSettings as $s) {
        echo "<p><strong>{$s->key}</strong>: " . (strlen($s->value) > 3 ? substr($s->value, 0, 10) . '...' : $s->value) . "</p>";
    }
} else {
    echo "<p>No payment gateway settings found</p>";
}

echo "<h2>4. Blog/Content System</h2>";
echo "<p>Blog posts: " . DB::table('blog_posts')->count() . "</p>";
echo "<p>Categories: " . (Schema::hasTable('categories') ? DB::table('categories')->count() : 'N/A') . "</p>";

echo "<h2>5. User/Member System</h2>";
echo "<p>Users table exists: " . (Schema::hasTable('users') ? 'Yes' : 'No') . "</p>";
echo "<p>User roles columns: " . (Schema::hasTable('users') ? (Schema::hasColumn('users', 'role') ? 'has role' : (Schema::hasColumn('users', 'is_admin') ? 'has is_admin' : 'no role column')) : 'N/A') . "</p>";

echo "<h2>6. Existing Checkout Routes</h2>";
$routesFile = file_get_contents('/home/joalacom/www/routes/web.php');
preg_match_all('/Route::(get|post)\([\'\"](.*checkout|buy|purchase|payment)[\'\"]/i', $routesFile, $matches);
echo "<pre>" . print_r($matches[0] ?? [], true) . "</pre>";