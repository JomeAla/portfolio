<?php
/**
 * Debug script for segments
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Test segments table
    $tables = DB::select('SHOW TABLES');
    echo "Tables:\n";
    foreach ($tables as $t) {
        echo "- " . array_values((array)$t)[0] . "\n";
    }
    
    // Try to query segments
    $segments = DB::table('segments')->get();
    echo "\nSegments count: " . $segments->count() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}