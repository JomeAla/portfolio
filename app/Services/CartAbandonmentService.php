<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Lead;
use App\Models\EmailSequence;
use App\Models\EmailQueue;

class CartAbandonmentService
{
    public function detectAbandonedCarts(int $hoursThreshold = 1): array
    {
        $cutoff = now()->subHours($hoursThreshold);

        $abandonedOrders = Order::where('payment_status', 'pending')
            ->whereNotNull('cart_started_at')
            ->where('cart_started_at', '<', $cutoff)
            ->where('is_cart_abandoned', false)
            ->get();

        $count = 0;
        foreach ($abandonedOrders as $order) {
            $order->markCartAbandoned();
            $this->enrollInAbandonmentSequence($order);
            $count++;
        }

        Log::info("CartAbandonmentService: Marked {$count} carts as abandoned");

        return ['marked' => $count];
    }

    public function detectAbandonedCheckouts(int $minutesThreshold = 30): array
    {
        $cutoff = now()->subMinutes($minutesThreshold);

        $abandonedCheckouts = Order::where('payment_status', 'pending')
            ->whereNotNull('checkout_started_at')
            ->where('checkout_started_at', '<', $cutoff)
            ->whereNull('checkout_abandoned_at')
            ->get();

        $count = 0;
        foreach ($abandonedCheckouts as $order) {
            $order->markCheckoutAbandoned();
            $count++;
        }

        Log::info("CartAbandonmentService: Marked {$count} checkouts as abandoned");

        return ['marked' => $count];
    }

    public function enrollInAbandonmentSequence(Order $order): bool
    {
        try {
            $sequence = EmailSequence::where('name', 'LIKE', '%Cart Abandonment%')
                ->where('is_active', true)
                ->first();

            if (!$sequence) {
                $sequence = EmailSequence::where('name', 'LIKE', '%cart%')
                    ->where('is_active', true)
                    ->first();
            }

            if (!$sequence) {
                Log::info("CartAbandonmentService: No active cart abandonment sequence found");
                return false;
            }

            $lead = Lead::firstOrCreate(
                ['email' => $order->customer_email],
                [
                    'name' => $order->customer_name,
                    'source' => 'cart_abandonment',
                    'score' => 15,
                ]
            );

            $lead->tags()->firstOrCreate(['name' => 'Cart Abandonment']);
            $lead->addScore(15);

            $existingQueue = EmailQueue::where('lead_id', $lead->id)
                ->whereHas('step', function ($q) use ($sequence) {
                    $q->where('sequence_id', $sequence->id);
                })
                ->where('status', 'pending')
                ->exists();

            if ($existingQueue) {
                Log::info("CartAbandonmentService: Lead {$lead->email} already in abandonment sequence");
                return false;
            }

            $steps = $sequence->steps()->orderBy('step_order')->get();

            foreach ($steps as $step) {
                $delayHours = $step->delay_days * 24 + ($step->delay_hours ?? 0);

                if ($step->step_order == 1) {
                    $delayHours = max($delayHours, 1);
                }

                EmailQueue::create([
                    'lead_id' => $lead->id,
                    'sequence_step_id' => $step->id,
                    'subject' => $step->subject ?? "You left something behind!",
                    'body' => $step->body ?? $this->getDefaultAbandonmentEmail($order, $step->step_order),
                    'status' => 'pending',
                    'scheduled_send_time' => now()->addHours($delayHours),
                ]);
            }

            Log::info("CartAbandonmentService: Enrolled {$lead->email} in abandonment sequence");
            return true;
        } catch (\Exception $e) {
            Log::error("CartAbandonmentService: Error enrolling in sequence: " . $e->getMessage());
            return false;
        }
    }

    public function recoverCart(Order $order): bool
    {
        try {
            $order->markCartRecovered();

            EmailQueue::whereHas('lead', function ($q) use ($order) {
                $q->where('email', $order->customer_email);
            })
            ->where('status', 'pending')
            ->whereHas('step', function ($q) {
                $q->whereHas('sequence', function ($sq) {
                    $sq->where('name', 'LIKE', '%Cart Abandonment%')
                      ->orWhere('name', 'LIKE', '%cart%');
                });
            })
            ->delete();

            $lead = Lead::where('email', $order->customer_email)->first();
            if ($lead) {
                $lead->tags()->syncWithoutDetaching(
                    Tag::firstOrCreate(['name' => 'Cart Recovered'])->id
                );
                $lead->removeScore(5);
            }

            Log::info("CartAbandonmentService: Cart recovered for order {$order->order_number}");
            return true;
        } catch (\Exception $e) {
            Log::error("CartAbandonmentService: Error recovering cart: " . $e->getMessage());
            return false;
        }
    }

    private function getDefaultAbandonmentEmail(Order $order, int $stepNumber): string
    {
        $productName = $order->product?->title ?? 'your item';
        $amount = number_format($order->final_amount ?? $order->amount ?? 0);
        $checkoutUrl = url('/buy/' . ($order->product?->slug ?? ''));

        $emails = [
            1 => "<h2>Hey {{name}}, did you forget something?</h2>
                <p>You left <strong>{$productName}</strong> in your cart at Joala Ventures.</p>
                <p>Complete your purchase now for just <strong>₦{$amount}</strong>.</p>
                <p><a href='{$checkoutUrl}' style='display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;'>Complete Your Purchase</a></p>
                <p>Still thinking about it? No worries — we'll save your cart for you.</p>",

            2 => "<h2>Still thinking about it, {{name}}?</h2>
                <p>Your cart with <strong>{$productName}</strong> is still waiting for you.</p>
                <p>Here's a quick reminder of what you're missing:</p>
                <ul><li>{$productName} - ₦{$amount}</li></ul>
                <p><a href='{$checkoutUrl}' style='display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;'>Get It Now</a></p>",

            3 => "<h2>Last call, {{name}}! 🏃</h2>
                <p>This is your final reminder about <strong>{$productName}</strong>.</p>
                <p>Your cart will expire soon. Don't miss out!</p>
                <p><a href='{$checkoutUrl}' style='display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;'>Grab It Before It's Gone</a></p>",
        ];

        return $emails[$stepNumber] ?? $emails[1];
    }

    public function getAbandonmentStats(): array
    {
        $totalCarts = Order::whereNotNull('cart_started_at')->count();
        $abandonedCarts = Order::where('is_cart_abandoned', true)->count();
        $recoveredCarts = Order::whereNotNull('cart_recovered_at')->count();

        $recoveryRate = $totalCarts > 0 ? round(($recoveredCarts / $totalCarts) * 100, 1) : 0;

        $lostRevenue = Order::where('is_cart_abandoned', true)
            ->where('payment_status', 'pending')
            ->sum('final_amount');

        $recoveredRevenue = Order::whereNotNull('cart_recovered_at')
            ->where('payment_status', 'success')
            ->sum('final_amount');

        return [
            'total_carts' => $totalCarts,
            'abandoned_carts' => $abandonedCarts,
            'recovered_carts' => $recoveredCarts,
            'recovery_rate' => $recoveryRate,
            'lost_revenue' => $lostRevenue,
            'recovered_revenue' => $recoveredRevenue,
        ];
    }
}