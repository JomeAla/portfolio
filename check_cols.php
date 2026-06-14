<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $conn = Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<h2>Connected via Laravel config!</h2>";

    $tables = ['leads', 'landing_pages', 'blog_posts'];
    foreach ($tables as $table) {
        echo "<h4>$table columns:</h4><ul>";
        $result = $conn->query("DESCRIBE $table");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>{$row['Field']} - {$row['Type']}</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
