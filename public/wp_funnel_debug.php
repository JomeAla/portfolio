<!DOCTYPE html>
<html>
<head><title>WP Funnel Stages</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>WordPress Starter Kit Funnel (ID 2) - Full Analysis</h1>";
    
    // 1. Funnel details
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>1. Funnel Details</h2>";
    $stmt = $pdo->query("SELECT * FROM funnels WHERE id = 2");
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($funnel) {
        echo "<div class='mb-8 p-4 border border-zinc-700 rounded'>";
        foreach ($funnel as $k => $v) {
            echo "<p><strong>$k:</strong> " . ($v ?? 'NULL') . "</p>";
        }
        echo "</div>";
    } else {
        echo "<p class='text-red-400'>Funnel ID 2 NOT FOUND!</p>";
    }
    
    // 2. Funnel stages
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>2. Funnel Stages</h2>";
    $stmt = $pdo->query("SELECT * FROM funnel_stages WHERE funnel_id = 2 ORDER BY `order`");
    $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($stages)) {
        echo "<p class='text-red-400 mb-4'>❌ NO STAGES DEFINED! This is the problem.</p>";
    } else {
        echo "<table class='w-full mb-8 border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>Type</th><th class='p-2 text-left'>Order</th><th class='p-2 text-left'>Content</th></tr>";
        foreach ($stages as $s) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$s['id']}</td><td class='p-2'>{$s['name']}</td><td class='p-2'>{$s['type']}</td><td class='p-2'>{$s['order']}</td><td class='p-2 text-xs'>" . substr($s['content'] ?? '', 0, 50) . "</td></tr>";
        }
        echo "</table>";
        echo "<p class='text-emerald-400 mb-4'>✅ Found " . count($stages) . " stages</p>";
    }
    
    // 3. Check if content has URLs
    echo "<h2 class='text-xl font-semibold mb-4 text-yellow-400'>3. Stage URLs</h2>";
    foreach ($stages as $s) {
        $content = json_decode($s['content'], true);
        if ($content) {
            echo "<p class='mb-2'>{$s['name']}: " . print_r($content, true) . "</p>";
        }
    }
    
    // 4. Check landing page URL
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>4. Landing Page Connection</h2>";
    $stmt = $pdo->query("SELECT id, slug, funnel_id FROM landing_pages WHERE slug = 'free-wordpress-starter-kit'");
    $lp = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($lp) {
        echo "<p>LP Funnel ID: {$lp['funnel_id']}</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>