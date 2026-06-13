<?php
$host = 'localhost';
$dbname = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

header('Content-Type: text/plain');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

echo "=== Creating WhatsApp Marketing Lead Magnet Sequence ===\n\n";

$stmt = $pdo->prepare("INSERT IGNORE INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())");
$stmt->execute(['Lead Magnet - WhatsApp Marketing', 'Nurture for WhatsApp marketing guide']);

$seq = $pdo->lastInsertId();
echo "Created sequence ID: $seq\n";

$steps = [
    ['Your WhatsApp Marketing Guide', "Hi {{name}},

Thanks for downloading!

Download: https://www.joala.com.ng/whatsapp-marketing-guide.zip

This guide shows you how to get customers on WhatsApp the right way.

Key highlights:
- WhatsApp Business setup
- Status strategy that converts  
- Broadcast without getting blocked

Jome", 0, 1],
    
    ['WhatsApp Status Strategy', "Hi {{name}},

The #1 mistake people make? Posting boring status.

Here's what works:
- Product demos (quick 30-sec videos)
- Testimonials  
- Behind the scenes
- Offer highlights

Post 1-2x daily. Mix value + offer.

Jome", 3, 2],
    
    ['WhatsApp Marketing Bundle Offer', "Hi {{name}},

Get the full WhatsApp Marketing Bundle:
https://www.joala.com.ng/whatsapp-marketing-bundle

48 ready-to-use templates for:
- Broadcast messages
- Status updates
- Quick replies
- Catalog descriptions

Was ₦25,000 - Now ₦18,000

Jome", 7, 3],
];

foreach ($steps as $s) {
    $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([$seq, $s[0], $s[1], $s[2], $s[3]]);
    echo "Added: {$s[0]}\n";
}

echo "\n=== DONE! WhatsApp sequence created ===\n";