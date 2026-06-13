<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Adding missing FunnelStage columns...\n\n";

// wait_until_type: specific datetime, day_of_week, etc.
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN wait_until_type VARCHAR(50) NULL");
    echo "SUCCESS: Added wait_until_type column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// wait_until_value: JSON for the specific value
try {
    DB::statement("ALTER TABLE funnel_stages ADD COLUMN wait_until_value JSON NULL");
    echo "SUCCESS: Added wait_until_value column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

echo "\nDone!";