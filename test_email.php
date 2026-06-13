<h1>Email Sequence Test</h1>
<?php
error_reporting(0);

// Test email sending
$configFile = __DIR__ . '/bootstrap/cache/config.php';
if (!file_exists($configFile)) {
    echo "Config cache not found. Run: php artisan config:cache";
    exit;
}

$config = include($configFile);
$c = $config['connections']['mysql'];
$conn = new mysqli($c['host'], $c['username'], $c['password'], $c['database']);

if ($conn->connect_error) {
    die("Database error: " . $conn->connect_error);
}

echo "<h2>1. Checking Database Tables...</h2>";

// Check tables
$tables = ['email_sequences', 'sequence_steps', 'email_queue', 'leads', 'settings'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color:green'>✓ $table exists</p>";
    } else {
        echo "<p style='color:red'>✗ $table missing</p>";
    }
}

echo "<h2>2. Checking Sequences...</h2>";
$result = $conn->query("SELECT * FROM email_sequences WHERE is_active = 1");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<p>Sequence: {$row['name']} (ID: {$row['id']})</p>";
        
        // Get steps
        $steps = $conn->query("SELECT * FROM sequence_steps WHERE sequence_id = {$row['id']} ORDER BY step_order");
        if ($steps->num_rows > 0) {
            echo "<ul>";
            while ($step = $steps->fetch_assoc()) {
                echo "<li>Step {$step['step_order']}: {$step['subject']} (delay: {$step['delay_days']} days)</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:orange'>No steps in this sequence</p>";
        }
    }
} else {
    echo "<p style='color:orange'>No active sequences found. Create one first.</p>";
}

echo "<h2>3. Checking Pending Emails...</h2>";
$result = $conn->query("SELECT * FROM email_queue WHERE status = 'pending'");
if ($result->num_rows > 0) {
    echo "<p>Found {$result->num_rows} pending emails</p>";
} else {
    echo "<p>No pending emails</p>";
}

echo "<h2>4. Testing Email Configuration...</h2>";

// Check SMTP settings
$smtpResult = $conn->query("SELECT * FROM settings WHERE setting_key LIKE 'smtp%'");
if ($smtpResult->num_rows > 0) {
    echo "<p>SMTP settings found in database:</p><ul>";
    while ($row = $smtpResult->fetch_assoc()) {
        $value = $row['setting_key'] === 'smtp_password' ? '******' : $row['setting_value'];
        echo "<li>{$row['setting_key']}: $value</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:orange'>No SMTP settings found in database. Need to configure email settings first.</p>";
}

echo "<h2>Summary</h2>";
echo "<p>To test email sending, you need:</p>";
echo "<ol>";
echo "<li>SMTP settings configured (in Settings admin)</li>";
echo "<li>At least one active sequence with steps</li>";
echo "<li>Leads enrolled in the sequence</li>";
echo "</ol>";

echo "<p><a href='/admin/marketing/sequences'>Go to Sequences</a></p>";
echo "<p><a href='/admin/marketing/settings'>Go to Settings (Email config)</a></p>";