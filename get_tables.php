<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try Laravel cached config
$configFile = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configFile)) {
    $config = include($configFile);
    if (isset($config['connections']['mysql'])) {
        $c = $config['connections']['mysql'];
        $conn = @new mysqli($c['host'], $c['username'], $c['password'], $c['database']);
        if (!$conn->connect_error) {
            listTables($conn);
            exit;
        }
    }
}

// Try .env file directly
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'DB_') === 0) {
            echo "$line<br>";
        }
    }
}

function listTables($conn) {
    $result = $conn->query("SHOW TABLES");
    echo "<h1>Tables in Database</h1><ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
}