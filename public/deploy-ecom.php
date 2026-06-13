<?php
/**
 * Create zip on server from uploaded directory, then update DB.
 * Access via: https://www.joala.com.ng/deploy-ecom.php?key=joala2024&action=create_zip
 */

$host = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($host, 'joala.com.ng') === false) die('Access denied');

$key = $_GET['key'] ?? '';
if ($key !== 'joala2024') die('Invalid key');

$portfolioPath = '/home/joalacom/public_html';
require_once $portfolioPath . '/vendor/autoload.php';
$app = require $portfolioPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<pre>\n";
echo "=== Create E-commerce Starter Kit Zip ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Check if FTP uploaded the files somewhere
$possiblePaths = [
    '/home/joalacom/public_html/ecommerce-starter-kit',
    '/home/joalacom/ecommerce-starter-kit',
    '/home/joalacom/public_html/portfolio/ecommerce-starter-kit',
    '/home/joalacom/ftp-upload/ecommerce-starter-kit',
];

$foundDir = null;
foreach ($possiblePaths as $p) {
    if (is_dir($p) && count(scandir($p)) > 2) {
        echo "Found: $p (" . (count(scandir($p)) - 2) . " files)\n";
        $foundDir = $p;
        break;
    }
}

if (!$foundDir) {
    echo "Source directory not found. Let me check where FTP put it...\n";
    
    // Check if there's any ecom directory anywhere
    $checkPaths = [
        '/home/joalacom/',
        '/home/',
    ];
    
    foreach ($checkPaths as $base) {
        if (is_dir($base)) {
            $items = scandir($base);
            foreach ($items as $item) {
                if (stripos($item, 'ecom') !== false || stripos($item, 'laravel') !== false) {
                    $fullPath = $base . '/' . $item;
                    if (is_dir($fullPath)) {
                        echo "Found directory: $fullPath (" . (count(scandir($fullPath)) - 2) . " items)\n";
                        $foundDir = $fullPath;
                    }
                }
            }
        }
    }
    
    if (!$foundDir) {
        echo "\nFTP files not found on server.\n";
        echo "Need to upload files via different method.\n";
        echo "\nOptions:\n";
        echo "1. Upload via cPanel File Manager\n";
        echo "2. Use git to deploy\n";
        echo "3. Create zip locally and upload via HTTP chunked upload\n";
        
        echo "\n=== Checking if we can reach external files ===\n";
        
        // Check if we can access external URLs (to download the zip)
        $testUrl = 'https://www.google.com';
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
        $test = @file_get_contents($testUrl, false, $ctx);
        echo "Can access external URLs: " . ($test ? 'Yes' : 'No') . "\n";
        
        echo "\n=== Checking storage capacity ===\n";
        $storageDir = storage_path('app/public/products');
        echo "Storage path: $storageDir\n";
        echo "Exists: " . (is_dir($storageDir) ? 'Yes' : 'No') . "\n";
        
        // List any existing zips
        if (is_dir($storageDir)) {
            $files = scandir($storageDir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $size = filesize($storageDir . '/' . $f);
                echo "  $f: " . number_format($size / 1024, 2) . " KB\n";
            }
        }
        
        echo "\n=== Solution ===\n";
        echo "Upload ecom-starter-kit.zip via cPanel File Manager to:\n";
        echo "/home/joalacom/public_html/storage/app/public/products/\n";
        echo "\nThen visit: https://www.joala.com.ng/deploy-ecom.php?key=joala2024&action=finish\n";
    }
}

if ($foundDir) {
    echo "\nCreating zip from $foundDir...\n";
    $zipPath = storage_path('app/public/products/ecom-starter-kit.zip');
    
    // Remove existing empty zip
    if (file_exists($zipPath) && filesize($zipPath) == 0) {
        unlink($zipPath);
    }
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        echo "Failed to create zip\n";
        exit;
    }
    
    function addDirToZip($zip, $dir, $basePath) {
        $files = scandir($dir);
        $count = 0;
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $dir . '/' . $file;
            $relativePath = ltrim(str_replace($basePath . '/', '', $fullPath), '/');
            if (is_dir($fullPath)) {
                $count += addDirToZip($zip, $fullPath, $basePath);
            } else {
                $zip->addFile($fullPath, $relativePath);
                $count++;
            }
        }
        return $count;
    }
    
    $fileCount = addDirToZip($zip, $foundDir, $foundDir);
    $zip->close();
    
    $zipSize = filesize($zipPath);
    echo "Zip created: " . number_format($zipSize / 1024, 2) . " KB ($fileCount files)\n";
    
    // Update DB
    DB::table('products')->where('slug', 'ecommerce-starter-kit')->update([
        'file_path' => 'public/products/ecom-starter-kit.zip',
        'updated_at' => now(),
    ]);
    
    echo "DB updated with file_path: public/products/ecom-starter-kit.zip\n";
    echo "\n=== Done ===\n";
    echo "Product ID: 18\n";
    echo "Slug: ecommerce-starter-kit\n";
    echo "Price: NGN 55,000\n";
    echo "File: " . number_format($zipSize / 1024, 2) . " KB zip\n";
    echo "\nSales page: https://www.joala.com.ng/ecommerce-starter-kit.php\n";
}