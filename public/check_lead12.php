<!DOCTYPE html>
<html>
<head><title>Check Lead 12</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-zinc-900 text-white p-8">
<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1 class='text-2xl font-bold mb-8'>Lead 12 Details</h1>";
    
    $stmt = $pdo->query("SELECT * FROM leads WHERE id = 12");
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        echo "<div class='mb-8 p-4 border border-zinc-700 rounded'>";
        foreach ($lead as $key => $value) {
            echo "<p><strong>$key:</strong> " . ($value ?? 'NULL') . "</p>";
        }
        echo "</div>";
    }
    
    echo "<h2 class='text-xl font-semibold mt-8 mb-4 text-yellow-400'>Why No Email?</h2>";
    echo "<p>Lead has sequence_id: " . ($lead['sequence_id'] ?? 'NULL') . "</p>";
    
    // The issue: firstOrCreate doesn't update sequence_id if lead exists
    // So even though landing page has sequence_id=21, the lead might not get it
    
    echo "<h2 class='text-xl font-semibold mt-8 mb-4'>Check Code Logic</h2>";
    echo "<p>The submitLead code uses firstOrCreate() - if lead already exists, it won't update sequence_id!</p>";
    echo "<p class='mt-2 text-yellow-400'>This lead is NEW so it should work... let me check enrollLeadInSequence()</p>";
    
} catch (PDOException $e) {
    echo "<p class='text-red-400'>Error: " . $e->getMessage() . "</p>";
}
?>
</body>
</html>