<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>Fix Landing Page Sequence</h2>";

// Check all sequences
echo "<h3>All Email Sequences:</h3>";
$sequences = DB::table('email_sequences')->orderBy('id', 'DESC')->limit(20)->get();
foreach ($sequences as $seq) {
    echo "<p>ID: {$seq->id} - {$seq->name}</p>";
}

// Get current landing page
echo "<h3>Current Landing Page:</h3>";
$lp = DB::table('landing_pages')->where('slug', 'free-wordpress-starter-kit')->first();
echo "<p>ID: {$lp->id}, sequence_id: {$lp->sequence_id}, funnel_id: {$lp->funnel_id}</p>";

// Get sequences that have steps
echo "<h3>Sequences with steps:</h3>";
$seqsWithSteps = DB::table('sequence_steps')
    ->select('sequence_id')
    ->groupBy('sequence_id')
    ->pluck('sequence_id')
    ->toArray();
print_r($seqsWithSteps);

// Try using sequence 5 (should exist)
if (in_array(5, $seqsWithSteps)) {
    echo "<p>Using sequence 5...</p>";
    DB::table('landing_pages')->where('id', $lp->id)->update(['sequence_id' => 5]);
    echo "<p>Updated!</p>";
} else if (count($seqsWithSteps) > 0) {
    $newSeqId = $seqsWithSteps[0];
    echo "<p>Using sequence {$newSeqId}...</p>";
    DB::table('landing_pages')->where('id', $lp->id)->update(['sequence_id' => $newSeqId]);
    echo "<p>Updated!</p>";
}

// Verify
$lp2 = DB::table('landing_pages')->where('slug', 'free-wordpress-starter-kit')->first();
echo "<p><strong>New sequence_id: {$lp2->sequence_id}</strong></p>";

// Show sequence steps for the new sequence
echo "<h3>Sequence Steps (ID {$lp2->sequence_id}):</h3>";
$steps = DB::table('sequence_steps')->where('sequence_id', $lp2->sequence_id)->orderBy('step_order')->get();
foreach ($steps as $step) {
    echo "<p>Step {$step->step_order}: {$step->subject}</p>";
}