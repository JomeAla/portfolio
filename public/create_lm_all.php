<?php
$host = 'localhost'; $dbname = 'joalacom_joala'; $user = 'joalacom_joala'; $pass = 'J0ala@2024!';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); } catch (PDOException $e) { die("DB Error"); }

echo "=== Creating All 10 Lead Magnet Sequences ===\n\n";

$seqs = [
    ['WhatsApp Marketing', 'whatsapp-marketing-guide.zip', 'WhatsApp Marketing Bundle'],
    ['Course Creation', 'course-creation-checklist.zip', 'Course Creator Kit'],
    ['Local Business Digital', 'local-business-guide.zip', 'Local Business Digital Kit'],
    ['SaaS Starter', 'saas-starter-guide.zip', 'SaaS Starter Kit'],
    ['Website Audit', 'web-audit-guide.zip', 'Website Audit Kit'],
    ['Instagram Growth', 'instagram-guide.zip', 'Instagram Growth System'],
    ['Nigerian Business Digital', 'nigerian-business-guide.zip', 'Nigerian Business Digital Kit'],
    ['Church Website', 'church-website-guide.zip', 'Church & Organization Website Kit'],
    ['E-commerce Guide', 'ecommerce-guide.zip', 'E-commerce Starter Kit'],
];

$added = 0;
foreach ($seqs as $s) {
    $pdo->prepare("INSERT IGNORE INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())")->execute(["Lead Magnet - {$s[0]}", "Nurture for {$s[0]}"]);
    $seq = $pdo->lastInsertId();
    if($seq == 0) {
        $row = $pdo->query("SELECT id FROM email_sequences WHERE name = 'Lead Magnet - {$s[0]}'")->fetch();
        $seq = $row['id'];
    }
    echo "Creating: {$s[0]} (ID: $seq)\n";
    
    $steps = [
        ["Your {$s[0]} Guide", "Hi {{name}},\n\nThanks for downloading!\n\nDownload: https://www.joala.com.ng/{$s[1]}\n\nThis guide will help you get started.\n\nAny questions? Just reply.\n\nJome", 0, 1],
        ["Key Strategy - {$s[0]}", "Hi {{name}},\n\nHere's the key strategy for {$s[0]}:\n\n1. Start small\n2. Be consistent\n3. Measure results\n\nImplement these 3 and you'll see progress.\n\nQuestions? Reply.\n\nJome", 3, 2],
        ["Special Offer - {$s[2]}", "Hi {{name}},\n\nGet full {$s[2]}:\nhttps://www.joala.com.ng/" . strtolower(str_replace(' ', '-', $s[2])) . "\n\nIncludes:\n- Complete templates\n- Step-by-step guide\n- Bonus resources\n\nSpecial price available - just ask!\n\nJome", 7, 3],
    ];
    
    $pdo->exec("DELETE FROM sequence_steps WHERE sequence_id = $seq");
    foreach ($steps as $st) {
        $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([$seq, $st[0], $st[1], $st[2], $st[3]]);
    }
    $added++;
}

echo "\n=== DONE! Created $added lead magnet sequences ===\n";
echo "Check http://www.joala.com.ng/check-sequences\n";