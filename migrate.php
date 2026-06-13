<?php
/**
 * Migration Runner - Upload to your server and access via browser
 * URL: https://joala.com.ng/migrate.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "Running migrations...\n";
$kernel->call('migrate', ['--force' => true]);
echo "\nDone!</pre>";