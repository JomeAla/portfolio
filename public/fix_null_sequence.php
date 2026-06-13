<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>Fix Sequence ID</h2>";

// Set sequence_id to NULL to avoid foreign key error
DB::statement('UPDATE landing_pages SET sequence_id = NULL WHERE id = 18');

echo "<p>Set sequence_id to NULL</p>";

// Verify
$lp = DB::table('landing_pages')->where('id', 18)->first();
echo "<p>Current sequence_id: " . ($lp->sequence_id ?? 'NULL') . "</p>";

// Check if there's a separate sequences table that should be used
echo "<h3>Check sequences table:</h3>";
$seqs = DB::table('sequences')->limit(10)->get();
foreach ($seqs as $s) {
    echo "<p>ID: {$s->id} - {$s->name}</p>";
}