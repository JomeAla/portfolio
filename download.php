<?php
/**
 * Download Handler - serves lead magnet files after submission
 * URL: /download.php?file=email-marketing-checklist
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$file = $_GET['file'] ?? '';

// Map slugs to files
$files = [
    'email-marketing-checklist' => 'downloads/email-marketing-checklist.html',
];

if (!isset($files[$file])) {
    http_response_code(404);
    echo "File not found";
    exit;
}

$filepath = __DIR__ . '/' . $files[$file];

if (!file_exists($filepath)) {
    http_response_code(404);
    echo "File not found: $filepath";
    exit;
}

// Check if user submitted email
$email = $_GET['email'] ?? '';

if ($email) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get landing page
        $stmt = $pdo->prepare("SELECT id FROM landing_pages WHERE slug = 'free-email-checklist'");
        $stmt->execute();
        $lp = $stmt->fetch(PDO::FETCH_ASSOC);
        $lpId = $lp['id'] ?? null;
        
        // Create lead
        $stmt = $pdo->prepare("INSERT INTO leads (email, name, landing_page_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$email, 'Download Lead', $lpId]);
        
    } catch (Exception $e) {
        // Continue even if DB fails
    }
}

// Serve file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $filepath);
finfo_close($finfo);

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
readfile($filepath);
exit;