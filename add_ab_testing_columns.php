<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Adding A/B testing columns to funnels table...\n\n";

// ab_testing_enabled: whether A/B testing is enabled
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN ab_testing_enabled TINYINT DEFAULT 0");
    echo "SUCCESS: Added ab_testing_enabled column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// ab_variants: JSON - array of variant configurations
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN ab_variants JSON NULL");
    echo "SUCCESS: Added ab_variants column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// ab_traffic_split: JSON - how traffic is distributed (e.g., {"a": 50, "b": 50})
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN ab_traffic_split JSON NULL");
    echo "SUCCESS: Added ab_traffic_split column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// ab_winner: which variant won (a, b, etc.)
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN ab_winner VARCHAR(10) NULL");
    echo "SUCCESS: Added ab_winner column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// ab_started_at: when A/B test started
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN ab_started_at DATETIME NULL");
    echo "SUCCESS: Added ab_started_at column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// ab_min_sample_size: minimum visitors before declaring winner
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN ab_min_sample_size INT DEFAULT 100");
    echo "SUCCESS: Added ab_min_sample_size column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// ab_confidence_level: confidence threshold (e.g., 95 for 95%)
try {
    DB::statement("ALTER TABLE funnels ADD COLUMN ab_confidence_level INT DEFAULT 95");
    echo "SUCCESS: Added ab_confidence_level column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

echo "\nDone! All A/B testing columns added.\n";

// Verify columns exist
$columns = DB::select("SHOW COLUMNS FROM funnels WHERE Field LIKE 'ab%'");
echo "\nA/B Testing columns in funnels:\n";
foreach ($columns as $col) {
    echo "  - " . $col->Field . "\n";
}