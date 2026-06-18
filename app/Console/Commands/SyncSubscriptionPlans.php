<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriptionPlan;
use App\Services\PaystackSubscriptionService;

class SyncSubscriptionPlans extends Command
{
    protected $signature = 'membership:sync-plans';
    protected $description = 'Sync membership_tiers to subscription_plans and create Paystack plans';

    public function handle(): int
    {
        $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $stmt = $pdo->query("SELECT * FROM membership_tiers WHERE is_active = 1 ORDER BY sort_order, price");
        $tiers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($tiers)) {
            $this->warn("No active membership tiers found.");
            return 0;
        }

        $count = 0;
        foreach ($tiers as $tier) {
            $interval = match($tier['billing_period'] ?? 'monthly') {
                'yearly' => 'yearly',
                'quarterly' => 'quarterly',
                'weekly' => 'weekly',
                'one_time' => 'monthly',
                default => 'monthly',
            };

            $features = !empty($tier['features'])
                ? (is_string($tier['features']) ? json_decode($tier['features'], true) : $tier['features'])
                : [];

            $plan = SubscriptionPlan::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($tier['name'])],
                [
                    'name' => $tier['name'],
                    'description' => $tier['description'] ?? '',
                    'price' => $tier['price'] ?? 0,
                    'interval' => $interval,
                    'trial_days' => 0,
                    'features' => is_array($features) ? $features : [],
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => $tier['sort_order'] ?? 0,
                ]
            );

            if (!$plan->paystack_plan_code) {
                try {
                    app(PaystackSubscriptionService::class)->createPlan($plan);
                    $this->info("  Created Paystack plan for: {$tier['name']}");
                } catch (\Exception $e) {
                    $this->warn("  Could not create Paystack plan for {$tier['name']}: {$e->getMessage()}");
                }
            }

            $count++;
        }

        $this->info("Synced {$count} membership tier(s) to subscription plans.");
        return 0;
    }
}
