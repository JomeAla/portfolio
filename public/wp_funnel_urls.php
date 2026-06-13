<!DOCTYPE html>
<html>
<head><title>WP Funnel URLs</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>WordPress Starter Kit Funnel URLs</h1>";
    
    // Get stages
    $stmt = $pdo->query("SELECT * FROM funnel_stages WHERE funnel_id = 2 ORDER BY `order`");
    $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table class='w-full border border-zinc-700 mb-8'><tr class='bg-zinc-800'><th class='p-2 text-left'>Stage</th><th class='p-2 text-left'>Type</th><th class='p-2 text-left'>Current Content</th></tr>";
    foreach ($stages as $s) {
        echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$s['name']}</td><td class='p-2'>{$s['type']}</td><td class='p-2 text-xs'>" . htmlspecialchars($s['content'] ?? '') . "</td></tr>";
    }
    echo "</table>";
    
    // Landing page URL
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>1. Landing Page URL</h2>";
    echo "<div class='mb-4 p-4 border border-emerald-500 rounded bg-emerald-900/20'>";
    echo "<p class='text-emerald-400 mb-2'>Use this slug in the landing_page type:</p>";
    echo "<code class='bg-zinc-800 px-2 py-1 rounded'>free-wordpress-starter-kit</code>";
    echo "<p class='mt-4 text-yellow-400'>OR the full URL:</p>";
    echo "<code class='bg-zinc-800 px-2 py-1 rounded text-sm'>/l/free-wordpress-starter-kit</code>";
    echo "</div>";
    
    // Download page
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>2. Download Page</h2>";
    echo "<div class='mb-4 p-4 border border-emerald-500 rounded bg-emerald-900/20'>";
    echo "<p class='text-emerald-400 mb-2'>Use type: <code>download</code></p>";
    echo "<p class='text-yellow-400 mb-2'>Content (JSON):</p>";
    echo "<pre class='bg-zinc-800 p-2 rounded text-xs overflow-x-auto'>{\"file\":\"wordpress-starter-kit.zip\"}</pre>";
    echo "<p class='mt-2 text-xs text-zinc-400'>Make sure the file exists at /downloads/wordpress-starter-kit.zip</p>";
    echo "</div>";
    
    // Thank you page
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>3. Thank You Page</h2>";
    echo "<div class='mb-4 p-4 border border-emerald-500 rounded bg-emerald-900/20'>";
    echo "<p class='text-emerald-400 mb-2'>Use type: <code>thankyou</code></p>";
    echo "<p class='text-yellow-400 mb-2'>Content (JSON):</p>";
    echo "<pre class='bg-zinc-800 p-2 rounded text-xs overflow-x-auto'>{\"message\":\"Check your email for the download link!\",\"button_text\":\"Download Again\"}</pre>";
    echo "</div>";
    
    // Show actual live URLs
    echo "<h2 class='text-xl font-semibold mb-4 text-yellow-400'>Live URLs</h2>";
    echo "<ul class='list-disc pl-5 space-y-2'>";
    echo "<li><strong>Landing:</strong> <a href='https://www.joala.com.ng/l/free-wordpress-starter-kit' class='text-blue-400 underline'>https://www.joala.com.ng/l/free-wordpress-starter-kit</a></li>";
    echo "<li><strong>Download:</strong> https://www.joala.com.ng/downloads/wordpress-starter-kit.zip</li>";
    echo "<li><strong>Thank You:</strong> (shows message after download)</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>