<?php
$host = 'localhost'; $dbname = 'joalacom_joala'; $user = 'joalacom_joala'; $pass = 'J0ala@2024!';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); } catch (PDOException $e) { die("DB Error"); }

echo "=== CHECKING LEAD MAGNET SEQUENCES ===\n\n";

$seqs = $pdo->query("SELECT id, name FROM email_sequences WHERE name LIKE 'Lead Magnet%' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

foreach ($seqs as $s) {
    echo "Sequence: {$s['name']} (ID: {$s['id']})\n";
    
    $steps = $pdo->query("SELECT step_order, subject, LEFT(body, 100) as body_preview FROM sequence_steps WHERE sequence_id = {$s['id']} ORDER BY step_order")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($steps as $st) {
        echo "  Step {$st['step_order']}: {$st['subject']}\n";
    }
    echo "\n";
}

echo "=== DONE ===\n";