<!DOCTYPE html>
<html>
<head><title>Check Funnels List</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>All Funnels in Database</h1>";
    
    $stmt = $pdo->query("SELECT id, name, slug, funnel_type, is_active, goal, product_id, welcome_sequence_id, created_at FROM funnels ORDER BY id");
    $funnels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table class='w-full border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>Slug</th><th class='p-2 text-left'>Type</th><th class='p-2 text-left'>Active</th><th class='p-2 text-left'>Goal</th></tr>";
    foreach ($funnels as $f) {
        $active = $f['is_active'] ? '<span class="text-emerald-400">Yes</span>' : '<span class="text-red-400">No</span>';
        echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$f['id']}</td><td class='p-2'>{$f['name']}</td><td class='p-2'>{$f['slug']}</td><td class='p-2'>{$f['funnel_type']}</td><td class='p-2'>$active</td><td class='p-2'>{$f['goal']}</td></tr>";
    }
    echo "</table>";
    
    echo "<p class='mt-4 text-yellow-400'>Total: " . count($funnels) . " funnels</p>";
    
    // Check if WordPress is in list
    echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-emerald-400'>WordPress Funnel Details</h2>";
    $stmt = $pdo->query("SELECT * FROM funnels WHERE name LIKE '%WordPress%'");
    $wp = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($wp)) {
        echo "<p class='text-red-400'>❌ WordPress funnel NOT FOUND in database!</p>";
    } else {
        foreach ($wp as $w) {
            echo "<div class='mt-4 p-4 border border-emerald-500 rounded'>";
            echo "<p><strong>ID:</strong> {$w['id']} - Found!</p>";
            echo "<p><strong>is_active:</strong> {$w['is_active']}</p>";
            echo "<p><strong>funnel_type:</strong> {$w['funnel_type']}</p>";
            echo "</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>