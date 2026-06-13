<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "<h1>Tables in Database</h1><ul>";
foreach (Schema::getConnection()->getDoctrineSchemaManager()->listTableNames() as $table) {
    echo "<li>$table</li>";
}
echo "</ul>";