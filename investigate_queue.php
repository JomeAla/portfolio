<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Email Queue Investigation</h2>";

// 1. Check email_queue table
echo "<h3>1. Email Queue Table</h3>";
$r = $conn->query("SHOW TABLES LIKE 'email_queue'");
if ($r && $r->num_rows > 0) {
    echo "Table EXISTS<br>";
    $c = $conn->query("SELECT COUNT(*) cnt FROM email_queue")->fetch_assoc();
    echo "Total queued: {$c['cnt']}<br>";
    
    // Check pending emails
    $p = $conn->query("SELECT COUNT(*) cnt FROM email_queue WHERE status='pending'")->fetch_assoc();
    echo "Pending: {$p['cnt']}<br>";
    
    // Show recent queue
    $q = $conn->query("SELECT id, lead_email, subject, status, scheduled_at FROM email_queue ORDER BY id DESC LIMIT 10");
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Email</th><th>Subject</th><th>Status</th><th>Scheduled</th></tr>";
    while ($row = $q->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['lead_email']}</td><td>{$row['subject']}</td><td>{$row['status']}</td><td>{$row['scheduled_at']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "Table NOT FOUND<br>";
}

echo "<br><h3>2. Leads with Sequences</h3>";
$sql = "SELECT id, email, name, sequence_id, enrolled_at FROM leads WHERE sequence_id IS NOT NULL ORDER BY id DESC LIMIT 10";
$result = $conn->query($sql);
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Sequence ID</th><th>Enrolled At</th></tr>";
while ($r = $result->fetch_assoc()) {
    echo "<tr><td>{$r['id']}</td><td>{$r['email']}</td><td>{$r['name']}</td><td>{$r['sequence_id']}</td><td>{$r['enrolled_at']}</td></tr>";
}
echo "</table>";

echo "<br><h3>3. Sequence Steps for Sequence 22 (free-email-checklist)</h3>";
$sql = "SELECT id, subject, step_order, delay_hours FROM sequence_steps WHERE sequence_id = 22 ORDER BY step_order";
$result = $conn->query($sql);
echo "<table border='1'>";
echo "<tr><th>Step</th><th>Subject</th><th>Delay (hours)</th></tr>";
while ($r = $result->fetch_assoc()) {
    echo "<tr><td>{$r['step_order']}</td><td>{$r['subject']}</td><td>{$r['delay_hours']}</td></tr>";
}
echo "</table>";

echo "<br><h3>4. Test: Check Queue Manual</h3>";
$c = $conn->query("SELECT COUNT(*) cnt FROM email_queue")->fetch_assoc();
echo "Current queue count: {$c['cnt']}";

$conn->close();