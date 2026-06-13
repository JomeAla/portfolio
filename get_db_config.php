<?php
echo "<h1>Get DB Config</h1>";
echo "<pre>";

$configFile = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configFile)) {
    $config = include($configFile);
    $c = $config['connections']['mysql'];
    echo "MySQL Config:\n";
    echo "Host: " . $c['host'] . "\n";
    echo "Database: " . $c['database'] . "\n";
    echo "Username: " . $c['username'] . "\n";
    echo "Password: " . $c['password'] . "\n";
} else {
    echo "No config cache found";
}
