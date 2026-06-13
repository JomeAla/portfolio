<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking and fixing email_sequences table...\n";

try {
    DB::statement("ALTER TABLE email_sequences ADD COLUMN trigger_type VARCHAR(50) NULL");
    echo "SUCCESS: Added trigger_type column\n";
} catch (Exception $e) {
    echo "Column may already exist: " . $e->getMessage() . "\n";
}

echo "\nDone!";