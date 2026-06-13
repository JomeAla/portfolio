<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$result = $conn->query("SHOW COLUMNS FROM leads LIKE 'source'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE leads ADD COLUMN source VARCHAR(50) DEFAULT NULL AFTER email");
    echo "✅ Added 'source' column to leads table<br>";
} else {
    echo "✓ 'source' column already exists<br>";
}

$result = $conn->query("SHOW COLUMNS FROM leads LIKE 'enrolled_at'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE leads ADD COLUMN enrolled_at DATETIME DEFAULT NULL");
    echo "✅ Added 'enrolled_at' column to leads table<br>";
} else {
    echo "✓ 'enrolled_at' column already exists<br>";
}

echo "<h3>Done!</h3>";
