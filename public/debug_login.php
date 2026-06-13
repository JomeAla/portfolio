<?php
// Debug login issue
$email = 'admin@joala.com.ng';
$password = 'password123';

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing login for: $email\n";

// Use direct PDO like the controller does
$host = config('database.connections.mysql.host');
$dbname = config('database.connections.mysql.database');
$username = config('database.connections.mysql.username');
$pass = config('database.connections.mysql.password');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get customer
    $stmt = $pdo->prepare("SELECT * FROM customer_accounts WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        echo "ERROR: No customer found with email $email\n";
    } else {
        echo "Customer found: " . $customer['email'] . "\n";
        echo "Stored hash: " . substr($customer['password'], 0, 30) . "...\n";
        
        $verify = password_verify($password, $customer['password']);
        echo "Password verify result: " . ($verify ? 'TRUE' : 'FALSE') . "\n";
        
        if (!$verify) {
            // Generate new hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            echo "New hash: $newHash\n";
            
            // Update password
            $pdo->exec("UPDATE customer_accounts SET password = '$newHash' WHERE id = {$customer['id']}");
            echo "Password updated with new hash\n";
            
            // Test again
            $stmt = $pdo->prepare("SELECT * FROM customer_accounts WHERE email = ?");
            $stmt->execute([$email]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            $verify = password_verify($password, $customer['password']);
            echo "After update - Password verify result: " . ($verify ? 'TRUE' : 'FALSE') . "\n";
        }
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}