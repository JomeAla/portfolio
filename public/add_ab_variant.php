<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Check funnel_leads for A/B column</h1>";

$columns = DB::getSchemaBuilder()->getColumnListing('funnel_leads');
echo "<p>Columns: " . implode(', ', $columns) . "</p>";

if (!in_array('ab_variant', $columns)) {
    echo "<p>Adding ab_variant column...</p>";
    DB::statement('ALTER TABLE funnel_leads ADD COLUMN ab_variant VARCHAR(1) DEFAULT NULL AFTER wait_until');
    echo "<p>Added ab_variant column</p>";
} else {
    echo "<p>ab_variant column already exists</p>";
}