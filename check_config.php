<?php
echo "<h1>Laravel Config</h1>";
echo "<pre>";

$dir = __DIR__;

// Try to find .env file
$envFile = $dir . '/.env';
if (file_exists($envFile)) {
    echo ".env file found\n";
    $lines = file($envFile);
    foreach ($lines as $line) {
        if (preg_match('/^(DB_|MAIL_)/', $line) && !preg_match('/^#/', $line)) {
            echo trim($line) . "\n";
        }
    }
} else {
    echo ".env not found\n";
}

echo "\n\nChecking bootstrap/config.php...\n";
$configFile = $dir . '/bootstrap/cache/config.php';
if (file_exists($configFile)) {
    $config = include($configFile);
    echo "MySQL Host: " . $config['connections']['mysql']['host'] . "\n";
    echo "MySQL Database: " . $config['connections']['mysql']['database'] . "\n";
    echo "MySQL Username: " . $config['connections']['mysql']['username'] . "\n";
    echo "MySQL Password: " . substr($config['connections']['mysql']['password'], 0, 4) . "****\n";
} else {
    echo "Config cache not found - run: php artisan config:cache\n";
}
