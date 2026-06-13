<?php
$key = $_GET['key'] ?? '';
if ($key !== 'joala2024') { die('Invalid key'); }

$action = $_GET['do'] ?? '';

if ($action === 'write_htaccess') {
    $htaccess = '<IfModule mod_rewrite.c>
    RewriteEngine On
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule . - [L]
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
';
    $written = file_put_contents(__DIR__ . '/../.htaccess', $htaccess);
    if ($written !== false) {
        echo "OK .htaccess written (" . $written . " bytes)";
    } else {
        echo "FAILED to write .htaccess";
    }
    exit;
}

if ($action === 'check') {
    $htaccessPath = __DIR__ . '/../.htaccess';
    echo "HTACCESS exists: " . (file_exists($htaccessPath) ? 'YES' : 'NO') . "\n";
    if (file_exists($htaccessPath)) {
        echo "Content:\n" . file_get_contents($htaccessPath);
    }
    echo "\n\nINDEX exists: " . (file_exists(__DIR__ . '/../index.php') ? 'YES' : 'NO') . "\n";
    echo "GIT_PULL exists: " . (file_exists(__DIR__ . '/../git_pull.php') ? 'YES' : 'NO') . "\n";
    echo "PUBLIC_GIT_PULL exists: " . (file_exists(__DIR__ . '/git_pull.php') ? 'YES' : 'NO') . "\n";
    echo "CWD: " . __DIR__ . "\n";
    exit;
}

if ($action === 'gitpull') {
    chdir(__DIR__ . '/..');
    exec('git pull origin master 2>&1', $output, $returnVar);
    echo implode("\n", $output);
    echo "\nExit code: $returnVar";
    exit;
}

echo "Usage: ?key=joala2024&do=check|write_htaccess|gitpull";
