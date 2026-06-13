<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Check A/B Fields</h1>";

$columns = DB::getSchemaBuilder()->getColumnListing('funnels');
$abColumns = array_filter($columns, function($c) {
    return strpos($c, 'ab') !== false;
});

echo "<p>A/B columns: " . implode(', ', $abColumns) . "</p>";

echo "<h2>Check funnel 26 A/B config</h2>";
$funnel = DB::table('funnels')->where('id', 26)->first();
echo "<ul>";
echo "<li>ab_testing_enabled: " . ($funnel->ab_testing_enabled ?? 'NULL') . "</li>";
echo "<li>ab_variants: " . ($funnel->ab_variants ?? 'NULL') . "</li>";
echo "<li>ab_traffic_split: " . ($funnel->ab_traffic_split ?? 'NULL') . "</li>";
echo "<li>ab_winner: " . ($funnel->ab_winner ?? 'NULL') . "</li>";
echo "</ul>";