<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $db = Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<h1>Connected! Creating tables...</h1>";

    $db->exec("
        CREATE TABLE IF NOT EXISTS leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) DEFAULT NULL,
            email VARCHAR(255) NOT NULL,
            landing_page_id INT DEFAULT NULL,
            sequence_id INT DEFAULT NULL,
            status VARCHAR(50) DEFAULT 'new',
            is_newsletter TINYINT(1) DEFAULT 0,
            confirmed TINYINT(1) DEFAULT 0,
            confirmation_token VARCHAR(255) DEFAULT NULL,
            confirmed_at DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT NULL,
            updated_at DATETIME DEFAULT NULL,
            UNIQUE KEY leads_email_unique (email)
        )
    ");
    echo "<p style='color:green'>✓ Created leads</p>";
    echo "<h2 style='color:green'>✓ Done!</h2>";

} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
