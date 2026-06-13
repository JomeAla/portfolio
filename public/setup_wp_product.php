<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>WordPress Starter Kit Product Setup</h2>";

// Check if product exists
$product = DB::table('products')->where('slug', 'wordpress-starter-kit')->first();

if ($product) {
    echo "<p style='color:green;'>[OK] Product already exists!</p>";
    echo "<pre>";
    print_r((array)$product);
    echo "</pre>";
} else {
    echo "<p style='color:orange;'>Product not found. Creating...</p>";
    
    // Create product
    $productId = DB::table('products')->insertGetId([
        'title' => 'WordPress Starter Kit',
        'short_description' => 'Build a professional WordPress website in minutes with 50+ templates, plugins & guides',
        'description' => <<<EOT
# WordPress Starter Kit

Everything you need to build a professional, conversion-ready WordPress website in minutes - not weeks.

## What's Included:

### 10 Pre-Built Homepage Designs
- Business, portfolio, e-commerce, blog & more

### 15+ Inner Page Templates
- About, services, contact, pricing, FAQ

### 20+ Essential Plugins
- SEO, forms, security, speed optimization

### Conversion-Ready Sections
- Hero banners, CTAs, testimonials, pricing tables

### Step-by-Step Setup Guide
- From installation to launch in 30 minutes

### SEO Checklist
- Rank higher on Google from day one

## Bonuses (Worth ₦15,000):
1. Email Marketing Bundle - 10 high-converting templates
2. Lead Capture Templates - Popups, landing pages, opt-in boxes
3. 90-Day Content Planner - 270 blog post ideas
4. Speed Optimization Checklist - Load in under 2 seconds
EOT
,
        'type' => 'ebook',
        'price' => 28000.00,
        'sale_price' => 12000.00,
        'slug' => 'wordpress-starter-kit',
        'file_path' => 'uploads/products/files/wordpress-starter-kit-premium.zip',
        'is_active' => 1,
        'is_featured' => 1,
        'order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "<p style='color:green;'>[OK] Product created with ID: " . $productId . "</p>";
}

// Verify checkout route works
echo "<h3>Checkout URL:</h3>";
echo "<p><a href='/checkout/wordpress-starter-kit' target='_blank'>Test Checkout</a></p>";

// Check if file exists
$filePath = storage_path('app/public/uploads/products/files/wordpress-starter-kit-premium.zip');
if (file_exists($filePath)) {
    echo "<p style='color:green;'>[OK] Product file exists!</p>";
} else {
    echo "<p style='color:red;'>[FAIL] Product file NOT found at: " . $filePath . "</p>";
    echo "<p>Note: The product will still work, but customers won't be able to download the file after purchase.</p>";
}