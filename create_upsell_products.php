<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Creating Upsell Products ===\n\n";

// Create product 1: Premium Bundle
$exists1 = DB::table('products')->where('slug', 'email-marketing-premium-bundle')->first();
if (!$exists1) {
    DB::table('products')->insertGetId([
        'title' => 'Email Marketing Premium Bundle',
        'slug' => 'email-marketing-premium-bundle',
        'short_description' => 'Everything you need for complete email marketing success',
        'description' => "The ultimate package for serious Nigerian entrepreneurs.\n\n**What's Included:**\n- Email Templates Pack (₦15,000)\n- Done-For-You Setup (₦50,000)\n- Priority Support (₦25,000)\n- Bonus materials\n\n**Total Value: ₦90,000+\nYour Price: ₦65,000**\n\nOne-time payment. Instant access.",
        'type' => 'bundle',
        'price' => 65000,
        'sale_price' => 65000,
        'file_path' => 'uploads/products/files/premium-bundle.html',
        'is_active' => 1,
        'is_featured' => 0,
        'order' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Created: Email Marketing Premium Bundle (₦65,000)\n";
} else {
    echo "⏭ Already exists: Email Marketing Premium Bundle\n";
}

// Create product 2: Done-For-You Service
$exists2 = DB::table('products')->where('slug', 'done-for-you-email-automation')->first();
if (!$exists2) {
    DB::table('products')->insertGetId([
        'title' => 'Done-For-You Email Automation',
        'slug' => 'done-for-you-email-automation',
        'short_description' => 'We build your complete email marketing system while you focus on your business',
        'description' => "The Done-For-You service for busy entrepreneurs.\n\n**What You Get:**\n- 3 Custom email sequences built\n- Full implementation & testing\n- Connected to your email provider\n- 30 days priority support\n- Documentation & training\n\n**Investment: ₦150,000**\n\nOne-time payment. Results guaranteed.",
        'type' => 'service',
        'price' => 150000,
        'sale_price' => 150000,
        'file_path' => 'uploads/products/files/done-for-you.html',
        'is_active' => 1,
        'is_featured' => 0,
        'order' => 3,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✓ Created: Done-For-You Email Automation (₦150,000)\n";
} else {
    echo "⏭ Already exists: Done-For-You Email Automation\n";
}

// Show all products
echo "\nAll Products in Store:\n";
$products = DB::table('products')->where('is_active', 1)->orderBy('order')->get();
foreach($products as $p) {
    echo "  - {$p->title} (₦{$p->sale_price}) [{$p->slug}]\n";
}

echo "\n=== Done ===\n";
echo "Visit: https://joala.com.ng/store\n";
echo "</pre>";