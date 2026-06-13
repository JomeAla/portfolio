<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $result = DB::select('SELECT 1 as test');
    echo "DB OK: " . json_encode($result);
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage();
}