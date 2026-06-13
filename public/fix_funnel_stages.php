<!DOCTYPE html>
<html>
<head>
<title>Fix WP Funnel Stages - Direct</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Fix WP Funnel Stages - Direct</h1>";
    
    // Delete old stages
    $pdo->exec("DELETE FROM funnel_stages WHERE funnel_id = 2");
    echo "<p class='text-yellow-400'>1. Deleted old stages</p>";
    
    // Insert new stages
    $pdo->exec("INSERT INTO funnel_stages (funnel_id, name, type, `order`, content) VALUES
        (2, 'Landing Page', 'landing_page', 1, '{\"page_slug\":\"free-wordpress-starter-kit\"}'),
        (2, 'Download Page', 'download', 2, '{\"file\":\"wordpress-starter-kit.zip\"}'),
        (2, 'Thank You', 'thankyou', 3, '{\"message\":\"Check your email for the download link!\",\"button_text\":\"Download Again\"}')");
    echo "<p class='text-yellow-400'>2. Inserted new stages</p>";
    
    // Update funnel welcome_sequence
    $pdo->exec("UPDATE funnels SET welcome_sequence_id = 21 WHERE id = 2");
    echo "<p class='text-yellow-400'>3. Updated welcome_sequence_id to 21</p>";
    
    // Verify
    echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-emerald-400'>Verification</h2>";
    $stmt = $pdo->query("SELECT f.name, f.welcome_sequence_id, fs.name as stage_name, fs.type, fs.order 
        FROM funnels f 
        LEFT JOIN funnel_stages fs ON f.id = fs.funnel_id 
        WHERE f.id = 2 
        ORDER BY fs.order");
    
    echo "<table class='w-full border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>Stage</th><th class='p-2 text-left'>Type</th><th class='p-2 text-left'>Order</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$row['stage_name']}</td><td class='p-2'>{$row['type']}</td><td class='p-2'>{$row['order']}</td></tr>";
    }
    echo "</table>";
    
    echo "<p class='text-emerald-400 font-bold mt-8'>✅ WordPress Starter Kit Funnel is now fixed!</p>";
    echo "<p class='mt-4'>The stages have been updated directly in the database.</p>";
    echo "<p>You can verify by visiting: <a href='https://www.joala.com.ng/l/free-wordpress-starter-kit' class='text-blue-400 underline'>Landing Page</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>