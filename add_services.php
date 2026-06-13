<h1>Adding WordPress and Shopify Services...</h1>
<?php
error_reporting(0);

$configFile = __DIR__ . '/bootstrap/cache/config.php';
if (!file_exists($configFile)) {
    die("Config not found");
}

$config = include($configFile);
$c = $config['connections']['mysql'];
$conn = new mysqli($c['host'], $c['username'], $c['password'], $c['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if services table exists
$result = $conn->query("SHOW TABLES LIKE 'services'");
if ($result->num_rows == 0) {
    echo "Creating services table...";
    $conn->query("CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(255),
        features VARCHAR(255),
        pricing VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

// Check if WordPress exists
$result = $conn->query("SELECT id FROM services WHERE slug = 'wordpress-development'");
if ($result->num_rows == 0) {
    $conn->query("INSERT INTO services (title, slug, description, icon, features, pricing, is_active) VALUES (
        'WordPress Development', 'wordpress-development', 'Custom WordPress themes, plugins, and complete website solutions.', 'fab fa-wordpress', 'Custom Themes,Plugin Development,WooCommerce', 'Starting from ₦30,000', 1
    )");
    echo "<p>✓ Added WordPress Development</p>";
} else {
    echo "<p>WordPress already exists</p>";
}

// Check if Shopify exists
$result = $conn->query("SELECT id FROM services WHERE slug = 'shopify-development'");
if ($result->num_rows == 0) {
    $conn->query("INSERT INTO services (title, slug, description, icon, features, pricing, is_active) VALUES (
        'Shopify Development', 'shopify-development', 'Professional Shopify stores, theme customization, and e-commerce solutions.', 'fab fa-shopify', 'Store Setup,Theme Customization,App Integration', 'Starting from ₦40,000', 1
    )");
    echo "<p>✓ Added Shopify Development</p>";
} else {
    echo "<p>Shopify already exists</p>";
}

echo "<h2>Done! <a href='/services'>View Services</a></h2>";