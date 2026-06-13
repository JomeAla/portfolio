<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Funnel Stages Condition Fields</h1>";

$columns = DB::getSchemaBuilder()->getColumnListing('funnel_stages');
$conditionCols = array_filter($columns, function($c) {
    return strpos($c, 'condition') !== false || strpos($c, 'action') !== false || strpos($c, 'wait') !== false;
});

echo "<p>Condition-related columns: " . implode(', ', $conditionCols) . "</p>";

echo "<h2>Sample stage with conditions</h2>";
$stages = DB::table('funnel_stages')->where('funnel_id', 26)->get();
foreach ($stages as $s) {
    echo "<h3>{$s->name}</h3>";
    echo "<ul>";
    echo "<li>condition_type: " . ($s->condition_type ?? 'NULL') . "</li>";
    echo "<li>condition_value: " . ($s->condition_value ?? 'NULL') . "</li>";
    echo "<li>action_on_complete: " . ($s->action_on_complete ?? 'NULL') . "</li>";
    echo "<li>action_config: " . ($s->action_config ?? 'NULL') . "</li>";
    echo "<li>wait_until_type: " . ($s->wait_until_type ?? 'NULL') . "</li>";
    echo "<li>wait_until_value: " . ($s->wait_until_value ?? 'NULL') . "</li>";
    echo "</ul>";
}