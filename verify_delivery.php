<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Lead Magnet Sequences - Download Link Check</h2>";

// Check first step of each lead magnet sequence
$sql = "SELECT ss.id, ss.sequence_id, es.name as seq_name, ss.subject, ss.body
        FROM sequence_steps ss
        JOIN email_sequences es ON ss.sequence_id = es.id
        WHERE ss.step_order = 1 AND es.name LIKE 'Lead Magnet%'
        ORDER BY ss.sequence_id";
$result = $conn->query($sql);

echo "<table border='1'>";
echo "<tr><th>Seq</th><th>Sequence Name</th><th>Subject</th><th>Has Download Link</th></tr>";
while ($r = $result->fetch_assoc()) {
    $has = (stripos($r['body'], 'joala.com.ng') || stripos($r['body'], 'download')) ? '✓' : '✗';
    echo "<tr><td>{$r['sequence_id']}</td><td>{$r['seq_name']}</td><td>{$r['subject']}</td><td>$has</td></tr>";
}
echo "</table>";

echo "<h2>Post-Purchase Sequences - First Email</h2>";
$sql = "SELECT ss.id, ss.sequence_id, es.name as seq_name, ss.subject
        FROM sequence_steps ss
        JOIN email_sequences es ON ss.sequence_id = es.id
        WHERE ss.step_order = 1 AND es.name LIKE 'Post-Purchase%'
        ORDER BY ss.sequence_id";
$result = $conn->query($sql);

echo "<table border='1'>";
echo "<tr><th>Seq ID</th><th>Sequence</th><th>First Email</th></tr>";
while ($r = $result->fetch_assoc()) {
    echo "<tr><td>{$r['sequence_id']}</td><td>{$r['seq_name']}</td><td>{$r['subject']}</td></tr>";
}
echo "</table>";

$conn->close();