<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "Adding to sequence_steps table directly...<br>";

// Check table name/structure
$r = $conn->query("DESCRIBE sequence_steps");
$cols = [];
while($row = $r->fetch_assoc()) { $cols[] = $row['Field']; }
echo "Columns: " . implode(", ", $cols) . "<br>";

// The column might be sequence_id not seq_id (need to check)
$conn->query("INSERT INTO sequence_steps (sequence_id, subject, delay_days, step_order) VALUES (22, 'Checklist ready!', 0, 1)");
echo "Inserted step 1<br>";

$conn->query("INSERT INTO sequence_steps (sequence_id, subject, delay_days, step_order) VALUES (22, 'How is the checklist?', 3, 2)");
echo "Inserted step 2<br>";

$conn->query("INSERT INTO sequence_steps (sequence_id, subject, delay_days, step_order) VALUES (22, 'Want more templates?', 7, 3)");
echo "Inserted step 3<br>";

// Verify
$r = $conn->query("SELECT COUNT(*) cnt FROM sequence_steps WHERE sequence_id=22");
echo "Seq 22 steps: " . $r->fetch_assoc()['cnt'] . "<br>";

$conn->close();