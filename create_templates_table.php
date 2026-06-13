<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

echo "<h1>Create Email Templates Table</h1>";

$result = $conn->query("SHOW TABLES LIKE 'email_templates'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE email_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        category VARCHAR(100),
        created_at DATETIME,
        updated_at DATETIME
    )");
    echo "✅ Created email_templates table<br>";
} else {
    echo "✓ Table already exists<br>";
}

$defaultTemplates = [
    [
        'name' => 'Welcome Email',
        'subject' => 'Welcome to {{company_name}}!',
        'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
<h1 style="color: #2563eb;">Welcome, {{name}}!</h1>
<p>Thank you for joining us. We\'re excited to have you on board!</p>
<p>Get started by exploring our services:</p>
<ul>
<li>Web Development</li>
<li>Mobile Apps</li>
<li>Digital Marketing</li>
</ul>
<p>Best regards,<br>The {{company_name}} Team</p>
</div>',
        'description' => 'Default welcome email for new leads',
        'is_active' => 1,
        'category' => 'onboarding'
    ],
    [
        'name' => 'Follow Up',
        'subject' => 'Following up - Any questions?',
        'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
<h2>Hi {{name}},</h2>
<p>I wanted to follow up on your recent inquiry. Hope you\'re doing well!</p>
<p>Do you have any questions about our services? We\'re here to help.</p>
<p>Feel free to reply to this email or call us directly.</p>
<p>Best,<br>The Team</p>
</div>',
        'description' => 'Day 1 follow up email',
        'is_active' => 1,
        'category' => 'followup'
    ],
    [
        'name' => 'Newsletter',
        'subject' => '{{company_name}} Newsletter - {{month}} Updates',
        'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
<h2>Hello {{name}},</h2>
<p>Here\'s what\'s new at {{company_name}} this month:</p>
<div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 15px 0;">
<p><strong>Our Latest Projects</strong></p>
<p>We\'ve been busy building amazing things for our clients!</p>
</div>
<p><a href="{{unsubscribe_url}}" style="color: #666; font-size: 12px;">Unsubscribe</a></p>
</div>',
        'description' => 'Monthly newsletter template',
        'is_active' => 1,
        'category' => 'newsletter'
    ],
    [
        'name' => 'Special Offer',
        'subject' => 'Special Offer Just For You, {{name}}!',
        'body' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
<h1 style="color: #dc2626;">Special Offer! 🎉</h1>
<p>Hi {{name}},</p>
<p>Don\'t miss out on this limited-time offer!</p>
<div style="background: #fef3c7; border: 2px dashed #f59e0b; padding: 20px; margin: 20px 0; text-align: center;">
<p><strong>Get 20% Off Our Services</strong></p>
<p>Use code: <strong>SAVE20</strong></p>
</div>
<p>Offer expires in 7 days!</p>
<p>Best,<br>{{company_name}}</p>
</div>',
        'description' => 'Promotional offer email',
        'is_active' => 1,
        'category' => 'promotion'
    ]
];

foreach ($defaultTemplates as $t) {
    $check = $conn->query("SELECT id FROM email_templates WHERE name = '{$t['name']}'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO email_templates (name, subject, body, description, is_active, category, created_at, updated_at) 
            VALUES ('{$t['name']}', '{$t['subject']}', '{$t['body']}', '{$t['description']}', {$t['is_active']}, '{$t['category']}', NOW(), NOW())");
        echo "✅ Added template: {$t['name']}<br>";
    } else {
        echo "✓ Already exists: {$t['name']}<br>";
    }
}

echo "<br><h2>Done!</h2>";
echo "<p><a href='/email_templates.php'>Go to Templates</a></p>";
