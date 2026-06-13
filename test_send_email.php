<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Email Send</h1>";
echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;font-family:monospace;'>";

$password = 'J0ala@2024!';
$conn = @new mysqli('localhost', 'joalacom_joala', $password, 'joalacom_joala');
if ($conn->connect_error) {
    echo "DB Error: " . $conn->connect_error . "\n";
    exit;
}

echo "=== Test Email Sending ===\n\n";

// Get pending email
$result = $conn->query("SELECT q.*, l.name, l.email as lead_email FROM email_queue q JOIN leads l ON q.lead_id = l.id WHERE q.status = 'pending' ORDER BY q.scheduled_at LIMIT 1");

if (!$result || $result->num_rows == 0) {
    echo "No pending emails to send\n";
    exit;
}

$email = $result->fetch_assoc();
echo "Sending email to: {$email['lead_email']}\n";
echo "Subject: {$email['subject']}\n\n";

// Get SMTP config from settings (or fallback to .env)
$smtp = [
    'host' => 'mail.joala.com.ng',
    'port' => 465,
    'username' => 'support@joala.com.ng',
    'password' => 'SkAJW8JMlM*xLn&A',
    'encryption' => 'ssl',
    'from_email' => 'support@joala.com.ng',
    'from_name' => 'JoAla Ventures'
];

echo "SMTP Config:\n";
echo "  Host: {$smtp['host']}\n";
echo "  Port: {$smtp['port']}\n";
echo "  Username: {$smtp['username']}\n";
echo "  Encryption: {$smtp['encryption']}\n\n";

// Try to send email using PHP mail()
$to = $email['lead_email'];
$subject = $email['subject'];
$body = str_replace('{{name}}', $email['name'], $email['body']);
$body = nl2br($body);

$headers = "From: {$smtp['from_name']} <{$smtp['from_email']}>\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

echo "Attempting to send...\n";

$result = mail($to, $subject, $body, $headers);

if ($result) {
    echo "✅ Email sent successfully!\n";
    
    // Update queue status
    $conn->query("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = {$email['id']}");
    echo "Updated queue status to 'sent'\n";
} else {
    echo "❌ Failed to send email\n";
    echo "Error: " . error_get_last()['message'] . "\n";
}

echo "\nDone.";
