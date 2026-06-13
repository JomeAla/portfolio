<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>Quick Fix</h2>";

// 1. Set landing page sequence to NULL
DB::statement('UPDATE landing_pages SET sequence_id = NULL WHERE id = 18');
echo "<p>1. Set landing page sequence_id to NULL</p>";

// 2. Check funnel 2
$funnel = DB::table('funnels')->where('id', 2)->first();
echo "<p>2. Funnel 2: welcome_sequence_id = " . ($funnel->welcome_sequence_id ?? 'NULL') . ", followup_sequence_id = " . ($funnel->followup_sequence_id ?? 'NULL') . "</p>";

// 3. If funnel doesn't have sequence IDs, set them
if (!$funnel->welcome_sequence_id) {
    // Try to find sequence with "Welcome" in name
    $welcomeSeq = DB::table('email_sequences')->where('name', 'LIKE', '%Welcome%')->first();
    if ($welcomeSeq) {
        DB::table('funnels')->where('id', 2)->update(['welcome_sequence_id' => $welcomeSeq->id]);
        echo "<p>3. Set funnel welcome_sequence_id to: {$welcomeSeq->id}</p>";
    }
}

if (!$funnel->followup_sequence_id) {
    $followupSeq = DB::table('email_sequences')->where('name', 'LIKE', '%Follow%')->first();
    if ($followupSeq) {
        DB::table('funnels')->where('id', 2)->update(['followup_sequence_id' => $followupSeq->id]);
        echo "<p>4. Set funnel followup_sequence_id to: {$followupSeq->id}</p>";
    }
}

// Verify
$funnel2 = DB::table('funnels')->where('id', 2)->first();
echo "<p><strong>Final funnel config:</strong></p>";
echo "<ul>";
echo "<li>welcome_sequence_id: " . ($funnel2->welcome_sequence_id ?? 'NULL') . "</li>";
echo "<li>followup_sequence_id: " . ($funnel2->followup_sequence_id ?? 'NULL') . "</li>";
echo "<li>sequence_id: " . (DB::table('landing_pages')->where('id', 18)->first()->sequence_id ?? 'NULL') . "</li>";
echo "</ul>";