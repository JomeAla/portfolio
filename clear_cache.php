<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Clear Routes Cache ===\n\n";

// Clear all caches
Cache::flush();
echo "Cache cleared\n";

// Clear route cache
Artisan::call('route:clear');
echo "Route cache cleared\n";

// Clear view cache
Artisan::call('view:clear');
echo "View cache cleared\n";

// Clear config cache
Artisan::call('config:clear');
echo "Config cache cleared\n";

// Check routes
$routes = Route::getRoutes();
echo "\nMarketing routes found: " . count($routes->getByName()) . " total routes\n";

// Check specific route
$marketingRoute = Route::getRoutes()->getByName('admin.marketing');
if ($marketingRoute) {
    echo "admin.marketing route: FOUND - " . $marketingRoute->uri() . "\n";
} else {
    echo "admin.marketing route: NOT FOUND\n";
}

echo "\n=== Done ===\n";
echo "</pre>";