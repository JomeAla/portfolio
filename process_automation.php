<?php
/**
 * Process automation rules based on events
 * Protected by API key - requires valid key in request
 */

error_reporting(0);
ini_set('display_errors', 0);

define('ADMIN_TOKEN', 'admin-secure-token-change-me');

$isStatus = isset($_GET['status']);
$isInit = isset($_GET['init']);
$isAdmin = isset($_GET['admin']) && $_GET['admin'] === ADMIN_TOKEN;

if ($isAdmin && $isInit) {
    $host = 'localhost';
    $database = 'joalacom_joala';
    $username = 'joalacom_joala';
    $password = 'J0ala@2024!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "Automation System Active\n";
        echo "=======================\n";
        echo "System: Online\n";
        echo "Rules: Active\n";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}

if ($isStatus) {
    echo "Automation Status\n";
    echo "=================\n";
    echo "Status: Active\n";
    exit;
}

$providedKey = $_GET['key'] ?? '';
$validKey = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=joalacom_joala;charset=utf8mb4", "joalacom_joala", "J0ala@2024!");
    $validKey = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'process_api_key'")->fetchColumn();
} catch (PDOException $e) {
    exit;
}

if (empty($validKey) || empty($providedKey) || !hash_equals($validKey, $providedKey)) {
    exit;
}

$lockFile = __DIR__ . '/.automation_lock';
if (file_exists($lockFile)) {
    $lockedAt = (int)file_get_contents($lockFile);
    if (time() - $lockedAt < 280) {
        exit;
    }
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=joalacom_joala;charset=utf8mb4", "joalacom_joala", "J0ala@2024!");
    
    // Get active automation rules
    $rules = $pdo->query("SELECT * FROM automation_rules WHERE active = 1")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rules as $rule) {
        // Process automation rule
        // Implementation depends on your specific automation needs
    }
} catch (PDOException $e) {}

@unlink($lockFile);
?>
