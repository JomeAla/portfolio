<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "Creating steps for Seq 21...<br>";

// Seq 21 steps
$steps21 = [
    [21, "Your download is ready!", 0, 1],
    [21, "Quick start guide", 2, 2],
    [21, "Need help?", 5, 3],
];

foreach($steps21 as $s) {
    $conn->query("INSERT INTO sequence_steps (sequence_id, subject, delay_days, step_order) VALUES ($s[0], '$s[1]', $s[2], $s[3])");
}
echo "Done Seq 21<br>";

echo "Creating steps for Seq 22...<br>";
// Seq 22 steps  
$steps22 = [
    [22, "Here's your checklist!", 0, 1],
    [22, "Quick question", 3, 2],
    [22, "Want more templates?", 7, 3],
];

foreach($steps22 as $s) {
    $conn->query("INSERT INTO sequence_steps (sequence_id, subject, delay_days, step_order) VALUES ($s[0], '$s[1]', $s[2], $s[3])");
}
echo "Done Seq 22<br>";

// Check
echo "Verifying...<br>";
print_r($conn->query("SELECT COUNT(*) FROM sequence_steps WHERE sequence_id=21")->fetch_row());
print_r($conn->query("SELECT COUNT(*) FROM sequence_steps WHERE sequence_id=22")->fetch_row());

$conn->close();