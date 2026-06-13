<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Adding Product Images ===\n\n";

// Add images to products
DB::table('products')->where('slug', 'email-marketing-premium-bundle')->update([
    'image' => 'premium-bundle-cover.svg'
]);
echo "✓ Added image to Premium Bundle\n";

DB::table('products')->where('slug', 'done-for-you-email-automation')->update([
    'image' => 'done-for-you-cover.svg'
]);
echo "✓ Added image to Done-For-You Service\n";

// Verify
$products = DB::table('products')->whereIn('slug', [
    'email-sequence-templates-pack',
    'email-marketing-premium-bundle', 
    'done-for-you-email-automation'
])->get();

echo "\nProduct Images:\n";
foreach($products as $p) {
    echo "  - {$p->title}: " . ($p->image ?: 'NO IMAGE') . "\n";
}

echo "\n=== Done ===\n";
echo "</pre>";