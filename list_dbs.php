<?php
/**
 * List Databases
 * Access via: https://joala.com.ng/list_dbs.php
 */

$host = 'localhost';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$pdo = new PDO("mysql:host=$host", $user, $pass);
$dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);

echo "<h1>Databases</h1><ul>";
foreach ($dbs as $db) {
    echo "<li>$db</li>";
}
echo "</ul>";