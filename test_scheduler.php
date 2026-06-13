<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

// Test the scheduler
echo "Testing Laravel Scheduler...\n";
try {
    Artisan::call('schedule:run');
    echo "Scheduler ran successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test the email queue processor
echo "\nTesting Email Queue Processor...\n";
try {
    Artisan::call('email:process', ['--batch=5']);
    echo "Email processor ran successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nBoth commands are working. Make sure cron is set up:\n";
echo "* * * * * cd /home/joalacom/www && php artisan schedule:run >> /dev/null 2>&1\n";