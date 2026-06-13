<?php
$conn = new mysqli("localhost", "joalacom_joala", "J0ala@2024!", "joalacom_joala");

echo "<h2>WordPress Starter Kit Funnel (ID 2) - Full Analysis</h2>";

// Get funnel details
$funnel = $conn->query("SELECT * FROM funnels WHERE id = 2")->fetch_assoc();
echo "<h3>Funnel Configuration</h3>";
echo "<pre>";
print_r($funnel);
echo "</pre>";

// Get stages
echo "<h3>Funnel Stages</h3>";
$stages = $conn->query("SELECT * FROM funnel_stages WHERE funnel_id = 2 ORDER BY `order`");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Order</th><th>Content</th></tr>";
while ($row = $stages->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['type']}</td>";
    echo "<td>{$row['order']}</td>";
    echo "<td>" . substr($row['content'], 0, 200) . "...</td>";
    echo "</tr>";
}
echo "</table>";

// Get landing page linked to this funnel
echo "<h3>Landing Pages Linked to This Funnel</h3>";
$lps = $conn->query("SELECT * FROM landing_pages WHERE funnel_id = 2");
while ($row = $lps->fetch_assoc()) {
    echo "<p><strong>Slug:</strong> {$row['slug']} | <strong>Title:</strong> {$row['title']} | <strong>Active:</strong> {$row['is_active']}</p>";
}

// Check all WordPress related funnels
echo "<h3>All WordPress Funnels (IDs 2,3,4,21)</h3>";
$wpFunnels = $conn->query("SELECT id, name, funnel_type, welcome_sequence_id, followup_sequence_id, is_active FROM funnels WHERE name LIKE '%WordPress%' OR id IN (2,3,4,21)");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Welcome Seq</th><th>Follow-up Seq</th><th>Active</th></tr>";
while ($row = $wpFunnels->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['funnel_type']}</td>";
    echo "<td>{$row['welcome_sequence_id']}</td>";
    echo "<td>{$row['followup_sequence_id']}</td>";
    echo "<td>{$row['is_active']}</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();