<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: text/plain');

$key = $_GET['key'] ?? '';

if (empty($key)) {
    echo "ERROR: Missing access key\n";
    echo "Usage: /cron_process_emails.php?key=YOUR_SECRET_KEY\n";
    exit;
}

$expectedKey = 'joala2026cron';

if ($key !== $expectedKey) {
    echo "ERROR: Invalid access key\n";
    exit;
}

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EmailQueue;
use App\Models\Lead;

echo "=== Processing Email Queue ===\n\n";

$pendingEmails = EmailQueue::where('status', 'pending')
    ->where('scheduled_at', '<=', now())
    ->orderBy('scheduled_at')
    ->limit(20)
    ->get();

if ($pendingEmails->isEmpty()) {
    echo "No pending emails to process.\n";
    exit;
}

echo "Found {$pendingEmails->count()} email(s)\n\n";

$smtp = [
    'host' => 'mail.joala.com.ng',
    'port' => 465,
    'username' => 'support@joala.com.ng',
    'password' => 'SkAJW8JMlM*xLn&A',
    'from_email' => 'support@joala.com.ng',
    'from_name' => 'JoAla Ventures'
];

foreach ($pendingEmails as $email) {
    $lead = Lead::find($email->lead_id);
    if (!$lead) {
        $email->update(['status' => 'failed', 'error_message' => 'Lead not found']);
        echo "❌ Email #{$email->id}: Lead not found\n";
        continue;
    }
    
    $to = $lead->email;
    $name = $lead->name ?? 'Customer';
    $subject = $email->subject;
    $body = str_replace('{{name}}', $name, $email->body);
    
    $headers = "From: {$smtp['from_name']} <{$smtp['from_email']}>\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    echo "Sending: {$to} - {$subject}\n";
    
    $result = mail($to, $subject, $body, $headers);
    
    if ($result) {
        $email->update(['status' => 'sent', 'sent_at' => now()]);
        echo "✅ Sent!\n\n";
    } else {
        $error = error_get_last()['message'] ?? 'Unknown';
        $email->update(['status' => 'failed', 'error_message' => $error]);
        echo "❌ Failed: {$error}\n\n";
    }
}

echo "=== Complete ===\n";
