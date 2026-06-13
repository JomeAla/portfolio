<?php
/**
 * Create Email Sequences for Lead Magnets
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create Welcome Sequence for Free Email Checklist
    $stmt = $pdo->prepare("INSERT INTO email_sequences (name, description, is_active, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())");
    $stmt->execute(['Welcome Sequence', 'Emails sent after downloading free email checklist']);
    $seqId = $pdo->lastInsertId();
    
    // Email 1: Thank you + download link
    $stmt = $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, 0, 1, NOW(), NOW())");
    $stmt->execute([
        $seqId,
        'Here is your Email Marketing Checklist!',
        <<<HTML
<p>Hi {{name}},</p>
<p>Thanks for downloading my Email Marketing Checklist!</p>
<p>Here's your download link: <a href="https://joala.com.ng/download.php?file=email-marketing-checklist&email={{email}}">Download Email Marketing Checklist</a></p>
<p>This checklist covers everything you need for successful email campaigns:</p>
<ul>
<li>Pre-campaign setup</li>
<li>Subject line best practices</li>
<li>Content optimization</li>
<li>Technical setup</li>
<li>Compliance requirements</li>
</ul>
<p>Print it out and check off each item as you go!</p>
<p>Best,<br>Jome</p>
<p>P.S. Got questions? Reply to this email!</p>
HTML
    ]);
    
    // Email 2: Follow up 1 day later
    $stmt = $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, 1, 2, NOW(), NOW())");
    $stmt->execute([
        $seqId,
        'How is your checklist going?',
        <<<HTML
<p>Hi {{name}},</p>
<p>Hope you found the checklist useful!</p>
<p>I wanted to check in - have you had a chance to go through it?</p>
<p>When you're ready to implement email marketing, I'm here to help. Just reply to this email with any questions.</p>
<p>Best,<br>Jome</p>
HTML
    ]);
    
    // Email 3: Soft sell 3 days later
    $stmt = $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, 3, 3, NOW(), NOW())");
    $stmt->execute([
        $seqId,
        'Want more email templates?',
        <<<HTML
<p>Hi {{name}},</p>
<p>Since you found the checklist helpful, I thought you might like my Email Templates Pack.</p>
<p>It includes 6 ready-to-use sequences with 24 tested templates:</p>
<ul>
<li>Welcome sequence</li>
<li>Launch sequence</li>
<li>Cart abandonment</li>
<li>Post-purchase follow-up</li>
<li>Re-engagement sequence</li>
<li>And more!</li>
</ul>
<p>Get it here: <a href="https://joala.com.ng/email-templates">Email Templates Pack</a></p>
<p>Just ₦12,000 (regular ₦15,000)</p>
<p>Best,<br>Jome</p>
HTML
    ]);
    
    // Email 4: Final call 7 days later
    $stmt = $pdo->prepare("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order, created_at, updated_at) VALUES (?, ?, ?, 7, 4, NOW(), NOW())");
    $stmt->execute([
        $seqId,
        'Last chance -Email Templates Pack',
        <<<HTML
<p>Hi {{name}},</p>
<p>This is my final email about the templates pack.</p>
<p>If you're serious about email marketing, this pack will save you hours of work.</p>
<p>Get it now before the price goes back up: <a href="https://joala.com.ng/email-templates">Email Templates Pack</a></p>
<p>Best,<br>Jome</p>
HTML
    ]);
    
    echo "<h2>✅ Email Sequence Created!</h2>";
    echo "<p>Sequence ID: $seqId</p>";
    echo "<p>Steps: 4 emails (day 0, 1, 3, 7)</p>";
    echo "<p>Connect to landing page in admin panel</p>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}