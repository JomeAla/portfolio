<?php
/**
 * Fix Laravel Boot Script
 * URL: https://joala.com.ng/fix_boot.php
 */

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;font-family:monospace;'>";
echo "=== Fix Laravel Boot ===\n\n";

chdir(__DIR__);

// Clear all cache files
echo "Clearing all cached files...\n";
$cacheDirs = [
    'bootstrap/cache',
    'storage/framework/cache',
    'storage/framework/views', 
    'storage/framework/sessions'
];

foreach ($cacheDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*.php');
        foreach ($files as $file) {
            @unlink($file);
            echo "Deleted: $file\n";
        }
    }
}

// Run artisan commands
echo "\nRunning artisan optimize...\n";
exec("php artisan optimize 2>&1", $output, $return);
foreach ($output as $line) {
    echo $line . "\n";
}

echo "\nDone.</pre>";