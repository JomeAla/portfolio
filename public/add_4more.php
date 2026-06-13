<?php
$host = 'localhost'; $dbname = 'joalacom_joala'; $user = 'joalacom_joala'; $pass = 'J0ala@2024!';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); } catch (PDOException $e) { die("DB Error"); }

$seqs = [
    ['Instagram Growth', 'instagram-guide.zip', 'Instagram Growth System', 'https://www.joala.com.ng/instagram-growth-system', '12 Insta Tips'],
    ['Nigerian Business Digital', 'nigerian-business-guide.zip', 'Nigerian Business Digital Kit', 'https://www.joala.com.ng/nigerian-business-digital-kit', '12 Digital Tips'],
    ['Church Website', 'church-website-guide.zip', 'Church Website Kit', 'https://www.joala.com.ng/church-website-kit', '12 Church Tips'],
    ['E-commerce Guide', 'ecommerce-guide.zip', 'E-commerce Starter Kit', 'https://www.joala.com.ng/ecommerce-starter-kit', '12 E-comm Tips']
];

foreach ($seqs as $s) {
    $pdo->prepare("INSERT IGNORE INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())")->execute(["Lead Magnet - {$s[0]}", "Nurture for {$s[0]}"]);
    $seq = $pdo->lastInsertId();
    echo "Created {$s[0]}\n";
    
    $steps = [
        ["Your {$s[0]} Guide", "Hi {{name}},\n\nDownload: https://www.joala.com.ng/{$s[1]}\n\nJome", 0, 1],
        ["Key Strategy", "Hi {{name}},\n\n{$s[4]}\n\nJome", 3, 2],
        ["{$s[2]} Offer", "Hi {{name}},\n\n{$s[2]}:\n{$s[3]}\n\nJome", 7, 3],
    ];
    foreach ($steps as $st) {
        $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([$seq, $st[0], $st[1], $st[2], $st[3]]);
    }
}

echo "\nDone! 4 more sequences.\n";