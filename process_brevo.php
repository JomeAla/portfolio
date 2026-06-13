<?php
/**
 * Process Email Queue using Brevo API
 * Run via cron: curl -s http://joala.com.ng/process_brevo.php
 */

$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

// Get Brevo API key
$res = $conn->query("SELECT value FROM settings WHERE `key` = 'brevo_api_key'");
if ($r = $res->fetch_assoc()) {
    $apiKey = $r['value'];
} else {
    // Try mail_password as API key
    $res = $conn->query("SELECT value FROM settings WHERE `key` = 'mail_password'");
    $apiKey = $res->fetch_assoc()['value'] ?? '';
}

echo "Processing emails via Brevo API...<br>";

// Get pending emails
$sql = "SELECT eq.id, eq.lead_id, eq.sequence_step_id, l.email, l.name, ss.subject, ss.body
        FROM email_queue eq
        JOIN leads l ON eq.lead_id = l.id
        JOIN sequence_steps ss ON eq.sequence_step_id = ss.id
        WHERE eq.status = 'pending'
        AND eq.scheduled_send_time <= NOW()
        LIMIT 5";
        
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    echo "No emails to send";
    $conn->close();
    exit;
}

echo "Found {$result->num_rows} emails to send<br><br>";

while ($email = $result->fetch_assoc()) {
    $to = $email['email'];
    $name = $email['name'] ?? 'Customer';
    $subject = $email['subject'];
    $body = $email['body'];
    
    // Convert to HTML
    $htmlBody = nl2br($body);
    $htmlBody = "<html><body><div style='font-family:Arial,sans-serif;max-width:600px;'>$htmlBody</div></body></html>";
    
    // Send via Brevo API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'sender' => ['email' => 'campaigns@joala.com.ng', 'name' => 'JoAla'],
        'to' => [['email' => $to, 'name' => $name]],
        'subject' => $subject,
        'htmlContent' => $htmlBody
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: $apiKey",
        "content-type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $resultData = json_decode($response, true);
    
    if ($httpCode === 201 || $httpCode === 200) {
        // Mark as sent
        $conn->query("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = {$email['id']}");
        echo "✓ Sent to $to: $subject<br>";
    } else {
        $error = $resultData['message'] ?? 'Unknown error';
        $conn->query("UPDATE email_queue SET status = 'failed', error_message = '$error' WHERE id = {$email['id']}");
        echo "✗ Failed to $to: $error<br>";
    }
}

echo "<br>Done!";

$conn->close();