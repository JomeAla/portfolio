<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing file upload ===\n\n";

// Test 1: Check public path
$publicPath = public_path();
echo "Public path: $publicPath\n";

// Test 2: Check uploads/blog path  
$uploadPath = public_path('uploads/blog');
echo "Upload path: $uploadPath\n";

// Test 3: List files in uploads/blog
echo "\nFiles in uploads/blog:\n";
if (is_dir($uploadPath)) {
    $files = scandir($uploadPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
    if (count($files) <= 2) {
        echo "  (empty)\n";
    }
} else {
    echo "  Directory does not exist!\n";
}

// Test 4: Check if uploads folder exists at root level
echo "\nChecking root public_html:\n";
$rootPath = '/home/joalacom/public_html';
if (is_dir("$rootPath/uploads")) {
    echo "uploads folder exists at root\n";
    $rootFiles = scandir("$rootPath/uploads");
    foreach ($rootFiles as $f) {
        if ($f !== '.' && $f !== '..') {
            echo "  - $f\n";
        }
    }
} else {
    echo "uploads folder does NOT exist at root\n";
}

echo "\n=== DONE ===";