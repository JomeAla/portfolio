<?php
/**
 * Cart Abandonment Recovery - Database Migration
 * Run this to add cart tracking columns to orders table
 */

$conn = new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Cart Abandonment Database Setup</h2>";

$columnsToAdd = [
    'cart_started_at' => 'DATETIME DEFAULT NULL COMMENT "When cart was initiated"',
    'cart_abandoned_at' => 'DATETIME DEFAULT NULL COMMENT "When cart was marked abandoned"',
    'cart_recovered_at' => 'DATETIME DEFAULT NULL COMMENT "When cart was recovered (purchase made)"',
    'is_cart_abandoned' => 'TINYINT(1) DEFAULT 0 COMMENT "Flag: cart is abandoned"',
    'checkout_started_at' => 'DATETIME DEFAULT NULL COMMENT "When checkout was initiated"',
    'checkout_abandoned_at' => 'DATETIME DEFAULT NULL COMMENT "When checkout was marked abandoned"',
];

$fixes = [];

foreach ($columnsToAdd as $column => $definition) {
    $check = $conn->query("DESCRIBE orders");
    $exists = false;
    while ($row = $check->fetch_assoc()) {
        if ($row['Field'] === $column) {
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $sql = "ALTER TABLE orders ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "<p style='color:green'>✓ Added column: $column</p>";
            $fixes[] = $column;
        } else {
            echo "<p style='color:red'>✗ Failed to add $column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:gray'>- Column already exists: $column</p>";
    }
}

// Create Cart Abandonment Email Sequence
echo "<h3>Creating Cart Abandonment Email Sequence</h3>";

// Check if sequence exists
$seqCheck = $conn->query("SELECT id FROM email_sequences WHERE trigger_type = 'cart_abandonment'");
if ($seqCheck->num_rows == 0) {
    $conn->query("INSERT INTO email_sequences (name, description, trigger_type, is_active, created_at, updated_at) 
        VALUES ('Cart Abandonment Recovery', 'Emails sent when carts are abandoned', 'cart_abandonment', 1, NOW(), NOW())");
    $seqId = $conn->insert_id;
    echo "<p style='color:green'>✓ Created Cart Abandonment sequence (ID: $seqId)</p>";
    
    // Add sequence steps
    $steps = [
        ['step_order' => 1, 'delay_days' => 0, 'subject' => 'Forgot something?', 'body' => "Hi {{name}},\n\nI noticed you started to get the {{product_name}} but didn't complete your purchase.\n\nYour order is still waiting for you!\n\nGet it now: {{checkout_link}}\n\nBest,\nJome"],
        ['step_order' => 2, 'delay_days' => 1, 'subject' => 'Still thinking about it?', 'body' => "Hi {{name}},\n\nJust checking in - are you still interested in {{product_name}}?\n\nIf you have questions, reply to this email.\n\nGet it now: {{checkout_link}}\n\nBest,\nJome"],
        ['step_order' => 3, 'delay_days' => 3, 'subject' => 'Last chance - discount ending soon!', 'body' => "Hi {{name}},\n\nThis is your final reminder! Your {{product_name}} discount expires in 24 hours.\n\nDon't miss out on this offer.\n\nGet it now: {{checkout_link}}\n\nBest,\nJome"],
    ];
    
    foreach ($steps as $step) {
        $conn->query("INSERT INTO sequence_steps (sequence_id, step_order, delay_days, subject, body, created_at, updated_at) 
            VALUES ($seqId, {$step['step_order']}, {$step['delay_days']}, '{$step['subject']}', '{$step['body']}', NOW(), NOW())");
    }
    echo "<p style='color:green'>✓ Added " . count($steps) . " email steps</p>";
} else {
    $row = $seqCheck->fetch_assoc();
    echo "<p style='color:gray'>- Cart Abandonment sequence already exists (ID: {$row['id']})</p>";
}

// Update existing pending orders that should be tracked
$conn->query("UPDATE orders SET is_cart_abandoned = 0 WHERE payment_status = 'pending' AND is_cart_abandoned IS NULL");
$conn->query("UPDATE orders SET cart_started_at = created_at WHERE payment_status = 'pending' AND cart_started_at IS NULL");
echo "<p style='color:green'>✓ Updated existing pending orders</p>";

// Show current status
echo "<h3>Current Cart Tracking Status</h3>";
$result = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN is_cart_abandoned = 1 THEN 1 ELSE 0 END) as abandoned
FROM orders");
$row = $result->fetch_assoc();
echo "<p>Total Orders: {$row['total']} | Pending: {$row['pending']} | Abandoned: {$row['abandoned']}</p>";

echo "<h3>Next Step</h3>";
echo "<p>Create a cron job to detect and mark abandoned carts:</p>";
echo "<code>0 * * * * curl https://joala.com.ng/process-cart-abandonment.php</code>";

$conn->close();

echo "<h2 style='color:green; margin-top:20px'>Cart Abandonment Setup Complete!</h2>";