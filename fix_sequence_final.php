<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>Fix Landing Page Sequence - Final</h2>";

// 1. Check sequences table
$seqCount = DB::table('sequences')->count();
echo "<p>sequences table has {$seqCount} records</p>";

// 2. Check email_sequences for Welcome sequence
$emailSeq = DB::table('email_sequences')
    ->where('name', 'LIKE', '%Welcome%')
    ->where('name', 'LIKE', '%WP%')
    ->first();

if ($emailSeq) {
    echo "<p>Found email_sequence: ID {$emailSeq->id} - {$emailSeq->name}</p>";
    
    // 3. Try to insert into sequences table
    try {
        $seqId = DB::table('sequences')->insertGetId([
            'name' => 'Welcome - WP Starter Kit',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "<p>Created sequence with ID: {$seqId}</p>";
        
        // 4. Update landing page
        DB::table('landing_pages')
            ->where('id', 18)
            ->update(['sequence_id' => $seqId]);
            
        echo "<p>Updated landing page sequence_id to {$seqId}</p>";
        
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
        
        // If insert fails, try to get existing sequence
        $existingSeq = DB::table('sequences')
            ->where('name', 'LIKE', '%Welcome%')
            ->first();
            
        if ($existingSeq) {
            DB::table('landing_pages')
                ->where('id', 18)
                ->update(['sequence_id' => $existingSeq->id]);
            echo "<p>Updated to existing sequence: {$existingSeq->id}</p>";
        }
    }
} else {
    echo "<p>No email sequence found, checking all...</p>";
    $allSeqs = DB::table('email_sequences')->get();
    foreach ($allSeqs as $s) {
        echo "<p>- ID {$s->id}: {$s->name}</p>";
    }
}

// 5. Verify final state
$lp = DB::table('landing_pages')->where('id', 18)->first();
echo "<p><strong>Final landing page sequence_id: " . ($lp->sequence_id ?? 'NULL') . "</strong></p>";

// 6. Also check funnel for welcome_sequence_id
$funnel = DB::table('funnels')->where('id', 2)->first();
echo "<p>Funnel welcome_sequence_id: " . ($funnel->welcome_sequence_id ?? 'NULL') . "</p>";

if (empty($funnel->welcome_sequence_id) && $emailSeq) {
    DB::table('funnels')->where('id', 2)->update(['welcome_sequence_id' => $emailSeq->id]);
    echo "<p>Updated funnel welcome_sequence_id to {$emailSeq->id}</p>";
}