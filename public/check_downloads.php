<!DOCTYPE html>
<html>
<head><title>Check Downloads</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Check Downloads Folder</h1>";
    
    // Check downloads table
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>1. Downloads Table</h2>";
    $stmt = $pdo->query("SELECT * FROM downloads ORDER BY id DESC LIMIT 10");
    $downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($downloads)) {
        echo "<p class='text-yellow-400'>No downloads in table</p>";
    } else {
        echo "<table class='w-full border border-zinc-700 mb-4'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>File</th></tr>";
        foreach ($downloads as $d) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$d['id']}</td><td class='p-2'>{$d['name']}</td><td class='p-2'>{$d['file']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Check for WP Starter Kit specifically
    echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-yellow-400'>2. WordPress Starter Kit File</h2>";
    $stmt = $pdo->query("SELECT * FROM downloads WHERE file LIKE '%wordpress%' OR name LIKE '%wordpress%'");
    $wp = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($wp) {
        echo "<div class='p-4 border border-emerald-500 rounded bg-emerald-900/20'>";
        echo "<p class='text-emerald-400 font-bold'>✅ FOUND!</p>";
        echo "<p>File: {$wp['file']}</p>";
        echo "<p>Name: {$wp['name']}</p>";
        echo "</div>";
    } else {
        echo "<div class='p-4 border border-red-500 rounded bg-red-900/20'>";
        echo "<p class='text-red-400 font-bold'>❌ NOT FOUND!</p>";
        echo "<p class='text-yellow-400'>You need to upload the file or create a download record</p>";
        echo "</div>";
    }
    
    // Try to check if file exists via HTTP
    echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-emerald-400'>3. Direct File Check</h2>";
    echo "<p>Checking: https://www.joala.com.ng/downloads/wordpress-starter-kit.zip</p>";
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>