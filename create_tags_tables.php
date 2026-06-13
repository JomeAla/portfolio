<?php
error_reporting(0);

$conn = @new mysqli('localhost', 'joalacom_joala', 'J0ala@2024!', 'joalacom_joala');
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES LIKE 'tags'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        color VARCHAR(20) DEFAULT '#6366f1',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Created tags table<br>";
} else {
    echo "✓ tags table already exists<br>";
}

$result = $conn->query("SHOW TABLES LIKE 'lead_tags'");
if ($result->num_rows == 0) {
    $conn->query("CREATE TABLE lead_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lead_id INT NOT NULL,
        tag_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_lead_tag (lead_id, tag_id),
        FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
    )");
    echo "✅ Created lead_tags pivot table<br>";
} else {
    echo "✓ lead_tags table already exists<br>";
}

$result = $conn->query("SHOW COLUMNS FROM leads LIKE 'score'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE leads ADD COLUMN score INT DEFAULT 0 AFTER source");
    echo "✅ Added 'score' column to leads<br>";
} else {
    echo "✓ 'score' column already exists<br>";
}

$defaultTags = [
    ['name' => 'Hot Lead', 'slug' => 'hot-lead', 'color' => '#ef4444'],
    ['name' => 'Cold Lead', 'slug' => 'cold-lead', 'color' => '#3b82f6'],
    ['name' => 'Nigerian', 'slug' => 'nigerian', 'color' => '#22c55e'],
    ['name' => 'International', 'slug' => 'international', 'color' => '#8b5cf6'],
    ['name' => 'Ecommerce', 'slug' => 'ecommerce', 'color' => '#f59e0b'],
    ['name' => 'SaaS', 'slug' => 'saas', 'color' => '#06b6d4'],
    ['name' => 'Portfolio', 'slug' => 'portfolio', 'color' => '#ec4899'],
    ['name' => 'Corporate', 'slug' => 'corporate', 'color' => '#64748b'],
];

foreach ($defaultTags as $tag) {
    $stmt = $conn->prepare("INSERT IGNORE INTO tags (name, slug, color) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $tag['name'], $tag['slug'], $tag['color']);
    $stmt->execute();
}

echo "✅ Added default tags<br>";
echo "<h3>Done!</h3>";
