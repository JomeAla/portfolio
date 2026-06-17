<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class);
try {
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    $check = $pdo->query("SHOW COLUMNS FROM lesson_progress LIKE 'customer_email'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE lesson_progress ADD COLUMN customer_email VARCHAR(255) NOT NULL AFTER `id`");
        $pdo->exec("ALTER TABLE lesson_progress ADD INDEX idx_customer_email (customer_email)");
        echo "<h2 style='color:green;'>Fixed! Added customer_email column to lesson_progress table.</h2>";
        echo "<p><a href='/customer/login'>Try logging in again</a></p>";
    } else {
        echo "<h2 style='color:blue;'>customer_email column already exists. No fix needed.</h2>";
    }
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Error: " . $e->getMessage() . "</h2>";
}
