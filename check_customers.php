<?php
$pdo = new PDO('mysql:host=localhost;dbname=joalacom_joala', 'joalacom_joala', 'J0ala@2024!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables:\n";
foreach($tables as $t) {
    if(strpos($t, 'customer') !== false) echo $t . "\n";
}