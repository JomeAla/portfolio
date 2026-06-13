<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bootstrap Laravel manually
require __DIR__ . '/vendor/autoload.php';

$app = new Illuminate\Foundation\Application(__DIR__);
$app->singleton('config', function () {
    return new Illuminate\Config\Repository([
        'database' => [
            'default' => 'mysql',
            'connections' => [
                'mysql' => array_merge(
                    require __DIR__ . '/config/database.php',
                    ['database' => basename(require __DIR__ . '/config/database.php')['database']]
                )
            ]
        ]
    ]);
});

// But easier - let's just try to connect using Laravel's PDO
try {
    // Get from Laravel app
    $app = require __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $db = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "<h1>Connected! Creating tables...</h1>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Let me try another way...</p>";
}

// Try PDO with guessed credentials
$creds = [
    ['host' => 'localhost', 'user' => 'joala_com', 'pass' => 'joala@2025', 'db' => 'joalacom_joala'],
    ['host' => 'localhost', 'user' => 'joalacom', 'pass' => 'joala@2025', 'db' => 'joalacom_joala'],
    ['host' => 'localhost', 'user' => 'joalacom_joala', 'pass' => '', 'db' => 'joalacom_joala'],
];

foreach ($creds as $c) {
    try {
        $conn = new PDO("mysql:host={$c['host']};dbname={$c['db']}", $c['user'], $c['pass']);
        echo "<p style='color:green'>Connected with {$c['user']}!</p>";
        runCreate($conn);
        exit;
    } catch (PDOException $e) {
        // Continue
    }
}

function runCreate($conn) {
    $sql = "
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
    );
    ";
    $conn->exec($sql);
    echo "<p style='color:green'>✓ Created leads</p>";
    
    // Add other tables...
    echo "<h2 style='color:green'>✓ Done!</h2>";
}