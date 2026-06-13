<?php
$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$customers = $pdo->query("SELECT * FROM customer_accounts")->fetchAll(PDO::FETCH_ASSOC);
print_r($customers);