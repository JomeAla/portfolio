<?php
$host = 'localhost'; $dbname = 'joalacom_joala'; $user = 'joalacom_joala'; $pass = 'J0ala@2024!';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); } catch (PDOException $e) { die("DB Error"); }

$seqs = [
    ['Local Business Digital', 'local-business-guide.zip', 'Local Business Digital Kit', 'https://www.joala.com.ng/local-business-digital-kit', '10 Local Digital Guide'],
    ['SaaS Starter', 'saas-starter-guide.zip', 'SaaS Starter Kit', 'https://www.joala.com.ng/saas-starter-kit', '12 SaaS Starter Guide'],
    ['Website Audit', 'web-audit-guide.zip', 'Website Audit Kit', 'https://www.joala.com.ng/website-audit-kit', '10 Web Audit Guide']
];

foreach ($seqs as $s) {
    $pdo->prepare("INSERT IGNORE INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())")->execute(["Lead Magnet - {$s[0]}", "Nurture for {$s[0]}"]);
    $seq = $pdo->lastInsertId();
    echo "Created {$s[0]}\n";
    
    $steps = [
        ["Your {$s[0]} Guide", "Hi {{name}},\n\nDownload: https://www.joala.com.ng/{$s[1]}\n\nJome", 0, 1],
        ["Key Strategy", "Hi {{name}},\n\nKey tip: {$s[4]}\n\nJome", 3, 2],
        ["{$s[2]} Offer", "Hi {{name}},\n\nGet {$s[2]}:\n{$s[3]}\n\nJome", 7, 3],
    ];
    foreach ($steps as $st) {
        $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([$seq, $st[0], $st[1], $st[2], $st[3]]);
    }
}

echo "\nDone! 3 more sequences created.\n";