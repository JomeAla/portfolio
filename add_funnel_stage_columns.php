<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Adding new columns to funnel_stages table...\n\n";

// condition_type: 'none' | 'email_opens' | 'clicks' | 'score_above' | 'tag_has'
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN condition_type VARCHAR(50) DEFAULT 'none'");
    echo "SUCCESS: Added condition_type column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// condition_value: JSON (the threshold or parameters)
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN condition_value JSON NULL");
    echo "SUCCESS: Added condition_value column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// action_on_complete: 'advance' | 'email' | 'tag' | 'wait'
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN action_on_complete VARCHAR(50) DEFAULT 'advance'");
    echo "SUCCESS: Added action_on_complete column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// action_config: JSON (action parameters)
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN action_config JSON NULL");
    echo "SUCCESS: Added action_config column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// points_to_award: points to award when stage is completed
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN points_to_award INT DEFAULT 0");
    echo "SUCCESS: Added points_to_award column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// wait_duration_hours: how many hours to wait if condition_type is 'wait'
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN wait_duration_hours INT DEFAULT 0");
    echo "SUCCESS: Added wait_duration_hours column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// redirect_type: 'url' | 'next_stage' | 'conditional'
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN redirect_type VARCHAR(50) DEFAULT 'url'");
    echo "SUCCESS: Added redirect_type column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// conditional_stages: JSON - map condition results to stage IDs
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN conditional_stages JSON NULL");
    echo "SUCCESS: Added conditional_stages column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

echo "\nDone! All columns added.\n";

// Verify columns exist
$columns = DB::select("SHOW COLUMNS FROM funnel_stages");
echo "\nCurrent columns in funnel_stages:\n";
foreach ($columns as $col) {
    echo "  - " . $col->Field . "\n";
}