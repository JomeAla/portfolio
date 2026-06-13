<?php
/**
 * Cart Abandonment Processor
 * Run this via cron to detect and process abandoned carts
 * 
 * Usage: Add cron job like:
 * 0 * * * * curl https://joala.com.ng/process-cart-abandonment.php
 * 
 * Or run directly: php process-cart-abandonment.php
 */

require __DIR__ . '/bootstrap/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\Lead;
use App\Models\EmailQueue;
use App\Models\SequenceStep;
use App\Models\EmailSequence;
use Illuminate\Support\Facades\Log;

echo "<h2>Cart Abandonment Processor</h2>";
echo "<p>Running at: " . now() . "</p>";

$abandonedCarts = [];
$recoveredCount = 0;
$notifiedCount = 0;

// 1. Mark carts as abandoned (no checkout after 1 hour)
$pendingCarts = Order::whereNull('payment_status')
    ->whereNotNull('cart_started_at')
    ->where('is_cart_abandoned', false)
    ->where('cart_started_at', '<', now()->subHours(1))
    ->get();

echo "<h3>Step 1: Marking Abandoned Carts</h3>";
echo "<p>Found {$pendingCarts->count()} carts to check</p>";

foreach ($pendingCarts as $cart) {
    $cart->update([
        'cart_abandoned_at' => now(),
        'is_cart_abandoned' => true,
    ]);
    $abandonedCarts[] = $cart->order_number;
    echo "<p>Marked as abandoned: {$cart->order_number} - {$cart->customer_email}</p>";
}

// 2. Mark checkouts as abandoned (no payment after 30 min of starting checkout)
$pendingCheckouts = Order::whereNull('payment_status')
    ->whereNotNull('checkout_started_at')
    ->where('checkout_abandoned_at', null)
    ->where('checkout_started_at', '<', now()->subMinutes(30))
    ->get();

echo "<h3>Step 2: Marking Checkout Abandonment</h3>";
echo "<p>Found {$pendingCheckouts->count()} checkouts to check</p>";

foreach ($pendingCheckouts as $checkout) {
    $checkout->update([
        'checkout_abandoned_at' => now(),
    ]);
    echo "<p>Marked checkout as abandoned: {$checkout->order_number}</p>";
}

// 3. Recover carts that now have successful payment
$recentOrders = Order::where('payment_status', 'success')
    ->where('is_cart_abandoned', true)
    ->where('cart_recovered_at', null)
    ->get();

echo "<h3>Step 3: Recovering Carts</h3>";
echo "<p>Found {$recentOrders->count()} recovered carts</p>";

foreach ($recentOrders as $order) {
    $order->update([
        'cart_recovered_at' => now(),
        'is_cart_abandoned' => false,
    ]);
    $recoveredCount++;
    
    // Cancel any pending abandonment emails
    $lead = Lead::where('email', $order->customer_email)->first();
    if ($lead) {
        EmailQueue::where('lead_id', $lead->id)
            ->where('status', 'pending')
            ->whereHas('step', function($q) {
                $q->whereHas('sequence', function($s) {
                    $s->where('trigger_type', 'cart_abandonment');
                });
            })
            ->delete();
    }
    echo "<p>Recovered: {$order->order_number}</p>";
}

// 4. Send recovery emails for newly abandoned carts
$sequence = EmailSequence::where('trigger_type', 'cart_abandonment')
    ->where('is_active', true)
    ->first();

if ($sequence && count($abandonedCarts) > 0) {
    echo "<h3>Step 4: Sending Abandonment Emails</h3>";
    
    foreach ($abandonedCarts as $orderNumber) {
        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) continue;
        
        // Create or update lead
        $lead = Lead::firstOrCreate(
            ['email' => $order->customer_email],
            [
                'name' => $order->customer_name,
                'source' => 'cart_abandonment',
            ]
        );
        
        // Add tag
        $lead->tags()->firstOrCreate(['name' => 'Cart Abandonment']);
        
        // Cancel any existing pending emails for this sequence
        EmailQueue::where('lead_id', $lead->id)
            ->where('status', 'pending')
            ->delete();
        
        // Queue new emails
        $steps = $sequence->steps()->orderBy('step_order')->get();
        $delayHours = [1, 24, 72]; // 1 hour, 1 day, 3 days
        
        $stepNum = 0;
        foreach ($steps as $step) {
            $delay = $delayHours[$stepNum] ?? ($step->delay_days * 24);
            EmailQueue::create([
                'lead_id' => $lead->id,
                'sequence_step_id' => $step->id,
                'scheduled_at' => now()->addHours($delay),
                'status' => 'pending',
            ]);
            $notifiedCount++;
            $stepNum++;
        }
        
        echo "<p>Queued {$steps->count()} emails for: {$order->customer_email}</p>";
    }
}

// Summary
echo "<h2>Summary</h2>";
echo "<ul>";
echo "<li>Carts marked abandoned: " . count($abandonedCarts) . "</li>";
echo "<li>Carts recovered: $recoveredCount</li>";
echo "<li>Emails queued: $notifiedCount</li>";
echo "</ul>";

// Log results
Log::info("Cart Abandonment Processor: " . count($abandonedCarts) . " marked, $recoveredCount recovered, $notifiedCount notified");

echo "<p style='color:green'>✓ Process completed!</p>";