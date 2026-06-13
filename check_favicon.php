<?php
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Get favicon setting
    $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'favicon'");
    $favicon = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Favicon setting: " . ($favicon['value'] ?? 'NULL') . "\n";
    
    // Get logo setting
    $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'logo'");
    $logo = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Logo setting: " . ($logo['value'] ?? 'NULL') . "\n";
    
    // Check storage
    echo "\n=== Storage folder ===\n";
    if (is_dir('/home/joalacom/public_html/storage/app/public')) {
        echo "Storage public exists\n";
        $files = scandir('/home/joalacom/public_html/storage/app/public');
        foreach ($files as $f) {
            if ($f !== '.' && $f !== '..') {
                echo $f . "\n";
            }
        }
        
        if (is_dir('/home/joalacom/public_html/storage/app/public/logos')) {
            echo "\nLogos folder:\n";
            $logos = scandir('/home/joalacom/public_html/storage/app/public/logos');
            foreach ($logos as $l) {
                if ($l !== '.' && $l !== '..') {
                    echo $l . "\n";
                }
            }
        }
    } else {
        echo "Storage public does not exist!\n";
    }
    
    // Check public/uploads/logos
    echo "\n=== Public uploads/logos ===\n";
    if (is_dir('/home/joalacom/public_html/public/uploads/logos')) {
        $files = scandir('/home/joalacom/public_html/public/uploads/logos');
        foreach ($files as $f) {
            if ($f !== '.' && $f !== '..') {
                echo $f . "\n";
            }
        }
    } else {
        echo "Does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}