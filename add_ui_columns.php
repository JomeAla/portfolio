<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Adding UI improvement columns to funnels table...\n\n";

// is_template: mark funnel as a template
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN is_template TINYINT DEFAULT 0");
    echo "SUCCESS: Added is_template column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// template_category: category for template funnels
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN template_category VARCHAR(100) NULL");
    echo "SUCCESS: Added template_category column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// health_score: calculated health score (0-100)
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN health_score INT DEFAULT NULL");
    echo "SUCCESS: Added health_score column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// health_issues: JSON - list of issues found
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN health_issues JSON NULL");
    echo "SUCCESS: Added health_issues column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// last_health_check: when health was last calculated
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN last_health_check DATETIME NULL");
    echo "SUCCESS: Added last_health_check column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// stage_order: JSON - for drag-drop stage ordering
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN stage_order JSON NULL");
    echo "SUCCESS: Added stage_order column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

echo "\nDone! UI improvement columns added.\n";