<?php
/**
 * Setup Post-Purchase Email Sequences
 * Creates/updates sequences with download links for all products
 */

error_reporting(1);

// Database config
$host = 'localhost';
$db = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Setting Up Post-Purchase Sequences for All Products</h2><br>";

// Product data - map product ID to product details
$products = [
    1 => ['name' => 'Email Sequence Templates Pack', 'slug' => 'email-sequence-templates-pack', 'file' => 'email-templates-pack.zip'],
    2 => ['name' => 'Email Marketing Premium Bundle', 'slug' => 'email-marketing-premium-bundle', 'file' => 'premium-bundle.zip'],
    3 => ['name' => 'Done-For-You Email Automation', 'slug' => 'done-for-you-email-automation', 'file' => 'dfy-automation.zip'],
    4 => ['name' => 'WhatsApp Marketing Bundle', 'slug' => 'whatsapp-marketing-bundle', 'file' => 'whatsapp-bundle.zip'],
    5 => ['name' => 'Course Creator Kit', 'slug' => 'course-creator-kit', 'file' => 'course-creator-kit.zip'],
    6 => ['name' => 'Local Business Digital Kit', 'slug' => 'local-business-digital-kit', 'file' => 'local-business-kit.zip'],
    7 => ['name' => 'SaaS Starter Kit', 'slug' => 'saas-starter-kit', 'file' => 'saas-starter-kit.zip'],
    8 => ['name' => 'Freelancer Toolkit', 'slug' => 'freelancer-toolkit', 'file' => 'freelancer-toolkit.zip'],
    9 => ['name' => 'Instagram Growth System', 'slug' => 'instagram-growth-system', 'file' => 'instagram-growth.zip'],
    10 => ['name' => 'Nigerian Business Digital Kit', 'slug' => 'nigerian-business-digital-kit', 'file' => 'nigeria-business-kit.zip'],
    11 => ['name' => 'Church & Organization Website Kit', 'slug' => 'church-organization-website-kit', 'file' => 'church-website-kit.zip'],
    12 => ['name' => 'Restaurant POS Kit', 'slug' => 'restaurant-pos-kit', 'file' => 'restaurant-pos-kit.zip'],
    13 => ['name' => 'School Management System', 'slug' => 'school-management-system', 'file' => 'school-mgmt-system.zip'],
    14 => ['name' => 'Real Estate Property Kit', 'slug' => 'real-estate-property-kit', 'file' => 'real-estate-kit.zip'],
    15 => ['name' => 'E-commerce Starter Kit', 'slug' => 'e-commerce-starter-kit', 'file' => 'ecommerce-starter-kit.zip'],
    16 => ['name' => 'WordPress Starter Kit', 'slug' => 'wordpress-starter-kit', 'file' => 'wordpress-starter-kit.zip'],
    17 => ['name' => 'Website Audit Kit', 'slug' => 'website-audit-kit', 'file' => 'website-audit-kit.zip'],
];

// Sequence template for each product
foreach ($products as $product_id => $product) {
    $seq_name = "Post-Purchase - " . $product['name'];
    $funnel_slug = strtolower(str_replace(' ', '-', $product['name'])) . '-launch';
    
    // Check if sequence exists
    $res = $conn->query("SELECT id FROM email_sequences WHERE name = '$seq_name'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $seq_id = $row['id'];
        echo "Updating sequence $seq_id: $seq_name<br>";
        // Delete old steps
        $conn->query("DELETE FROM sequence_steps WHERE sequence_id = $seq_id");
    } else {
        $conn->query("INSERT INTO email_sequences (name, description, is_active) VALUES ('$seq_name', 'Post-purchase sequence for {$product['name']}', 1)");
        $seq_id = $conn->insert_id;
        echo "Created sequence $seq_id: $seq_name<br>";
    }
    
    // Create steps with download links
    $steps = [
        [
            'subject' => "Thank you for your purchase! 🎉",
            'content' => "<h2>Your {$product['name']} is Ready!</h2>
<p>Thank you for your purchase! Access your files below:</p>
<p><a href='https://joala.com.ng/downloads/{$product['file']}' style='background:#10b981;color:white;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;'>📥 Download Your Files</a></p>
<p>Or copy this link: <code>https://joala.com.ng/downloads/{$product['file']}</code></p>
<p><strong>Your download link expires in 7 days.</strong></p>",
            'delay_hours' => 0,
            'order' => 1
        ],
        [
            'subject' => "Quick guide to get started...",
            'content' => "<h2>Let's get started! 🚀</h2>
<p>Here are the first steps to get the most out of your {$product['name']}:</p>
<ol>
<li>Download and unzip your files</li>
<li>Review the included README guide</li>
<li>Implement the first strategy</li>
</ol>
<p>Questions? Reply and let me know!</p>",
            'delay_hours' => 48,
            'order' => 2
        ],
        [
            'subject' => "Need help? Let's chat 💬",
            'content' => "<h2>Questions about your purchase?</h2>
<p>I wanted to check in - are you enjoying your {$product['name']}?</p>
<p>If you have ANY questions, just reply to this email. I'm here to help!</p>
<p>Best,<br>JoAla Team</p>",
            'delay_hours' => 120,
            'order' => 3
        ],
        [
            'subject' => "Want to upgrade? ⬆️",
            'content' => "<h2>Get More with Premium Support</h2>
<p>Since you purchased {$product['name']}, you might be interested in our premium services:</p>
<ul>
<li>1-on-1 coaching calls</li>
<li>Custom implementation</li>
<li>Priority support</li>
</ul>
<p><a href='https://joala.com.ng/contact'>Book a Call →</a></p>",
            'delay_hours' => 240,
            'order' => 4
        ]
    ];
    
    foreach ($steps as $step) {
        $subject = mysqli_real_escape_string($conn, $step['subject']);
        $content = mysqli_real_escape_string($conn, $step['content']);
        $delay = $step['delay_hours'];
        $order = $step['order'];
        
        $sql = "INSERT INTO sequence_steps (sequence_id, subject, content, delay_hours, step_order) 
               VALUES ($seq_id, '$subject', '$content', $delay, $order)";
        $conn->query($sql);
    }
    
    // Update funnel to use this sequence
    $res = $conn->query("SELECT id FROM funnels WHERE product_id = $product_id AND is_active = 1");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $funnel_id = $row['id'];
        $conn->query("UPDATE funnels SET welcome_sequence_id = $seq_id WHERE id = $funnel_id");
        echo "Updated funnel $funnel_id with sequence $seq_id<br>";
    }
    
    echo "<br>";
}

echo "<h2 style='color:green'>✓ Complete!</h2>";
echo "<p>All products now have post-purchase email sequences with download links.</p>";

$conn->close();