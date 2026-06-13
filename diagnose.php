<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Email System Diagnostic</h1>";

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    echo "<p style='color:red'>DB Error: " . $conn->connect_error . "</p>";
    exit;
}
echo "<p style='color:green'>✓ Database Connected</p>";

echo "<h2>1. Email Sequences</h2>";
$result = $conn->query("SELECT id, name, is_active FROM email_sequences");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['is_active'] ? '<span style="color:green">Active</span>' : '<span style="color:gray">Inactive</span>';
        echo "<p>ID {$row['id']}: <strong>{$row['name']}</strong> ($status)</p>";
    }
} else {
    echo "<p style='color:orange'>No sequences found</p>";
}

echo "<h2>2. Sequence Steps</h2>";
$result = $conn->query("SELECT s.id, s.subject, s.delay_days, s.step_number, e.name as sequence_name 
    FROM sequence_steps s 
    JOIN email_sequences e ON s.sequence_id = e.id 
    ORDER BY s.sequence_id, s.step_number");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse'><tr><th>Step</th><th>Subject</th><th>Delay</th><th>Sequence</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['step_number']}</td><td>{$row['subject']}</td><td>{$row['delay_days']} days</td><td>{$row['sequence_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>No steps found</p>";
}

echo "<h2>3. Email Queue</h2>";
$result = $conn->query("SELECT status, COUNT(*) as count FROM email_queue GROUP BY status");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p>{$row['status']}: <strong>{$row['count']}</strong> emails</p>";
    }
} else {
    echo "<p>No emails in queue</p>";
}

echo "<h2>4. Leads</h2>";
$result = $conn->query("SELECT id, name, email, status FROM leads LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse'><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['status']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>No leads found</p>";
}

echo "<h2>5. Settings</h2>";
$result = $conn->query("DESCRIBE settings");
if ($result) {
    echo "settings columns:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']}\n";
    }
}

$result = $conn->query("SELECT * FROM settings LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<p style='color:green'>✓ Settings found</p>";
} else {
    echo "<p style='color:orange'>⚠ No settings found</p>";
}

echo "<h2>Quick Actions:</h2>";
echo "<ul>";
echo "<li><a href='/admin/marketing/sequences'>Manage Sequences</a></li>";
echo "<li><a href='/admin/marketing/leads'>Manage Leads</a></li>";
echo "<li><a href='/admin/marketing/settings'>Email Settings</a></li>";
echo "</ul>";
