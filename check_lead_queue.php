<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Lead → EmailQueue Check</h2>";

// Get leads with sequence_id
$sql = "SELECT l.id, l.email, l.sequence_id, l.enrolled_at, 
              (SELECT COUNT(*) FROM email_queue eq WHERE eq.lead_id = l.id) as queue_count
       FROM leads l 
       WHERE l.sequence_id IS NOT NULL
       ORDER BY l.id DESC LIMIT 10";
$result = $conn->query($sql);

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Email</th><th>Seq ID</th><th>Enrolled</th><th>Queue Entries</th></tr>";
while ($r = $result->fetch_assoc()) {
    echo "<tr><td>{$r['id']}</td><td>{$r['email']}</td><td>{$r['sequence_id']}</td><td>{$r['enrolled_at']}</td><td>{$r['queue_count']}</td></tr>";
}
echo "</table>";

echo "<br><h3>EmailQueue Table Structure</h3>";
$result = $conn->query("DESCRIBE email_queue");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th></tr>";
while ($r = $result->fetch_assoc()) {
    echo "<tr><td>{$r['Field']}</td><td>{$r['Type']}</td></tr>";
}
echo "</table>";

echo "<br><h3>Current EmailQueue Data</h3>";
$cnt = $conn->query("SELECT COUNT(*) cnt FROM email_queue")->fetch_assoc();
echo "Total: {$cnt['cnt']}<br>";

$conn->close();