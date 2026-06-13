<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Adding A/B testing column to funnel_leads table...\n\n";

// ab_variant: which A/B variant this lead was assigned to
try {
    DB::statement("ALTER TABLE funnel_leads ADD COLUMN ab_variant VARCHAR(10) NULL");
    echo "SUCCESS: Added ab_variant column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

// wait_until: timestamp to wait until before advancing
try {
    DB::statement("ALTER TABLE funnel_leads ADD COLUMN wait_until DATETIME NULL");
    echo "SUCCESS: Added wait_until column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

echo "\nDone!";