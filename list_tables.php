<?php
/**
 * List Tables with Row Counts
 * Access via: https://joala.com.ng/list_tables.php
 */

$host = 'localhost';
$db   = 'joalacom_joala';
$user = 'joalacom_joala';
$pass = 'J0ala@2024!';

$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo "<h1>Tables in Live Database</h1><ul>";
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo "<li><strong>$table</strong> - $count rows</li>";
}
echo "</ul>";
echo "<p>Total: " . count($tables) . " tables</p>";