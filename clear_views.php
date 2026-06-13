<?php
error_reporting(0);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$viewsDir = storage_path('framework/views');
$files = glob($viewsDir . '/*.php');

foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
        echo "Deleted: " . basename($file) . "<br>";
    }
}

echo "<h3>View cache cleared!</h3>";
