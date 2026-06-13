<!DOCTYPE html>
<html>
<head><title>Check LP Config</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Landing Page Configuration</h1>";
    
    // Check landing page settings
    $stmt = $pdo->query("SELECT * FROM landing_pages WHERE slug = 'free-wordpress-starter-kit'");
    $lp = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lp) {
        echo "<div class='mb-8 p-4 border border-zinc-700 rounded'>";
        echo "<p><strong>ID:</strong> {$lp['id']}</p>";
        echo "<p><strong>Slug:</strong> {$lp['slug']}</p>";
        echo "<p><strong>Sequence ID:</strong> " . ($lp['sequence_id'] ?? 'NULL/MISSING!') . "</p>";
        echo "<p><strong>Funnel ID:</strong> " . ($lp['funnel_id'] ?? 'NULL') . "</p>";
        echo "<p><strong>Active:</strong> " . ($lp['is_active'] ? 'Yes' : 'No') . "</p>";
        echo "</div>";
        
        if (!$lp['sequence_id']) {
            echo "<p class='text-red-400 font-bold'>❌ CRITICAL: No sequence_id set! This is why no email is sent.</p>";
            echo "<p class='mt-2'>Fix: UPDATE landing_pages SET sequence_id = 21 WHERE slug = 'free-wordpress-starter-kit';</p>";
        }
        
        if (!$lp['funnel_id']) {
            echo "<p class='text-yellow-400 mt-2'>Note: No funnel_id set either</p>";
        }
    } else {
        echo "<p class='text-red-400'>Landing page not found!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>