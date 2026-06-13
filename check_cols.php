<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try guessed credentials
$found = false;
$creds = [
    ['host' => 'localhost', 'user' => 'joala_com', 'pass' => 'joala@2025', 'db' => 'joalacom_joala'],
    ['host' => 'localhost', 'user' => 'joala_com', 'pass' => '4fu359TgAMi-O+', 'db' => 'joalacom_joala'],
    ['host' => 'localhost', 'user' => 'joalacom', 'pass' => 'joala@2025', 'db' => 'joalacom_joala'],
];

foreach ($creds as $c) {
    $conn = @new mysqli($c['host'], $c['user'], $c['pass'], $c['db']);
    if (!$conn->connect_error) {
        echo "<h3>Connected as {$c['user']}!</h3>";
        checkColumns($conn);
        $found = true;
        break;
    }
}

if (!$found) {
    echo "<p>Cannot connect. Please check tables manually in PHPMyAdmin.</p>";
}

function checkColumns($conn) {
    $tables = ['leads', 'landing_pages', 'blog_posts'];
    foreach ($tables as $table) {
        echo "<h4>$table columns:</h4><ul>";
        $result = $conn->query("DESCRIBE $table");
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['Field']} - {$row['Type']}</li>";
        }
        echo "</ul>";
    }
}