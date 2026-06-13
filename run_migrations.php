<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "<h1>Running Migrations...</h1>";

try {
    \Illuminate\Support\Facades\Schema::dropAllTables();
    
    $migrator = $app->make(Illuminate\Database\Migrations\Migrator::class);
    $migrator->run(__DIR__ . '/database/migrations');
    
    echo "<p style='color: green;'>✓ Migrations completed successfully!</p>";
    echo "<pre>";
    print_r($migrator->getNotes());
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
