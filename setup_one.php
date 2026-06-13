<?php
error_reporting(0);
$conn = new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

$products = [
    1 => ['name' => 'Email Sequence Templates Pack', 'file' => 'email-templates-pack.zip'],
    2 => ['name' => 'Email Marketing Premium Bundle', 'file' => 'premium-bundle.zip'],
    3 => ['name' => 'Done-For-You Email Automation', 'file' => 'dfy-automation.zip'],
    4 => ['name' => 'WhatsApp Marketing Bundle', 'file' => 'whatsapp-bundle.zip'],
    5 => ['name' => 'Course Creator Kit', 'file' => 'course-creator-kit.zip'],
    6 => ['name' => 'Local Business Digital Kit', 'file' => 'local-business-kit.zip'],
    7 => ['name' => 'SaaS Starter Kit', 'file' => 'saas-starter-kit.zip'],
    8 => ['name' => 'Freelancer Toolkit', 'file' => 'freelancer-toolkit.zip'],
    9 => ['name' => 'Instagram Growth System', 'file' => 'instagram-growth.zip'],
    10 => ['name' => 'Nigerian Business Digital Kit', 'file' => 'nigeria-business-kit.zip'],
    11 => ['name' => 'Church & Organization Website Kit', 'file' => 'church-website-kit.zip'],
    12 => ['name' => 'Restaurant POS Kit', 'file' => 'restaurant-pos-kit.zip'],
    13 => ['name' => 'School Management System', 'file' => 'school-mgmt-system.zip'],
    14 => ['name' => 'Real Estate Property Kit', 'file' => 'real-estate-kit.zip'],
    15 => ['name' => 'E-commerce Starter Kit', 'file' => 'ecommerce-starter-kit.zip'],
    16 => ['name' => 'WordPress Starter Kit', 'file' => 'wordpress-starter-kit.zip'],
    17 => ['name' => 'Website Audit Kit', 'file' => 'website-audit-kit.zip'],
];

if ($pid < 1 || $pid > 17) {
    echo "<h2>Products</h2>";
    foreach ($products as $id => $p) {
        echo "<a href='?pid=$id'>$id: {$p['name']}</a><br>";
    }
    exit;
}

$p = $products[$pid];
$seq_name = "Post-Purchase - " . $p['name'];

$res = $conn->query("SELECT id FROM email_sequences WHERE name = '$seq_name'");
if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $seq_id = $row['id'];
} else {
    $conn->query("INSERT INTO email_sequences (name, description, is_active) VALUES ('$seq_name', 'Post-purchase', 1)");
    $seq_id = $conn->insert_id;
}

$conn->query("DELETE FROM sequence_steps WHERE sequence_id = $seq_id");

$steps = [
    ["Thank you for your purchase! 🎉", "<h2>Your {$p['name']} is Ready!</h2><p><a href='https://joala.com.ng/downloads/{$p['file']}'>📥 Download Files</a></p>", 0, 1],
    ["Quick guide to get started...", "<h2>Let's get started!</h2><p>1. Download files<br>2. Review README<br>3. Implement first strategy</p>", 48, 2],
    ["Need help? Let's chat 💬", "<h2>Questions?</h2><p>Reply if you need help!</p>", 120, 3],
];

foreach ($steps as $s) {
    $conn->query("INSERT INTO sequence_steps (sequence_id, subject, content, delay_hours, step_order) 
              VALUES ($seq_id, '".$s[0]."', '".$s[1]."', ".$s[2].", ".$s[3].")");
}

$conn->query("UPDATE funnels SET welcome_sequence_id = $seq_id WHERE product_id = $pid");

echo "✓ Done: $seq_name (Seq ID: $seq_id)";

$conn->close();