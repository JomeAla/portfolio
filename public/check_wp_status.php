<!DOCTYPE html>
<html>
<head>
    <title>WP Funnel Status</title>
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
    
    echo "<h1 class='text-2xl font-bold mb-8'>WordPress Starter Kit Funnel Status</h1>";
    
    // 1. Check funnels
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>1. Funnels</h2>";
    $stmt = $pdo->query("SELECT id, name, is_active, description FROM funnels WHERE name LIKE '%WordPress%' OR name LIKE '%wordpress%' OR name LIKE '%Wordpress%'");
    $funnels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($funnels)) {
        echo "<p class='text-red-400 mb-4'>No WordPress funnels found!</p>";
    } else {
        echo "<table class='w-full mb-8 border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>Active</th></tr>";
        foreach ($funnels as $f) {
            $active = $f['is_active'] ? '<span class="text-emerald-400">Yes</span>' : '<span class="text-red-400">No</span>';
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$f['id']}</td><td class='p-2'>{$f['name']}</td><td class='p-2'>$active</td></tr>";
        }
        echo "</table>";
    }
    
    // 2. Check funnel stages (join with funnels)
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>2. Funnel Stages</h2>";
    $stmt = $pdo->query("SELECT fs.id, fs.funnel_id, fs.name, fs.type, fs.order, f.name as funnel_name
        FROM funnel_stages fs 
        LEFT JOIN funnels f ON fs.funnel_id = f.id 
        WHERE f.name LIKE '%WordPress%'
        ORDER BY fs.funnel_id, fs.order");
    $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($stages)) {
        echo "<p class='text-yellow-400 mb-4'>No stages found for WordPress funnels</p>";
    } else {
        echo "<table class='w-full mb-8 border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>Stage</th><th class='p-2 text-left'>Type</th><th class='p-2 text-left'>Order</th></tr>";
        foreach ($stages as $s) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$s['name']}</td><td class='p-2'>{$s['type']}</td><td class='p-2'>{$s['order']}</td></tr>";
        }
        echo "</table>";
    }
    
    // 3. Check landing pages
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>3. Landing Page</h2>";
    $stmt = $pdo->query("SELECT id, title, slug, is_active, sequence_id, funnel_id FROM landing_pages WHERE slug = 'free-wordpress-starter-kit'");
    $lp = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($lp) {
        echo "<div class='mb-8 p-4 border border-zinc-700 rounded'>";
        echo "<p><strong>Slug:</strong> {$lp['slug']}</p>";
        echo "<p><strong>Active:</strong> " . ($lp['is_active'] ? 'Yes' : 'No') . "</p>";
        echo "<p><strong>Sequence ID:</strong> {$lp['sequence_id']}</p>";
        echo "<p><strong>Funnel ID:</strong> {$lp['funnel_id']}</p>";
        echo "</div>";
    } else {
        echo "<p class='text-red-400 mb-4'>Landing page not found!</p>";
    }
    
    // 4. Check email sequences
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>4. Email Sequences</h2>";
    $stmt = $pdo->query("SELECT id, name, is_active FROM email_sequences WHERE name LIKE '%WordPress%'");
    $seqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($seqs)) {
        echo "<p class='text-yellow-400 mb-4'>No sequences found - lead won't receive download email!</p>";
    } else {
        echo "<table class='w-full mb-8 border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Name</th><th class='p-2 text-left'>Active</th></tr>";
        foreach ($seqs as $s) {
            $active = $s['is_active'] ? '<span class="text-emerald-400">Yes</span>' : '<span class="text-red-400">No</span>';
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$s['id']}</td><td class='p-2'>{$s['name']}</td><td class='p-2'>$active</td></tr>";
        }
        echo "</table>";
    }
    
    // 5. Check sequence steps
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>5. Sequence Steps</h2>";
    $stmt = $pdo->query("SELECT ss.id, ss.subject, ss.step_order, es.name as seq_name
        FROM sequence_steps ss
        LEFT JOIN email_sequences es ON ss.sequence_id = es.id
        WHERE es.name LIKE '%WordPress%'
        ORDER BY ss.step_order");
    $steps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($steps)) {
        echo "<p class='text-yellow-400 mb-4'>No email steps configured!</p>";
    } else {
        echo "<table class='w-full mb-8 border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>Subject</th><th class='p-2 text-left'>Step</th></tr>";
        foreach ($steps as $s) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$s['subject']}</td><td class='p-2'>{$s['step_order']}</td></tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>