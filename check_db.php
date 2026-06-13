<?php
/**
 * Check database schema
 * Upload to public_html and access via browser
 * URL: https://joala.com.ng/check_db.php
 */

$host = 'localhost';
$db   = 'joala_portfolio';
$user = 'joala_com';

$configFile = __DIR__ . '/.env';
if (file_exists($configFile)) {
    $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'DB_PASSWORD') !== false) {
            $pass = trim(explode('=', $line)[1]);
        }
    }
} else {
    $pass = ''; // Try empty
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Marketing Tables Schema</h2><pre style='font-size:12px'>";
    
    $tables = ['blog_posts', 'landing_pages', 'leads', 'email_sequences', 'sequence_steps', 'email_queue', 'tweet_queue', 'email_opens', 'twitter_settings'];
    
    foreach ($tables as $table) {
        echo "\n=== $table ===\n";
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} ({$row['Type']}) - Default: {$row['Default']}\n";
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";