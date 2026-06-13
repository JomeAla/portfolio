<?php
$host = 'localhost';
$dbname = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Error");
}

$stmt = $pdo->prepare("INSERT IGNORE INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())");
$stmt->execute(['Lead Magnet - Course Creation', 'Nurture for course creation']);

$seq = $pdo->lastInsertId();
echo "Created Course Creation sequence\n";

$steps = [
    ['Your Course Creation Checklist', "Hi {{name}},

Download: https://www.joala.com.ng/course-creation-checklist.zip

Turn your knowledge into a profitable course step by step.

Jome", 0, 1],
    ['Find Your Profitable Topic', "Hi {{name}},

The biggest question: What should you teach?

Ask yourself:
- What do people pay me for?
- What problems do I solve?
- What do I enjoy teaching?

Pick ONE topic. Deep, not wide.

Jome", 3, 2],
    ['Course Creator Kit Offer', "Hi {{name}},

Get the full Course Creator Kit:
https://www.joala.com.ng/course-creator-kit

50+ templates:
- Landing page
- Module outlines
- Student onboarding
- Launch emails

Was ₦25,000 - Now ₦18,000

Jome", 7, 3],
];

foreach ($steps as $s) {
    $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([$seq, $s[0], $s[1], $s[2], $s[3]]);
    echo "Added: {$s[0]}\n";
}

echo "\n=== DONE! ===\n";