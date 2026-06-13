<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "<h1>Additional System Analysis</h1>";

echo "<h2>1. Orders Table Schema</h2>";
if (Schema::hasTable('orders')) {
    echo "<pre>" . print_r(DB::getSchemaBuilder()->getColumnListing('orders'), true) . "</pre>";
    echo "<p>Order count: " . DB::table('orders')->count() . "</p>";
}

echo "<h2>2. Payments Table Schema</h2>";
if (Schema::hasTable('payments')) {
    echo "<pre>" . print_r(DB::getSchemaBuilder()->getColumnListing('payments'), true) . "</pre>";
    echo "<p>Payment count: " . DB::table('payments')->count() . "</p>";
}

echo "<h2>3. Payment Status Counts</h2>";
if (Schema::hasTable('payments')) {
    $statuses = DB::table('payments')->select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
    foreach ($statuses as $s) {
        echo "<li>{$s->status}: {$s->count}</li>";
    }
}

echo "<h2>4. Order Status</h2>";
if (Schema::hasTable('orders')) {
    $statuses = DB::table('orders')->select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
    foreach ($statuses as $s) {
        echo "<li>{$s->status}: {$s->count}</li>";
    }
}

echo "<h2>5. Check for existing payment packages</h2>";
$composer = file_get_contents('/home/joalacom/www/composer.json');
$deps = json_decode($composer, true);
echo "<h3>Installed packages:</h3><ul>";
foreach ($deps['require'] ?? [] as $pkg => $ver) {
    if (stripos($pkg, 'paystack') !== false || stripos($pkg, 'flutter') !== false || stripos($pkg, 'payment') !== false || stripos($pkg, 'stripe') !== false) {
        echo "<li>{$pkg}: {$ver}</li>";
    }
}
echo "</ul>";

echo "<h2>6. Blog Posts for Content</h2>";
$posts = DB::table('blog_posts')->select('id', 'title', 'slug', 'status')->limit(5)->get();
foreach ($posts as $p) {
    echo "<li>{$p->id}: {$p->title} ({$p->status})</li>";
}