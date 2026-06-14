<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running funnel migration via Artisan...\n\n";

try {
    $exitCode = Artisan::call('funnel:migrate');
    echo Artisan::output();
    echo "\nExit code: $exitCode\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
