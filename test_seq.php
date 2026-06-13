<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");
if ($conn->connect_error) {
    echo "DB Error: " . $conn->connect_error;
    exit;
}
echo "Connected";

// Create sequence for product 1
$conn->query("INSERT INTO email_sequences (name, description, is_active) VALUES ('Test Seq', 'Test', 1)");
echo " ID: " . $conn->insert_id;

$conn->close();