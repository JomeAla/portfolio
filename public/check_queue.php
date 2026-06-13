<!DOCTYPE html>
<html>
<head><title>Check Email Queue</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Email Queue Table Check</h1>";
    
    // Check if email_queue table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'email_queue'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "<p class='text-red-400 font-bold'>❌ Table 'email_queue' does NOT exist!</p>";
        
        // Check other email-related tables
        echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-yellow-400'>Available tables:</h2>";
        $stmt = $pdo->query("SHOW TABLES");
        echo "<ul class='list-disc pl-5'>";
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            if (stripos($row[0], 'email') !== false || stripos($row[0], 'queue') !== false || stripos($row[0], 'mail') !== false) {
                echo "<li>{$row[0]}</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p class='text-emerald-400 mb-4'>✅ email_queue table exists</p>";
        
        // Check structure
        $stmt = $pdo->query("DESCRIBE email_queue");
        echo "<h2 class='text-xl font-semibold mt-8 mb-4'>Structure:</h2>";
        echo "<table class='w-full border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>Field</th><th class='p-2 text-left'>Type</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$row['Field']}</td><td class='p-2'>{$row['Type']}</td></tr>";
        }
        echo "</table>";
        
        // Check any emails in queue
        $stmt = $pdo->query("SELECT * FROM email_queue ORDER BY id DESC LIMIT 5");
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($emails)) {
            echo "<p class='text-yellow-400 mt-4'>No emails in queue</p>";
        } else {
            echo "<h2 class='text-xl font-semibold mt-8 mb-4'>Recent emails:</h2>";
            echo "<pre class='p-2 bg-zinc-800 rounded'>" . print_r($emails, true) . "</pre>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>