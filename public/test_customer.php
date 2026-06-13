<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdo = db_pdo();
echo "Testing customer login...\n";
$stmt = $pdo->query("SELECT id, email, name FROM customer_accounts LIMIT 5");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
if(empty($customers)) {
    echo "No customers found. Creating test customer...\n";
    $hash = password_hash('test123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO customer_accounts (email, password, name, created_at) VALUES ('test@test.com', '$hash', 'Test User', NOW())");
    echo "Created test@test.com with password test123\n";
} else {
    echo "Found customers:\n";
    print_r($customers);
}