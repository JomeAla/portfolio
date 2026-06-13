<?php
// Simple test - no Laravel
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Product Page</h1>";

// Load Laravel manually
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "<h2>Testing Product Load</h2>";

try {
    $product = Product::where('slug', 'email-sequence-templates-pack')->first();
    
    if ($product) {
        echo "<p>✅ Product Found!</p>";
        echo "<p><strong>Title:</strong> " . $product->title . "</p>";
        echo "<p><strong>Price:</strong> ₦" . number_format($product->price) . "</p>";
        echo "<p><strong>Sale Price:</strong> ₦" . number_format($product->sale_price) . "</p>";
        echo "<p><strong>File:</strong> " . $product->file_path . "</p>";
    } else {
        echo "<p>❌ Product not found</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}