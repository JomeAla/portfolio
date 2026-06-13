<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables with 'customer':\n";
    foreach($tables as $t) {
        if(stripos($t, 'customer') !== false) echo "- $t\n";
    }
    
    // Check if customer_accounts has data
    $count = $pdo->query("SELECT COUNT(*) FROM customer_accounts")->fetchColumn();
    echo "\nCustomer accounts: $count\n";
    
    if($count == 0) {
        // Create test customer
        $hash = password_hash('test123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO customer_accounts (email, password, name, created_at) VALUES ('test@test.com', '$hash', 'Test User', NOW())");
        echo "Created test user: test@test.com / test123\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}