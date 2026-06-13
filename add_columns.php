<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Adding trigger_type column to email_sequences...\n";

try {
    DB::statement("ALTER TABLE email_sequences ADD COLUMN trigger_type VARCHAR(50) NULL");
    echo "SUCCESS: Added trigger_type column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

try {
    DB::statement("ALTER TABLE sequence_steps ADD COLUMN delay_hours INT DEFAULT 0");
    echo "SUCCESS: Added delay_hours column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

try {
    DB::statement("ALTER TABLE sequence_steps ADD COLUMN step_number INT DEFAULT 0");
    echo "SUCCESS: Added step_number column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

echo "\nNow running setup script...\n";