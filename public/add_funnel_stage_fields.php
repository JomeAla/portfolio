<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "<h2>Adding Advanced Fields to Funnel Stages</h2>";

$columns = DB::getSchemaBuilder()->getColumnListing('funnel_stages');
echo "<p>Current columns: " . implode(', ', $columns) . "</p>";

// Check each column and add if missing
$fields = [
    'sequence_id' => 'BIGINT UNSIGNED NULL',
    'email_template' => 'TEXT NULL',
    'delay_hours' => 'INT DEFAULT 0',
    'condition_type' => 'VARCHAR(50) NULL',
    'condition_value' => 'JSON NULL',
    'is_skippable' => 'TINYINT(1) DEFAULT 0',
    'action_on_complete' => 'VARCHAR(50) DEFAULT "advance"',
    'action_config' => 'JSON NULL',
    'points_to_award' => 'INT DEFAULT 0',
    'wait_duration_hours' => 'INT DEFAULT 0',
    'wait_until_type' => 'VARCHAR(50) NULL',
    'wait_until_value' => 'JSON NULL',
    'redirect_type' => 'VARCHAR(50) NULL',
    'conditional_stages' => 'JSON NULL',
];

$added = [];
$skipped = [];

foreach ($fields as $field => $definition) {
    if (in_array($field, $columns)) {
        $skipped[] = $field;
        continue;
    }
    
    try {
        DB::statement("ALTER TABLE funnel_stages ADD $field $definition");
        $added[] = $field;
        echo "<p style='color:green'>Added: $field</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error adding $field: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Summary</h3>";
echo "<p>Added: " . implode(', ', $added) . "</p>";
echo "<p>Skipped (already exist): " . implode(', ', $skipped) . "</p>";

$newColumns = DB::getSchemaBuilder()->getColumnListing('funnel_stages');
echo "<p>New columns: " . implode(', ', $newColumns) . "</p>";