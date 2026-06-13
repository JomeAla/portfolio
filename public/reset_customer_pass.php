<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$hash = password_hash('password123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE customer_accounts SET password = '$hash' WHERE id = 1");
echo "Password reset to: password123\n";