<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Create Test Pending Emails</h1>";
echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;font-family:monospace;'>";

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    echo "DB Error: " . $conn->connect_error . "\n";
    exit;
}

$testEmail = 'new_lead_' . time() . '@example.com';
$conn->query("INSERT INTO leads (name, email, sequence_id, status, created_at, updated_at) VALUES ('New Lead', '$testEmail', 1, 'new', NOW(), NOW())");
$leadId = $conn->insert_id;
echo "Created lead ID: $leadId ($testEmail)\n";

$now = date('Y-m-d H:i:s');

$conn->query("INSERT INTO email_queue (lead_id, sequence_step_id, subject, body, status, scheduled_at, created_at, updated_at) VALUES ($leadId, 1, 'Welcome Email', '<p>Hi {{name}},</p><p>Welcome!</p>', 'pending', '$now', NOW(), NOW())");
echo "Created pending email for Step 1\n";

$later = date('Y-m-d H:i:s', strtotime('+1 day'));
$conn->query("INSERT INTO email_queue (lead_id, sequence_step_id, subject, body, status, scheduled_at, created_at, updated_at) VALUES ($leadId, 2, 'Follow Up', '<p>Hi {{name}},</p><p>Following up!</p>', 'pending', '$later', NOW(), NOW())");
echo "Created pending email for Step 2 (scheduled for tomorrow)\n";

echo "\n=== Test emails created ===\n";
echo "To process: visit https://joala.com.ng/process_emails.php\n";
echo "</pre>";
