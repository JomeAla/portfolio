<?php
$host = 'localhost'; $dbname = 'joalacom_joala'; $user = 'joalacom_joala'; $pass = 'J0ala@2024!';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); } catch (PDOException $e) { die("DB Error"); }

echo "=== Creating Last 3 Lead Magnet Sequences ===\n\n";

$seqs = [
    ['Real Estate Property', 'real-estate-guide.zip', 'Real Estate Property Kit'],
    ['Restaurant POS', 'restaurant-pos-guide.zip', 'Restaurant POS Kit'],
    ['School Management', 'school-management-guide.zip', 'School Management System'],
];

foreach ($seqs as $s) {
    $pdo->prepare("INSERT IGNORE INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())")->execute(["Lead Magnet - {$s[0]}", "Nurture for {$s[0]}"]);
    $seq = $pdo->lastInsertId();
    if($seq == 0) {
        $row = $pdo->query("SELECT id FROM email_sequences WHERE name = 'Lead Magnet - {$s[0]}'")->fetch();
        $seq = $row['id'];
    }
    echo "Created: {$s[0]} (ID: $seq)\n";
    
    $steps = [
        ["Your {$s[0]} Guide", "Hi {{name}},\n\nThanks for downloading!\n\nDownload: https://www.joala.com.ng/{$s[1]}\n\nChecklist inside.\n\nJome", 0, 1],
        ["Key Tip - {$s[0]}", "Hi {{name}},\n\nKey tip for {$s[0]}:\n\n1. Start with basics\n2. Be consistent\n3. Track everything\n\nJome", 3, 2],
        ["{$s[2]} Offer", "Hi {{name}},\n\nGet {$s[2]}:\nhttps://www.joala.com.ng/" . strtolower(str_replace(' ', '-', $s[2])) . "\n\nFull kit with templates.\n\nJome", 7, 3],
    ];
    
    $pdo->exec("DELETE FROM sequence_steps WHERE sequence_id = $seq");
    foreach ($steps as $st) {
        $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([$seq, $st[0], $st[1], $st[2], $st[3]]);
    }
}

echo "\n=== DONE! ===\n";