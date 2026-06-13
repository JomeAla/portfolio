<!DOCTYPE html>
<html>
<head><title>Fix WP Funnel Stages</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Fixing WP Funnel Stages</h1>";
    
    // 1. Delete all existing stages for funnel 2
    $pdo->exec("DELETE FROM funnel_stages WHERE funnel_id = 2");
    echo "<p class='text-yellow-400'>1. Deleted old stages</p>";
    
    // 2. Insert correct stages
    $pdo->exec("INSERT INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at) VALUES
        (2, 'Landing Page', 'landing_page', 1, '{\"page_slug\":\"free-wordpress-starter-kit\"}', NOW(), NOW()),
        (2, 'Download Page', 'download', 2, '{\"file\":\"wordpress-starter-kit.zip\"}', NOW(), NOW()),
        (2, 'Thank You', 'thankyou', 3, '{\"message\":\"Check your email for the download link!\",\"button_text\":\"Download Again\"}', NOW(), NOW())");
    echo "<p class='text-yellow-400'>2. Inserted new stages</p>";
    
    // 3. Update funnel welcome_sequence to 21
    $pdo->exec("UPDATE funnels SET welcome_sequence_id = 21 WHERE id = 2");
    echo "<p class='text-yellow-400'>3. Updated welcome_sequence_id to 21</p>";
    
    // 4. Verify
    echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-emerald-400'>Verification</h2>";
    $stmt = $pdo->query("SELECT fs.id, fs.name, fs.type, fs.order, fs.content, f.welcome_sequence_id
        FROM funnel_stages fs
        JOIN funnels f ON fs.funnel_id = f.id
        WHERE f.id = 2
        ORDER BY fs.order");
    
    echo "<table class='w-full border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>Type</th><th class='p-2 text-left'>Order</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$row['id']}</td><td class='p-2'>{$row['name']}</td><td class='p-2'>{$row['type']}</td><td class='p-2'>{$row['order']}</td></tr>";
    }
    echo "</table>";
    
    echo "<p class='text-emerald-400 mt-4 font-bold'>✅ WordPress Starter Kit Funnel is now properly configured!</p>";
    echo "<p class='mt-4'>Funnel ID: 2</p>";
    echo "<p>Welcome Sequence ID: 21</p>";
    echo "<p>3 Stages: Landing Page → Download Page → Thank You</p>";
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>