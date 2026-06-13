<!DOCTYPE html>
<html>
<head><title>Check WP Lead Flow</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>WordPress Funnel Flow Verification</h1>";
    
    // 1. Check most recent lead
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>1. Latest Lead (ID 12)</h2>";
    $stmt = $pdo->query("SELECT * FROM leads WHERE id = 12");
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($lead) {
        echo "<div class='mb-8 p-4 border border-zinc-700 rounded'>";
        echo "<p><strong>Email:</strong> {$lead['email']}</p>";
        echo "<p><strong>Name:</strong> {$lead['name']}</p>";
        echo "<p><strong>Landing Page ID:</strong> {$lead['landing_page_id']}</p>";
        echo "<p><strong>Created:</strong> {$lead['created_at']}</p>";
        echo "<p><strong>Source:</strong> {$lead['source']}</p>";
        echo "</div>";
    }
    
    // 2. Check if email was sent to sequence
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>2. Email Sequence Queue</h2>";
    $stmt = $pdo->query("SELECT * FROM email_queue WHERE lead_id = 12 ORDER BY id DESC LIMIT 5");
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($emails)) {
        echo "<p class='text-yellow-400 mb-4'>No emails queued for this lead - download email NOT sent!</p>";
    } else {
        echo "<table class='w-full mb-8 border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>Subject</th><th class='p-2 text-left'>Status</th><th class='p-2 text-left'>Sent</th></tr>";
        foreach ($emails as $e) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$e['id']}</td><td class='p-2'>{$e['subject']}</td><td class='p-2'>{$e['status']}</td><td class='p-2'>{$e['sent_at']}</td></tr>";
        }
        echo "</table>";
    }
    
    // 3. Check downloads table
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>3. Downloads</h2>";
    $stmt = $pdo->query("SELECT * FROM downloads WHERE lead_id = 12 ORDER BY id DESC LIMIT 5");
    $dls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($dls)) {
        echo "<p class='text-yellow-400 mb-4'>No download records found</p>";
    } else {
        echo "<table class='w-full mb-8 border border-zinc-700'><tr class='bg-zinc-800'><th class='p-2 text-left'>ID</th><th class='p-2 text-left'>File</th><th class='p-2 text-left'>Date</th></tr>";
        foreach ($dls as $d) {
            echo "<tr class='border-t border-zinc-700'><td class='p-2'>{$d['id']}</td><td class='p-2'>{$d['file']}</td><td class='p-2'>{$d['created_at']}</td></tr>";
        }
        echo "</table>";
    }
    
    // 4. Check sequence step 1 content
    echo "<h2 class='text-xl font-semibold mb-4 text-emerald-400'>4. Email Step 1 Content</h2>";
    $stmt = $pdo->query("SELECT ss.subject, ss.body, ss.step_order FROM sequence_steps ss WHERE ss.sequence_id = 21 AND ss.step_order = 1");
    $step = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($step) {
        echo "<div class='mb-8 p-4 border border-zinc-700 rounded'>";
        echo "<p><strong>Subject:</strong> {$step['subject']}</p>";
        echo "<p><strong>Step:</strong> {$step['step_order']}</p>";
        echo "<p class='mt-2'><strong>Body:</strong></p>";
        echo "<pre class='mt-2 p-2 bg-zinc-800 rounded overflow-x-auto'>{$step['body']}</pre>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>