<?php
error_reporting(0);
$settings = [];
$lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($k, $v) = explode('=', $line, 2);
        $settings[trim($k)] = trim($v);
    }
}

$host = $settings['DB_HOST'] ?? 'localhost';
$db = $settings['DB_DATABASE'];
$user = $settings['DB_USERNAME'];
$pass = $settings['DB_PASSWORD'];

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Running New Migrations...</h1>";

$sqls = [
    // Add newsletter fields to leads
    "ALTER TABLE leads ADD COLUMN is_newsletter TINYINT(1) DEFAULT 0",
    "ALTER TABLE leads ADD COLUMN confirmed TINYINT(1) DEFAULT 0",
    "ALTER TABLE leads ADD COLUMN confirmation_token VARCHAR(255) NULL UNIQUE",
    "ALTER TABLE leads ADD COLUMN confirmed_at DATETIME NULL",
    
    // Add timer and popup to landing pages
    "ALTER TABLE landing_pages ADD COLUMN countdown_end DATETIME NULL",
    "ALTER TABLE landing_pages ADD COLUMN countdown_message VARCHAR(255) NULL",
    "ALTER TABLE landing_pages ADD COLUMN show_popup TINYINT(1) DEFAULT 0",
    "ALTER TABLE landing_pages ADD COLUMN popup_delay INT DEFAULT 5",
    "ALTER TABLE landing_pages ADD COLUMN popup_title VARCHAR(255) NULL",
    "ALTER TABLE landing_pages ADD COLUMN popup_html TEXT NULL",
    
    // Add popup to blog posts
    "ALTER TABLE blog_posts ADD COLUMN show_popup TINYINT(1) DEFAULT 0",
    "ALTER TABLE blog_posts ADD COLUMN popup_delay INT DEFAULT 10",
    "ALTER TABLE blog_posts ADD COLUMN popup_title VARCHAR(255) NULL",
    "ALTER TABLE blog_posts ADD COLUMN popup_html TEXT NULL",
];

foreach ($sqls as $sql) {
    $table = preg_match('/TABLE (\w+)/', $sql, $m) ? $m[1] : 'unknown';
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Added to $table</p>";
    } else {
        echo "<p style='color: orange;'>⚠ $table: " . $conn->error . "</p>";
    }
}

echo "<h2 style='color: green;'>✓ Migrations complete!</h2>";
echo "<p><a href='/'>Go to home page</a></p>";