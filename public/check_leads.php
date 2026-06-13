<!DOCTYPE html>
<html>
<head><title>Check Leads</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Recent Leads for WP Starter Kit</h1>";
    
    // Check leads with landing page ID 18
    $stmt = $pdo->query("SELECT id, email, name, created_at, landing_page_id, source FROM leads WHERE landing_page_id = 18 ORDER BY id DESC LIMIT 10");
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($leads)) {
        echo "<p class='text-yellow-400'>No leads found for landing page ID 18</p>";
    } else {
        echo "<table class='w-full border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Email</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>Date</th></tr>";
        foreach ($leads as $l) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$l['id']}</td><td class='p-2'>{$l['email']}</td><td class='p-2'>{$l['name']}</td><td class='p-2'>{$l['created_at']}</td></tr>";
        }
        echo "</table>";
    }
    
    // Also check if any funnel_leads exist
    echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-emerald-400'>Funnel Leads (WP Funnel ID 2)</h2>";
    $stmt = $pdo->query("SELECT id, email, name, created_at FROM funnel_leads WHERE funnel_id = 2 ORDER BY id DESC LIMIT 5");
    $fleads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fleads)) {
        echo "<p class='text-yellow-400'>No funnel leads found</p>";
    } else {
        echo "<table class='w-full border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>Email</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>Date</th></tr>";
        foreach ($fleads as $l) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$l['email']}</td><td class='p-2'>{$l['name']}</td><td class='p-2'>{$l['created_at']}</td></tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>