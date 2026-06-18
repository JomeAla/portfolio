<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Subscription;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';
    protected $description = 'Check for expiring and expired subscriptions and notify customers';

    public function handle(): int
    {
        // Mark expired subscriptions
        $expired = Subscription::where('status', 'active')
            ->where('current_period_end', '<', now())
            ->get();

        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);

            try {
                DB::table('customer_notifications')->insert([
                    'customer_email' => $sub->customer_email,
                    'type' => 'general',
                    'title' => 'Subscription Expired',
                    'message' => 'Your subscription has expired. Renew now to continue enjoying premium features.',
                    'link' => '/customer/subscriptions',
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {}
        }

        // Notify about subscriptions expiring in 3 days
        $expiringSoon = Subscription::where('status', 'active')
            ->where('current_period_end', '>', now())
            ->where('current_period_end', '<', now()->addDays(3))
            ->get();

        foreach ($expiringSoon as $sub) {
            try {
                DB::table('customer_notifications')->insert([
                    'customer_email' => $sub->customer_email,
                    'type' => 'general',
                    'title' => 'Subscription Expiring Soon',
                    'message' => 'Your subscription will expire on ' . $sub->current_period_end->format('M d, Y') . '. Renew now to avoid interruption.',
                    'link' => '/customer/subscriptions',
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {}
        }

        $this->info("Marked {$expired->count()} subscription(s) as expired.");
        $this->info("Notified {$expiringSoon->count()} customer(s) about upcoming expiry.");

        return 0;
    }
}
