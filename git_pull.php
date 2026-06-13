<?php
/**
 * Git Pull Script - Run this via browser to pull latest code from GitHub
 * URL: https://joala.com.ng/git_pull.php
 */

$repoUrl = 'https://github.com/JomeAla/portfolio.git';
$repoBranch = 'master';
$docRoot = __DIR__;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;font-family:monospace;'>";
echo "=== Git Pull Script ===\n\n";

// Change to document root
chdir($docRoot);

// Check if .git exists
if (!is_dir('.git')) {
    echo "Initializing git repository...\n";
    exec("git init", $output, $return);
    exec("git remote add origin " . $repoUrl, $output, $return);
}

// Configure git
exec("git config pull.rebase false", $output, $return);
exec("git config user.email 'jomealawuru@hotmail.com'", $output, $return);
exec("git config user.name 'JomeAla'", $output, $return);

// First, discard all local changes and do clean checkout
echo "Fetching from GitHub...\n";
exec("git fetch --all 2>&1", $output, $return);

echo "Resetting to match GitHub...\n";
exec("git reset --hard origin/" . $repoBranch . " 2>&1", $output, $return);

foreach ($output as $line) {
    echo $line . "\n";
}

if ($return === 0) {
    echo "\n✅ Git reset successful!\n";
    
    // Create necessary directories
    echo "\nCreating cache directories...\n";
    @mkdir($docRoot . '/bootstrap/cache', 0755, true);
    @mkdir($docRoot . '/storage/framework/cache', 0755, true);
    @mkdir($docRoot . '/storage/framework/sessions', 0755, true);
    @mkdir($docRoot . '/storage/framework/views', 0755, true);
    @mkdir($docRoot . '/storage/logs', 0755, true);
    
    // Set permissions
    chmod($docRoot . '/bootstrap/cache', 0755);
    echo "Directories created.\n";
    
    // Clear caches
    echo "\nClearing Laravel caches...\n";
    if (file_exists('artisan')) {
        chdir($docRoot);
        exec("php artisan config:clear 2>&1", $configOutput, $configReturn);
        exec("php artisan route:clear 2>&1", $routeOutput, $routeReturn);
        exec("php artisan cache:clear 2>&1", $cacheOutput, $cacheReturn);
        echo "Caches cleared.\n";
    }
} else {
    echo "\n❌ Git reset failed!\n";
}

echo "\nDone.</pre>";