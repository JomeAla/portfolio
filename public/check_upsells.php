<?php
$host = 'localhost'; $dbname = 'joalacom_joala'; $user = 'joalacom_joala'; $pass = 'J0ala@2024!';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); } catch (PDOException $e) { die("DB Error"); }

echo "=== POST-PURCHASE SEQUENCES (with upsells) ===\n\n";

// Get post-purchase sequences
$seqs = $pdo->query("SELECT id, name FROM email_sequences WHERE name LIKE 'Post-Purchase%' OR name LIKE 'Post Purchase%' ORDER BY id LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

foreach ($seqs as $s) {
    echo "=== {$s['name']} ===\n";
    
    $steps = $pdo->query("SELECT step_order, subject, LEFT(body, 150) as body_preview FROM sequence_steps WHERE sequence_id = {$s['id']} ORDER BY step_order")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($steps as $i => $st) {
        echo "Step {$st['step_order']}: {$st['subject']}\n";
        // Check if this contains upsell
        if(stripos($st['body_preview'], 'offer') || stripos($st['body_preview'], 'Get') || stripos($st['body_preview'], '/')) {
            echo "  -> Contains upsell/cross-sell!\n";
        }
    }
    echo "\n";
}

echo "=== DONE ===\n";