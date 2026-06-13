<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $exists = DB::table('products')->where('title', 'LIKE', '%Email Sequence Templates%')->first();
        
        if (!$exists) {
            DB::table('products')->insert([
                'title' => 'Email Sequence Templates Pack',
                'slug' => 'email-sequence-templates-pack',
                'short_description' => '6 ready-to-use email sequences with 24 tested templates for maximum conversions',
                'description' => "# Email Sequence Templates Pack

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
- Tested subject lines included",
                'type' => 'ebook',
                'price' => 15000.00,
                'sale_price' => 12000.00,
                'file_path' => 'uploads/products/files/email-sequence-templates-pack.html',
                'image' => 'uploads/products/email-templates-cover.svg',
                'is_active' => 1,
                'is_featured' => 1,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('products')->where('slug', 'email-sequence-templates-pack')->delete();
    }
};