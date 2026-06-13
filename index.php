<?php
/**
 * Laravel Router - Forward all requests to public/index.php
 */
$publicPath = __DIR__ . '/public/index.php';

if (file_exists($publicPath)) {
    require $publicPath;
} else {
    echo "Error: public/index.php not found";
}