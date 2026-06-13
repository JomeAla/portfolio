<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

echo "<h1>Email Queue Table</h1>";
$result = $conn->query("DESCRIBE email_queue");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table>";

$result = $conn->query("SELECT * FROM email_queue LIMIT 3");
if ($result && $result->num_rows > 0) {
    echo "<h2>Sample Data</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>" . print_r($row, true) . "</pre>";
    }
}
