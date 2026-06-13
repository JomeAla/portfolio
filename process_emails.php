<?php
/**
 * Auto-process email queue on page visit
 * Uses a time-lock file to prevent duplicate processing
 * Protected by API key - requires valid key in request
 * 
 * Usage: 
 *   curl -s https://www.joala.com.ng/process_emails.php?auto=1&key=YOUR_API_KEY
 *   curl -s https://www.joala.com.ng/process_emails.php?status=1&key=YOUR_API_KEY
 *   curl -s https://www.joala.com.ng/process_emails.php?init=1&admin=YOUR_ADMIN_TOKEN
 */

error_reporting(0);
ini_set('display_errors', 0);

// Configuration - change these for your setup
define('PROCESS_API_KEY', 'YOUR_API_KEY_HERE'); // Set via admin interface
define('ADMIN_TOKEN', 'admin-secure-token-change-me'); // Token for admin operations

$isAuto = isset($_GET['auto']);
$isStatus = isset($_GET['status']);
$isInit = isset($_GET['init']);
$isAdmin = isset($_GET['admin']) && $_GET['admin'] === ADMIN_TOKEN;

// If init is called with admin token, initialize/regenerate API key
if ($isAdmin && $isInit) {
    $host = 'localhost';
    $database = 'joalacom_joala';
    $username = 'joalacom_joala';
    $password = 'J0ala@2024!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $newApiKey = bin2hex(random_bytes(32));
        
        $check = $pdo->prepare("SELECT id FROM settings WHERE `key` = 'process_api_key'");
        $check->execute();
        
        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE settings SET `value` = ?, updated_at = NOW() WHERE `key` = 'process_api_key'");
            $stmt->execute([$newApiKey]);
            echo "API_KEY_UPDATED:" . $newApiKey;
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES ('process_api_key', ?, NOW(), NOW())");
            $stmt->execute([$newApiKey]);
            echo "API_KEY_CREATED:" . $newApiKey;
        }
    } catch (PDOException $e) {
        echo "ERROR:" . $e->getMessage();
    }
    exit;
}

// Status page - show queue status (no auth required for monitoring)
if ($isStatus) {
    // Try to get from database
    $host = 'localhost';
    $database = 'joalacom_joala';
    $username = 'joalacom_joala';
    $password = 'J0ala@2024!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
        
        $pending = $pdo->query("SELECT COUNT(*) FROM email_queue WHERE status = 'pending'")->fetchColumn();
        $sent = $pdo->query("SELECT COUNT(*) FROM email_queue WHERE status = 'sent'")->fetchColumn();
        $failed = $pdo->query("SELECT COUNT(*) FROM email_queue WHERE status = 'failed'")->fetchColumn();
        
        echo "Email Queue Status\n";
        echo "==================\n\n";
        echo "Pending: $pending\n";
        echo "Sent: $sent\n";
        echo "Failed: $failed\n";
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
    exit;
}

// Processing requires API key
$providedKey = $_GET['key'] ?? '';
$validKey = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=joalacom_joala;charset=utf8mb4", "joalacom_joala", "J0ala@2024!");
    $validKey = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'process_api_key'")->fetchColumn();
} catch (PDOException $e) {
    exit;
}

if (empty($validKey)) {
    exit;
}

if (empty($providedKey) || !hash_equals($validKey, $providedKey)) {
    exit;
}

// Lock file mechanism
$lockFile = __DIR__ . '/.email_process_lock';
$lockDuration = 280;

// Check if already processing
if (file_exists($lockFile)) {
    $lockedAt = (int)file_get_contents($lockFile);
    if (time() - $lockedAt < $lockDuration) {
        exit;
    }
}

// Get Brevo settings
$apiKey = '';
$fromEmail = 'campaigns@joala.com.ng';
$fromName = 'JoAla';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=joalacom_joala;charset=utf8mb4", "joalacom_joala", "J0ala@2024!");
    $apiKey = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'brevo_api_key'")->fetchColumn();
    $fromEmailDb = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'mail_from_address'")->fetchColumn();
    $fromNameDb = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'mail_from_name'")->fetchColumn();
    if ($fromEmailDb) $fromEmail = $fromEmailDb;
    if ($fromNameDb) $fromName = $fromNameDb;
} catch (PDOException $e) {}

if (empty($apiKey)) {
    exit;
}

// Get pending emails
try {
    $pdo = new PDO("mysql:host=localhost;dbname=joalacom_joala;charset=utf8mb4", "joalacom_joala", "J0ala@2024!");
    $stmt = $pdo->prepare("SELECT * FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 10");
    $stmt->execute();
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit;
}

if (count($emails) === 0) {
    exit;
}

file_put_contents($lockFile, time());

// Process emails
foreach ($emails as $email) {
    // Get lead info
    $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->execute([$email['lead_id']]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lead) {
        $pdo->prepare("UPDATE email_queue SET status = 'failed', error_message = 'Lead not found' WHERE id = ?")->execute([$email['id']]);
        continue;
    }
    
    // Get sequence step
    $stmt = $pdo->prepare("SELECT * FROM sequence_steps WHERE id = ?");
    $stmt->execute([$email['sequence_step_id']]);
    $step = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$step) {
        $pdo->prepare("UPDATE email_queue SET status = 'failed', error_message = 'Step not found' WHERE id = ?")->execute([$email['id']]);
        continue;
    }
    
    $to = $lead['email'];
    $name = $lead['name'] ?? 'Customer';
    $subject = $step['subject'];
    $body = str_replace('{{name}}', $name, $step['body'] ?? $step['content'] ?? '');
    $htmlContent = "<html><body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>" . nl2br($body) . "</body></html>";
    
    // Send via Brevo API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: $apiKey",
        "content-type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "sender" => ["name" => $fromName, "email" => $fromEmail],
        "to" => [["email" => $to, "name" => $name]],
        "subject" => $subject,
        "htmlContent" => $htmlContent
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201 || $httpCode === 200) {
        $pdo->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?")->execute([$email['id']]);
    } else {
        $pdo->prepare("UPDATE email_queue SET status = 'failed', error_message = 'HTTP $httpCode' WHERE id = ?")->execute([$email['id']]);
    }
}

@unlink($lockFile);
?>
