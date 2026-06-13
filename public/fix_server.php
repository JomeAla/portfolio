<?php
// Server fix script - run via browser
// Access: https://joala.com.ng/portfolio/fix_server.php?key=admin123

header('Content-Type: text/plain');

$key = $_GET['key'] ?? '';
if ($key !== 'admin123') {
    die('Invalid key');
}

echo "Starting fix...\n\n";

chdir('/home/joalacom/public_html/portfolio');

echo "1. Git pull...\n";
system('git pull origin master 2>&1', $ret1);
echo "Return: $ret1\n\n";

echo "2. Clear route cache...\n";
system('php artisan route:clear 2>&1', $ret2);
echo "Return: $ret2\n\n";

echo "3. Clear cache...\n";
system('php artisan cache:clear 2>&1', $ret3);
echo "Return: $ret3\n\n";

echo "4. Clear view cache...\n";
system('php artisan view:clear 2>&1', $ret4);
echo "Return: $ret4\n\n";

echo "5. Clear config cache...\n";
system('php artisan config:clear 2>&1', $ret5);
echo "Return: $ret5\n\n";

echo "\nDone!";