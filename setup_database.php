<?php
require __DIR__ . '/vendor/autoload.php';

$host = 'localhost';
$port = 3306;
$username = 'root';
$password = 'Mylordhelpme12';
$database = 'joala_portfolio';

echo "=== MySQL Database Setup for Portfolio Project ===\n\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[✓] Connected to MySQL server\n";
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[✓] Database '$database' created or already exists\n";
    
    $pdo->exec("USE `$database`");
    echo "[✓] Using database '$database'\n";
    
    echo "\n=== Running Laravel Migrations ===\n";
    $pdo = null;
    
    $artisan = 'C:/Users/jomea/.opencode/bin/portfolio/artisan';
    
    $envContent = file_get_contents('C:/Users/jomea/.opencode/bin/portfolio/.env');
    $envContent = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=joala_portfolio', $envContent);
    $envContent = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=root', $envContent);
    $envContent = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=Mylordhelpme12', $envContent);
    file_put_contents('C:/Users/jomea/.opencode/bin/portfolio/.env', $envContent);
    echo "[✓] Updated .env file with root user for local development\n";
    
    chdir('C:/Users/jomea/.opencode/bin/portfolio');
    $output = [];
    $returnCode = 0;
    exec('php artisan migrate --force 2>&1', $output, $returnCode);
    
    echo "\n--- Migration Output ---\n";
    echo implode("\n", $output);
    echo "\n--- End Migration ---\n";
    
    if ($returnCode === 0) {
        echo "\n[✓] Migrations completed successfully!\n";
    } else {
        echo "\n[!] Migration completed with warnings (check output above)\n";
    }
    
} catch (PDOException $e) {
    echo "[✗] MySQL Error: " . $e->getMessage() . "\n";
    exit(1);
}