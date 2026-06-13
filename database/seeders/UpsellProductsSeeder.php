<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpsellProductsSeeder extends Seeder
{
    public function run()
    {
        // Create Premium Bundle upsell product
        $premiumExists = DB::table('products')->where('title', 'LIKE', '%Premium Bundle%')->first();
        
        if (!$premiumExists) {
            DB::table('products')->insertGetId([
                'title' => 'Email Marketing Premium Bundle',
                'slug' => 'email-marketing-premium-bundle',
                'short_description' => 'Everything you need for complete email marketing success',
                'description' => <<<EOT
# Email Marketing Premium Bundle

The ultimate package for serious Nigerian entrepreneurs who want to dominate their inbox and convert more customers.

## What's Included:

**Email Templates Pack (₦15,000 value)**
- 24 Professionalemail templates
- 6 Complete sequences
- Implementation guide

**Done-For-You Setup (₦50,000 value)**
- We set up 3 email sequences for you
- Customized with your business details
- Connected to your email provider
- Tested and ready to go

**Priority Support (₦25,000 value)**
- 60-minute strategy call
- Priority email support for 30 days
- Access to private community

**Bonus:**
- Email marketing strategy playbook
- Subject line swipe file
- 50+ proven templates

Total Value: ₦90,000+
Your Price: ₦65,000

## Who This Is For:
- Entrepreneurs who want results fast
- Those who don't have time to set up themselves
- Businesses ready to scale with email marketing
EOT
,
                'type' => 'bundle',
                'price' => 65000.00,
                'sale_price' => 65000.00,
                'file_path' => 'uploads/products/files/premium-bundle.html',
                'is_active' => 1,
                'is_featured' => 0,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "Premium Bundle product created!\n";
        }
        
        // Create Done-For-You Service
        $serviceExists = DB::table('products')->where('title', 'LIKE', '%Done-For-You Email%')->first();
        
        if (!$serviceExists) {
            DB::table('products')->insertGetId([
                'title' => 'Done-For-You Email Automation Service',
                'slug' => 'done-for-you-email-automation',
                'short_description' => 'We build your complete email marketing system while you focus on your business',
                'description' => <<<EOT
# Done-For-You Email Automation Service

Stop struggling with email marketing. Let us build your complete system while you focus on running your business.

## What You Get:

### Phase 1: Discovery & Strategy (Day 1-2)
- 30-minute strategy call
- Audit your current email marketing
- Custom strategy for your business

### Phase 2: Implementation (Day 3-7)
- 3 Custom email sequences built
- All templates customized
- Automation workflows connected
- Testing & optimization

### Phase 3: Handover (Day 8)
- Complete documentation
- Training video walkthrough
- 30-day support included

## What's Included:
- Welcome sequence (3-5 emails)
- Cart abandonment sequence (2-3 emails)  
- Re-engagement sequence (3-4 emails)
- All automations connected
- A/B testing setup

## Investment:
₦150,000 one-time payment

Includes:
- Full implementation
- 30 days priority support
- Documentation & training
- 90-day follow-up check-in

## Ready to Get Started?
Book your strategy call today to get started.
EOT
,
                'type' => 'service',
                'price' => 150000.00,
                'sale_price' => 150000.00,
                'file_path' => 'uploads/products/files/done-for-you-service.html',
                'is_active' => 1,
                'is_featured' => 0,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "Done-For-You Service product created!\n";
        }
        
        echo "Upsell products setup complete!\n";
    }
}