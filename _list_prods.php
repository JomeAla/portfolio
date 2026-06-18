<?php
$pdo = new PDO('mysql:host=localhost;dbname=joalacom_joala;charset=utf8mb4', 'joalacom_joala', 'J0ala@2024!');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->query("SELECT id, title, slug, price, sale_price FROM products WHERE is_active = 1 ORDER BY id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID:{$row['id']} | {$row['title']} | slug:{$row['slug']} | price:{$row['price']} | sale:{$row['sale_price']}\n";
}
