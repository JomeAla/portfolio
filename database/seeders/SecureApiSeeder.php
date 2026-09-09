<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SecureApiPlan;
use Illuminate\Database\Seeder;

/**
 * Secure API Gateway: plan catalog + store products (idempotent).
 * Prices in Nigerian Naira (NGN).
 */
class SecureApiSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free', 'name' => 'Free', 'price_monthly' => 0,
                'quota_monthly' => 100, 'rate_rpm' => 60, 'service_accounts' => 3,
                'features' => ['webhook_proxy' => false, 'analytics' => false, 'cli' => false, 'email_notifications' => false, 'agency' => false, 'white_label' => false, 'priority_support' => false],
                'is_default' => true, 'trial_days' => 0, 'grace_days' => 0,
            ],
            [
                'slug' => 'pro', 'name' => 'Pro', 'price_monthly' => 15000,
                'quota_monthly' => 5000, 'rate_rpm' => 600, 'service_accounts' => 20,
                'features' => ['webhook_proxy' => true, 'analytics' => true, 'cli' => true, 'email_notifications' => true, 'agency' => false, 'white_label' => false, 'priority_support' => true],
                'is_default' => false, 'trial_days' => 7, 'grace_days' => 3,
            ],
            [
                'slug' => 'enterprise', 'name' => 'Enterprise', 'price_monthly' => 50000,
                'quota_monthly' => -1, 'rate_rpm' => 5000, 'service_accounts' => -1,
                'features' => ['webhook_proxy' => true, 'analytics' => true, 'cli' => true, 'email_notifications' => true, 'agency' => true, 'white_label' => true, 'priority_support' => true],
                'is_default' => false, 'trial_days' => 0, 'grace_days' => 7,
            ],
            [
                'slug' => 'lifetime', 'name' => 'Lifetime', 'price_monthly' => 75000,
                'quota_monthly' => -1, 'rate_rpm' => 5000, 'service_accounts' => -1,
                'features' => ['webhook_proxy' => true, 'analytics' => true, 'cli' => true, 'email_notifications' => true, 'agency' => true, 'white_label' => true, 'priority_support' => true],
                'is_default' => false, 'trial_days' => 0, 'grace_days' => 0,
            ],
            [
                'slug' => 'agency', 'name' => 'Agency', 'price_monthly' => 40000,
                'quota_monthly' => 50000, 'rate_rpm' => 5000, 'service_accounts' => -1,
                'features' => ['webhook_proxy' => true, 'analytics' => true, 'cli' => true, 'email_notifications' => true, 'agency' => true, 'white_label' => false, 'priority_support' => true],
                'is_default' => false, 'trial_days' => 0, 'grace_days' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SecureApiPlan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }

        $products = [
            ['slug' => 'secure-api-pro', 'title' => 'WP Secure API Gateway — Pro', 'price' => 15000, 'description' => 'Pro plan: 5,000 requests/month, 600 RPM, 20 service accounts, webhook proxy, analytics, email notifications, priority support. ₦15,000/month.'],
            ['slug' => 'secure-api-enterprise', 'title' => 'WP Secure API Gateway — Enterprise', 'price' => 50000, 'description' => 'Enterprise plan: unlimited requests, 5,000 RPM, unlimited service accounts, agency + white-label. ₦50,000/month.'],
            ['slug' => 'secure-api-lifetime', 'title' => 'WP Secure API Gateway — Lifetime', 'price' => 75000, 'description' => 'Lifetime license: unlimited requests and all Full features forever. One-time ₦75,000.'],
            ['slug' => 'secure-api-agency', 'title' => 'WP Secure API Gateway — Agency', 'price' => 40000, 'description' => 'Agency plan: 10 client seats included (₦3,000/extra seat/month), unlimited requests, client management. ₦40,000/month.'],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['slug' => $product['slug']], [
                'title'            => $product['title'],
                'description'      => $product['description'],
                'price'            => $product['price'],
                'type'             => 'digital',
                'is_active'        => true,
            ]);
        }
    }
}
