<!DOCTYPE html>
<html>
<head>
<title>Funnel Test</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-900 text-white p-8">
<?php
// Simple test to check if funnel can be loaded
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Testing Funnel ID 2 Load</h1>";
    
    // Get funnel
    $stmt = $pdo->query("SELECT * FROM funnels WHERE id = 2");
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($funnel) {
        echo "<div class='mb-8 p-4 border border-emerald-500 rounded bg-emerald-900/20'>";
        echo "<p class='text-emerald-400 font-bold'>✅ Funnel FOUND in database</p>";
        echo "<p class='mt-2'><strong>Name:</strong> {$funnel['name']}</p>";
        echo "<p><strong>Slug:</strong> {$funnel['slug']}</p>";
        echo "<p><strong>Active:</strong> " . ($funnel['is_active'] ? 'Yes' : 'No') . "</p>";
        echo "</div>";
        
        // Get stages
        $stmt = $pdo->query("SELECT * FROM funnel_stages WHERE funnel_id = 2 ORDER BY `order`");
        $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>Stages (" . count($stages) . ")</h2>";
        foreach ($stages as $s) {
            echo "<p class='mb-2'>- {$s['name']} ({$s['type']})</p>";
        }
        
        echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-yellow-400'>Manual SQL Fix</h2>";
        echo "<p>If editing doesn't work, run this SQL in phpMyAdmin:</p>";
        echo "<pre class='mt-2 p-2 bg-zinc-800 rounded text-xs'>DELETE FROM funnel_stages WHERE funnel_id = 2;
INSERT INTO funnel_stages (funnel_id, name, type, \`order\`, content) VALUES
(2, 'Landing Page', 'landing_page', 1, '{\"page_slug\":\"free-wordpress-starter-kit\"}'),
(2, 'Download Page', 'download', 2, '{\"file\":\"wordpress-starter-kit.zip\"}'),
(2, 'Thank You', 'thankyou', 3, '{\"message\":\"Check your email!\"}');
UPDATE funnels SET welcome_sequence_id = 21 WHERE id = 2;</pre>";
        
    } else {
        echo "<p class='text-red-400'>❌ Funnel NOT FOUND!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>