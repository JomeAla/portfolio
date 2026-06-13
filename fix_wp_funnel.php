<?php
$host = 'localhost'; $dbname = 'joalacom_joala'; $user = 'joalacom_joala'; $pass = 'J0ala@2024!';
try { $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass); } catch (PDOException $e) { die("DB Error: " . $e->getMessage()); }

echo "<h2>Fixing WordPress Starter Kit Funnel</h2>";

// Delete duplicate WordPress funnels (keep ID 2)
echo "<h3>Step 1: Deleting duplicate funnels...</h3>";
$stmt = $pdo->prepare("DELETE FROM funnel_stages WHERE funnel_id IN (3, 4, 21)");
$stmt->execute();
echo "<p>Deleted stages for funnels 3, 4, 21</p>";

$stmt = $pdo->prepare("DELETE FROM funnels WHERE id IN (3, 4, 21)");
$stmt->execute();
echo "<p>Deleted funnels 3, 4, 21</p>";

// Check existing stages for funnel 2
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM funnel_stages WHERE funnel_id = 2");
$row = $stmt->fetch();
echo "<h3>Step 2: Checking stages for Funnel 2...</h3>";
echo "<p>Current stages: " . $row['cnt'] . "</p>";

// Add stages if missing
if ($row['cnt'] < 3) {
    echo "<h3>Step 3: Adding missing stages...</h3>";
    
    // Stage 1: Landing Page
    $stmt = $pdo->prepare("INSERT IGNORE INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at) VALUES (2, 'Landing Page', 'landing_page', 1, '{\"page_slug\":\"free-wordpress-starter-kit\"}', NOW(), NOW())");
    $stmt->execute();
    echo "<p>Added Stage 1: Landing Page</p>";
    
    // Stage 2: Download Page
    $stmt = $pdo->prepare("INSERT IGNORE INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at) VALUES (2, 'Download Page', 'content', 2, '{\"url\":\"/downloads/wordpress-starter-kit.pdf\"}', NOW(), NOW())");
    $stmt->execute();
    echo "<p>Added Stage 2: Download Page</p>";
    
    // Stage 3: Thank You
    $stmt = $pdo->prepare("INSERT IGNORE INTO funnel_stages (funnel_id, name, type, `order`, content, created_at, updated_at) VALUES (2, 'Thank You', 'thankyou', 3, '{\"message\":\"Thanks for downloading!\"}', NOW(), NOW())");
    $stmt->execute();
    echo "<p>Added Stage 3: Thank You</p>";
} else {
    echo "<p>Stages already exist!</p>";
}

// Verify
echo "<h3>Verification:</h3>";
$stmt = $pdo->query("SELECT * FROM funnel_stages WHERE funnel_id = 2 ORDER BY `order`");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Order</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['type']}</td><td>{$row['order']}</td></tr>";
}
echo "</table>";

echo "<h2 style='color:green'>Done! WordPress Funnel Fixed!</h2>";