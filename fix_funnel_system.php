<?php
/**
 * Funnel System Fix Migration
 * Run this to add missing columns to the funnels table
 */

$conn = new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Funnel System Fixes</h2>";

$fixes = [];

// 1. Check and add missing columns to funnels table
$columnsToAdd = [
    'score_per_click' => 'INT DEFAULT 20 COMMENT "Points per link click"',
    'hot_lead_tag' => 'VARCHAR(100) DEFAULT NULL COMMENT "Tag name to apply when lead is hot"',
];

foreach ($columnsToAdd as $column => $definition) {
    $check = $conn->query("DESCRIBE funnels");
    $exists = false;
    while ($row = $check->fetch_assoc()) {
        if ($row['Field'] === $column) {
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $sql = "ALTER TABLE funnels ADD COLUMN $column $definition";
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

// 2. Check and add missing columns to funnel_leads table
$leadColumnsToAdd = [
    'times_visited' => 'INT DEFAULT 0 COMMENT "Number of page visits"',
    'pages_viewed' => 'INT DEFAULT 0 COMMENT "Number of unique pages viewed"',
    'clicks_count' => 'INT DEFAULT 0 COMMENT "Number of link clicks"',
    'is_tagged_hot' => 'TINYINT(1) DEFAULT 0 COMMENT "Flag for hot leads"',
];

foreach ($leadColumnsToAdd as $column => $definition) {
    $check = $conn->query("DESCRIBE funnel_leads");
    $exists = false;
    while ($row = $check->fetch_assoc()) {
        if ($row['Field'] === $column) {
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $sql = "ALTER TABLE funnel_leads ADD COLUMN $column $definition";
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

// 3. Check funnel_stages for new fields
$stageColumnsToAdd = [
    'condition_type' => 'VARCHAR(50) DEFAULT "none" COMMENT "none|email_opens|clicks|score_above"',
    'condition_value' => 'JSON DEFAULT NULL COMMENT "Condition parameters"',
    'is_skippable' => 'TINYINT(1) DEFAULT 1 COMMENT "Can skip this stage"',
];

foreach ($stageColumnsToAdd as $column => $definition) {
    $check = $conn->query("DESCRIBE funnel_stages");
    $exists = false;
    while ($row = $check->fetch_assoc()) {
        if ($row['Field'] === $column) {
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $sql = "ALTER TABLE funnel_stages ADD COLUMN $column $definition";
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

// 4. Set default scoring values for existing funnels that don't have them
$conn->query("UPDATE funnels SET score_per_page = 5 WHERE (score_per_page IS NULL OR score_per_page = 0)");
$conn->query("UPDATE funnels SET score_per_email = 10 WHERE (score_per_email IS NULL OR score_per_email = 0)");
$conn->query("UPDATE funnels SET score_per_checkout = 50 WHERE (score_per_checkout IS NULL OR score_per_checkout = 0)");
$conn->query("UPDATE funnels SET score_per_click = 20 WHERE (score_per_click IS NULL OR score_per_click = 0)");
$conn->query("UPDATE funnels SET score_hot_threshold = 100 WHERE (score_hot_threshold IS NULL OR score_hot_threshold = 0)");
echo "<p style='color:green'>✓ Updated default scoring values</p>";

if (count($fixes) > 0) {
    echo "<h3>Summary</h3>";
    echo "<p>Fixed " . count($fixes) . " issues:</p>";
    echo "<ul>";
    foreach ($fixes as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ul>";
} else {
    echo "<h3>Summary</h3>";
    echo "<p>No new fixes needed - system was already up to date!</p>";
}

echo "<h3>Current Funnel Settings</h3>";
$result = $conn->query("SELECT id, name, score_per_page, score_per_email, score_per_click, score_per_checkout, score_hot_threshold FROM funnels WHERE is_active = 1 LIMIT 10");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Per Page</th><th>Per Email</th><th>Per Click</th><th>Per Sale</th><th>Hot Threshold</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['score_per_page']}</td>";
    echo "<td>{$row['score_per_email']}</td>";
    echo "<td>{$row['score_per_click']}</td>";
    echo "<td>{$row['score_per_checkout']}</td>";
    echo "<td>{$row['score_hot_threshold']}</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();

echo "<h2 style='color:green; margin-top: 20px;'>Migration Complete!</h2>";