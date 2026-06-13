<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "Running migrations...\n";

try {
    Artisan::call('migrate', ['--force' => true, '--path' => '/database/migrations/2026_04_19_000001_add_funnel_enhancements.php']);
    echo Artisan::output();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}