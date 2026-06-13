<?php
// Create Email Broadcast Tables
header('Content-Type: text/plain');

$conn = new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Create email_lists table
$lists_sql = "CREATE TABLE IF NOT EXISTS email_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    subscriber_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($lists_sql)) {
    echo "[OK] email_lists table created\n";
} else {
    echo "[ERROR] " . $conn->error . "\n";
}

// Create email_campaigns (broadcasts) table
$campaigns_sql = "CREATE TABLE IF NOT EXISTS email_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT,
    list_id INT,
    segment_id INT,
    status ENUM('draft', 'scheduled', 'sending', 'sent', 'cancelled') DEFAULT 'draft',
    scheduled_at DATETIME,
    sent_at DATETIME,
    total_recipients INT DEFAULT 0,
    total_sent INT DEFAULT 0,
    total_opens INT DEFAULT 0,
    total_clicks INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (list_id) REFERENCES email_lists(id)
)";

if ($conn->query($campaigns_sql)) {
    echo "[OK] email_campaigns table created\n";
} else {
    echo "[ERROR] " . $conn->error . "\n";
}

// Create email_campaign_recipients table
$recipients_sql = "CREATE TABLE IF NOT EXISTS email_campaign_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'sent', 'opened', 'clicked', 'failed') DEFAULT 'pending',
    sent_at DATETIME,
    opened_at DATETIME,
    clicked_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE
)";

if ($conn->query($recipients_sql)) {
    echo "[OK] email_campaign_recipients table created\n";
} else {
    echo "[ERROR] " . $conn->error . "\n";
}

echo "\n[OK] Email Broadcast system ready!";

$conn->close();