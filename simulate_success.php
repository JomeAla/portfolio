<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\Lead;
use App\Models\EmailSequence;
use App\Models\EmailQueue;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Simulate Payment Success ===\n\n";

// Get the test order
$order = Order::where('customer_email', 'testpurchasefinal@email.com')->latest()->first();
if (!$order) {
    echo "No order found!\n";
    exit;
}

echo "Order: " . $order->order_number . "\n";
echo "Customer: " . $order->customer_email . "\n";
echo "Current Status: " . $order->payment_status . "\n";

// Update to success (simulate payment)
$order->update([
    'payment_status' => 'success',
    'payment_reference' => 'TEST_SIMULATED',
]);

echo "Updated to: success\n\n";

// Find post-purchase sequence
$sequence = EmailSequence::where('name', 'LIKE', '%Post Purchase%')
    ->orWhere('name', 'LIKE', '%Email Templates Pack%')
    ->where('is_active', true)
    ->first();

if (!$sequence) {
    echo "No post-purchase sequence found!\n";
    exit;
}

echo "Found sequence: " . $sequence->name . " (ID: " . $sequence->id . ")\n\n";

// Create or get lead
$lead = Lead::firstOrCreate(
    ['email' => $order->customer_email],
    [
        'name' => $order->customer_name,
        'source' => 'product_purchase',
        'score' => 10,
    ]
);

echo "Lead: " . $lead->email . " (ID: " . $lead->id . ")\n\n";

// Queue the emails
$steps = $sequence->steps()->orderBy('step_order')->get();
echo "Queueing " . $steps->count() . " emails:\n";

foreach($steps as $step) {
    $queue = EmailQueue::create([
        'lead_id' => $lead->id,
        'sequence_step_id' => $step->id,
        'scheduled_send_time' => now()->addDays($step->delay_days),
        'status' => 'pending',
    ]);
    echo "  Day " . $step->delay_days . ": " . $step->subject . " - Queued!\n";
}

echo "\n=== Done! ===\n";
echo "</pre>";