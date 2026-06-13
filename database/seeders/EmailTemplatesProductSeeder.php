<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmailTemplatesProductSeeder extends Seeder
{
    public function run()
    {
        // Check if product already exists
        $exists = DB::table('products')->where('title', 'LIKE', '%Email Sequence Templates%')->first();
        
        if ($exists) {
            echo "Product already exists!";
            return;
        }

        // Create product
        $productId = DB::table('products')->insertGetId([
            'title' => 'Email Sequence Templates Pack',
            'short_description' => '6 ready-to-use email sequences with 24 tested templates for maximum conversions',
            'description' => <<<EOT
# Email Sequence Templates Pack

Stop writing emails from scratch. This comprehensive pack gives you 6 complete email sequences with 24 tested, high-converting templates.

## What's Inside:

**6 Email Sequences:**
1. Welcome Series (5 emails) - Build relationships from day one
2. Abandoned Cart (3 emails) - Recover lost sales
3. Re-engagement (4 emails) - Win back inactive subscribers
4. Webinar Follow-up (5 emails) - Convert webinar attendees to customers
5. Product Launch (4 emails) - Launch new products with maximum impact
6. Thank You & Upsell (3 emails) - Maximize customer lifetime value

## Features:
- Copy & paste ready templates
- Easy customization with [placeholders]
- Industry best practices embedded
- Pro tips for maximum results
- Tested subject lines included

## Why This Product:
- Save 20+ hours of work
- Professional, high-converting copy
- Increase email open rates by up to 40%
- Templates work for any business type

## Who This Is For:
- Entrepreneurs
- Small business owners
- Digital marketers
- Coaches and consultants
- E-commerce store owners
EOT
,
            'type' => 'ebook',
            'price' => 15000.00,
            'sale_price' => 12000.00,
            'file_path' => 'uploads/products/files/email-sequence-templates-pack.html',
            'is_active' => 1,
            'is_featured' => 1,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "Product created with ID: " . $productId;
    }
}