<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$products = DB::table('products')
    ->where('title', 'LIKE', '%WordPress%')
    ->orWhere('title', 'LIKE', '%wordpress%')
    ->orWhere('title', 'LIKE', '%WP%')
    ->get();

echo "WordPress-related Products:\n";
echo "===========================\n";
foreach ($products as $p) {
    echo "ID: {$p->id}\n";
    echo "Title: {$p->title}\n";
    echo "Price: {$p->price}\n";
    echo "Sale Price: {$p->sale_price}\n";
    echo "File Path: " . ($p->file_path ?? 'NONE SET') . "\n";
    echo "Is Active: {$p->is_active}\n";
    echo "---\n";
}

if (count($products) === 0) {
    echo "No WordPress products found in database.\n";
}