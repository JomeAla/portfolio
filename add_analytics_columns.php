<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

echo "<h1>Add Analytics Columns</h1>";

$columns = ['opened' => 'TINYINT(1) DEFAULT 0', 'clicked' => 'TINYINT(1) DEFAULT 0', 'opened_at' => 'DATETIME NULL', 'clicked_at' => 'DATETIME NULL'];

foreach ($columns as $col => $type) {
    $result = $conn->query("SHOW COLUMNS FROM email_queue LIKE '$col'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE email_queue ADD COLUMN $col $type");
        echo "✅ Added column: $col\n";
    } else {
        echo "✓ Column already exists: $col\n";
    }
}

echo "\nDone!";
