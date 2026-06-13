<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>Check Tables</h2>";

// Check sequences table
echo "<h3>sequences table:</h3>";
$seqs = DB::table('sequences')->orderBy('id', 'DESC')->limit(10)->get();
foreach ($seqs as $s) {
    echo "<p>ID: {$s->id} - {$s->name}</p>";
}

// Check email_sequences table
echo "<h3>email_sequences table:</h3>";
$emailSeqs = DB::table('email_sequences')->orderBy('id', 'DESC')->limit(10)->get();
foreach ($emailSeqs as $es) {
    echo "<p>ID: {$es->id} - {$es->name}</p>";
}

// The relationship - check landing_pages table foreign key
echo "<h3>Landing page foreign key info:</h3>";
$cols = DB::getSchemaBuilder()->getColumnListing('landing_pages');
print_r($cols);