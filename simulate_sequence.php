<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Email Sequence Simulation</h1>";
echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;font-family:monospace;'>";

$password = 'J0ala@2024!';
$conn = @new mysqli('localhost', 'joalacom_joala', $password, 'joalacom_joala');
if ($conn->connect_error) {
    echo "DB Error: " . $conn->connect_error . "\n";
    exit;
}

// Check table structure first
echo "Checking table structure...\n";
$result = $conn->query("DESCRIBE sequence_steps");
echo "sequence_steps columns:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']}\n";
}

$result = $conn->query("DESCRIBE leads");
echo "leads columns:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']}\n";
}

$result = $conn->query("DESCRIBE email_queue");
echo "email_queue columns:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']}\n";
}

echo "=== Email Sequence Simulation ===\n\n";

// 1. Check/create sequence
echo "1. Checking sequences...\n";
$result = $conn->query("SELECT * FROM email_sequences WHERE is_active = 1");
if ($result && $result->num_rows > 0) {
    echo "   Found active sequence\n";
    $sequence = $result->fetch_assoc();
} else {
echo "   Creating test sequence...\n";
$conn->query("INSERT INTO email_sequences (name, is_active, created_at, updated_at) VALUES ('Welcome Series', 1, NOW(), NOW())");
$sequenceId = $conn->insert_id;
echo "   Created sequence ID: $sequenceId\n";

// Add steps (use step_number not step_order)
$conn->query("INSERT INTO sequence_steps (sequence_id, step_number, subject, body, delay_days, created_at, updated_at) VALUES ($sequenceId, 1, 'Welcome to Joala Ventures!', '<p>Hi {{name}},</p><p>Welcome! We\'re excited to have you.</p>', 0, NOW(), NOW())");
$conn->query("INSERT INTO sequence_steps (sequence_id, step_number, subject, body, delay_days, created_at, updated_at) VALUES ($sequenceId, 2, 'Following up - Any questions?', '<p>Hi {{name}},</p><p>Just checking in. Any questions?</p>', 1, NOW(), NOW())");
$conn->query("INSERT INTO sequence_steps (sequence_id, step_number, subject, body, delay_days, created_at, updated_at) VALUES ($sequenceId, 3, 'Special Offer Inside', '<p>Hi {{name}},</p><p>Here\'s a special offer just for you!</p>', 3, NOW(), NOW())");
    echo "   Added 3 steps\n";
    $sequence = ['id' => $sequenceId, 'name' => 'Welcome Series'];
}

$seqId = $sequence['id'];

// 2. Create test lead (match actual table structure)
echo "\n2. Creating test lead...\n";
$testEmail = 'test_' . time() . '@example.com';
$conn->query("INSERT INTO leads (name, email, sequence_id, status, created_at, updated_at) VALUES ('Test User', '$testEmail', $seqId, 'new', NOW(), NOW())");
$leadId = $conn->insert_id;
echo "   Created lead ID: $leadId ($testEmail)\n";

// 3. Link lead to sequence (already done via sequence_id in leads table)
echo "\n3. Lead is already linked to sequence via sequence_id\n";

// 4. Queue emails
echo "\n4. Queueing emails for each step...\n";
$steps = $conn->query("SELECT * FROM sequence_steps WHERE sequence_id = $seqId ORDER BY step_number");

if (!$steps || $steps->num_rows == 0) {
    echo "   No steps found - creating them...\n";
    // Create steps using correct column names
    $conn->query("INSERT INTO sequence_steps (sequence_id, step_number, subject, body, delay_days, created_at, updated_at) VALUES ($seqId, 1, 'Welcome to Joala Ventures!', '<p>Hi {{name}},</p><p>Welcome! We\'re excited to have you.</p>', 0, NOW(), NOW())");
    $conn->query("INSERT INTO sequence_steps (sequence_id, step_number, subject, body, delay_days, created_at, updated_at) VALUES ($seqId, 2, 'Following up - Any questions?', '<p>Hi {{name}},</p><p>Just checking in. Any questions?</p>', 1, NOW(), NOW())");
    $conn->query("INSERT INTO sequence_steps (sequence_id, step_number, subject, body, delay_days, created_at, updated_at) VALUES ($seqId, 3, 'Special Offer Inside', '<p>Hi {{name}},</p><p>Here\'s a special offer just for you!</p>', 3, NOW(), NOW())");
    $steps = $conn->query("SELECT * FROM sequence_steps WHERE sequence_id = $seqId ORDER BY step_number");
}

$now = date('Y-m-d H:i:s');

while ($step = $steps->fetch_assoc()) {
    $scheduled = date('Y-m-d H:i:s', strtotime("+{$step['delay_days']} days"));
    $subject = $conn->real_escape_string($step['subject']);
    $body = $conn->real_escape_string($step['body']);
    
    $conn->query("INSERT INTO email_queue (lead_id, sequence_step_id, subject, body, status, scheduled_at, created_at, updated_at) 
        VALUES ($leadId, {$step['id']}, '$subject', '$body', 'pending', '$scheduled', NOW(), NOW())");
    
    echo "   Step {$step['step_number']}: \"{$step['subject']}\" - scheduled $scheduled\n";
}

// 5. Show queue
echo "\n5. Email Queue Status:\n";
$result = $conn->query("SELECT status, COUNT(*) as cnt FROM email_queue WHERE lead_id = $leadId GROUP BY status");
while ($row = $result->fetch_assoc()) {
    echo "   {$row['status']}: {$row['cnt']} emails\n";
}

echo "\n=== Simulation Complete ===\n";
echo "\nTo process emails, ensure SMTP is configured.\n";
echo "Run: php artisan queue:work\n";
echo "\nTest lead email: $testEmail\n";
echo "Lead ID: $leadId\n";
echo "Sequence ID: $seqId\n";
echo "</pre>";
