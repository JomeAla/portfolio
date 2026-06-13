<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing existing blog images ===\n\n";

// Paths
$wrongDir = '/home/joalacom/public_html/public/uploads/blog';
$correctDir = '/home/joalacom/public_html/uploads/blog';

// Create correct directory if needed
if (!is_dir($correctDir)) {
    if (mkdir($correctDir, 0755, true)) {
        echo "✓ Created correct directory\n";
    }
} else {
    echo "✓ Correct directory exists\n";
}

// Move files
if (is_dir($wrongDir)) {
    $files = scandir($wrongDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $source = "$wrongDir/$file";
            $dest = "$correctDir/$file";
            if (rename($source, $dest)) {
                echo "✓ Moved: $file\n";
            } else {
                echo "✗ Failed to move: $file\n";
            }
        }
    }
} else {
    echo "Wrong directory does not exist\n";
}

echo "\n=== DONE ===";