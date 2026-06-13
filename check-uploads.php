<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking upload directories ===\n\n";

$uploadDir = public_path('uploads/blog');

if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "✓ Created blog upload directory\n";
    } else {
        echo "✗ Failed to create blog upload directory\n";
    }
} else {
    echo "✓ Blog upload directory exists: $uploadDir\n";
}

if (is_dir($uploadDir)) {
    if (is_writable($uploadDir)) {
        echo "✓ Blog upload directory is writable\n";
    } else {
        echo "✗ Blog upload directory is NOT writable\n";
    }
}

echo "\n=== DONE ===";