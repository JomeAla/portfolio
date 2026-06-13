<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES LIKE 'campaigns'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        sequence_ids JSON,
        status VARCHAR(50) DEFAULT 'draft',
        start_date DATETIME,
        end_date DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created campaigns table<br>";
} else {
    echo "✓ campaigns table already exists<br>";
}

$result = $conn->query("SHOW TABLES LIKE 'campaign_leads'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE campaign_leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        lead_id INT NOT NULL,
        enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_campaign_lead (campaign_id, lead_id)
    )");
    echo "✅ Created campaign_leads table<br>";
} else {
    echo "✓ campaign_leads table already exists<br>";
}

echo "<h3>Done!</h3>";