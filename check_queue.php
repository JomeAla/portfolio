<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\EmailQueue;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Email Queue Check ===\n\n";

// Get latest lead
$lead = Lead::latest()->first();
if (!$lead) {
    echo "No test leads found!\n";
    exit;
}

echo "Lead: " . $lead->email . "\n";
echo "Sequence ID: " . $lead->sequence_id . "\n\n";

echo "Email Queue:\n";
$queues = EmailQueue::where('lead_id', $lead->id)->get();
foreach($queues as $q) {
    echo "  Step " . $q->sequence_step_id . ": " . $q->scheduled_send_time . " - " . $q->status . "\n";
}

echo "\nTotal queued: " . $queues->count() . "\n";
echo "</pre>";