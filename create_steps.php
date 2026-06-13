<?php
$conn = @new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>Creating Sequence Steps</h2>";

// Sequence 21 = Post-Purchase - WordPress Starter Kit
echo "<h3>Sequence 21 (Post-Purchase - WordPress Starter Kit)</h3>";
$seq21_steps = [
    ["Your WordPress Starter Kit is ready!", "<h2>Your download is ready!</h2><p>Download your WordPress Starter Kit: <a href='https://joala.com.ng/downloads/wordpress-starter-kit.zip'>Download Here</a></p>", 0, 1],
    ["Next steps with your WordPress site...", "<h2>Let's get started!</h2><p>1. Download and unzip the kit<br>2. Follow the checklist<br>3. Build your site</p>", 2, 2],
    ["Need help? Let's chat", "<h2>Questions?</h2><p>Reply if you need help with your WordPress site!</p>", 5, 3],
    ["Want a professional website?",
    "<h2>Get a Custom Website</h2><p>Need a professional WordPress website built for you?</p><p><a href='https://joala.com.ng/contact'>Book a Call →</a></p>",
    7, 4]
];

foreach ($seq21_steps as $s) {
    $conn->query("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order) VALUES (21, '".$s[0]."', '".$s[1]."', ".$s[2].", ".$s[3].")");
    echo "Added: {$s[0]}<br>";
}

// Sequence 22 = Lead Magnet - Email Checklist
echo "<h3>Sequence 22 (Lead Magnet - Email Checklist)</h3>";
$seq22_steps = [
    ["Here's your Email Marketing Checklist! 📧", "<h2>Your download is ready!</h2><p>Download your checklist: <a href='https://joala.com.ng/downloads/email-marketing-checklist.pdf'>Download Here</a></p>", 0, 1],
    ["Quick question about your checklist...", "<h2>How's it going?</h2><p>Which tip are you implementing first? Reply and let me know!</p>", 3, 2],
    ["Want more email templates? 📝",
    "<h2>Get More Templates</h2><p>Our Email Templates Pack has 24 proven templates!</p><p><a href='https://joala.com.ng/store/email-sequence-templates-pack'>Get the Pack →</a></p>",
    7, 3],
    ["Last chance - Templates offer",
    "<h2>Final reminder</h2><p>This offer expires soon!</p><p><a href='https://joala.com.ng/store/email-sequence-templates-pack'>Get Access Now</a></p>",
    10, 4]
];

foreach ($seq22_steps as $s) {
    $conn->query("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order) VALUES (22, '".$s[0]."', '".$s[1]."', ".$s[2].", ".$s[3].")");
    echo "Added: {$s[0]}<br>";
}

// Also fix Sequence 16 (Lead Magnet - WordPress Starter kit original - ID 21 was wrong)
echo "<h3>Also creating for other sequences...</h3>";

// Create/update steps for other important sequences
$other_seqs = [
    2 => ["Post-Purchase - Email Templates Pack"],
    3 => ["Post-Purchase - Premium Bundle"],
    4 => ["Post-Purchase - Done-For-You Service"],
];

foreach ($other_seqs as $sid => $name) {
    $check = $conn->query("SELECT COUNT(*) cnt FROM sequence_steps WHERE sequence_id = $sid")->fetch_assoc();
    if ($check['cnt'] == 0) {
        $steps = [
            ["Thank you for your purchase!", "<h2>Your files are ready!</h2><p>Download from your account.</p>", 0, 1],
            ["Quick start guide...", "<h2>Let's get started!</h2><p>Here are the first steps...</p>", 2, 2],
            ["Need help?", "<h2>Questions?</h2><p>Reply if you need help!</p>", 5, 3]
        ];
        foreach ($steps as $s) {
            $conn->query("INSERT INTO sequence_steps (sequence_id, subject, body, delay_days, step_order) VALUES ($sid, '".$s[0]."', '".$s[1]."', ".$s[2].", ".$s[3].")");
        }
        echo "Created steps for Seq $sid: $name<br>";
    }
}

echo "<br><h2>Steps created! Now queuing emails...</h2>";

// Now queue emails for all leads
$leads = $conn->query("SELECT id, sequence_id FROM leads WHERE sequence_id IS NOT NULL");
while ($lead = $leads->fetch_assoc()) {
    $lid = $lead['id'];
    $sid = $lead['sequence_id'];
    
    $steps = $conn->query("SELECT id, delay_days FROM sequence_steps WHERE sequence_id = $sid ORDER BY step_order");
    while ($step = $steps->fetch_assoc()) {
        $delay = is_numeric($step['delay_days']) ? $step['delay_days'] : 0;
        $scheduled = date('Y-m-d H:i:s', time() + ($delay * 3600 * 24));
        $conn->query("INSERT INTO email_queue (lead_id, sequence_step_id, scheduled_send_time, status) VALUES ($lid, {$step['id']}, '$scheduled', 'pending')");
    }
    echo "Queued $lid → seq $sid<br>";
}

$total = $conn->query("SELECT COUNT(*) cnt FROM email_queue")->fetch_assoc();
echo "<br><h2 style='color:green'>✓ Total queued: {$total['cnt']}</h2>";

$conn->close();