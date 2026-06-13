<?php
error_reporting(0);

echo "<h1>Creating twitter_settings table...</h1>";

$sql = "CREATE TABLE IF NOT EXISTS twitter_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    access_token TEXT DEFAULT NULL,
    refresh_token TEXT DEFAULT NULL,
    token_type VARCHAR(50) DEFAULT NULL,
    expires_at INT DEFAULT NULL,
    client_id VARCHAR(255) DEFAULT NULL,
    client_secret VARCHAR(255) DEFAULT NULL,
    oauth_token VARCHAR(255) DEFAULT NULL,
    oauth_token_secret VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

$configFile = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configFile)) {
    $config = include($configFile);
    if (isset($config['connections']['mysql'])) {
        $c = $config['connections']['mysql'];
        $conn = new mysqli($c['host'], $c['username'], $c['password'], $c['database']);
        if (!$conn->connect_error) {
            if ($conn->query($sql)) {
                echo "<p style='color:green'>✓ Created twitter_settings table</p>";
            } else {
                echo "<p style='color:red'>Error: " . $conn->error . "</p>";
            }
        }
    }
} else {
    echo "<p>Config cache not found. Need to create table manually in PHPMyAdmin.</p>";
}