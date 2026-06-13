<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use App\Models\Funnel;
use App\Models\FunnelStage;

// Check funnel and stage
$funnel = Funnel::find(6);
if ($funnel) {
    echo "Funnel found: " . $funnel->name . "\n";
    $stage = FunnelStage::find(11);
    if ($stage) {
        echo "Stage found: " . $stage->name . " (type: " . $stage->type . ")\n";
    } else {
        echo "Stage 11 not found\n";
    }
} else {
    echo "Funnel 6 not found\n";
}

// List all funnel routes
echo "\n=== Checking routes ===\n";
$routes = Route::getRoutes();
foreach ($routes as $route) {
    if (strpos($route->uri(), 'funnel') !== false) {
        echo $route->methods()[0] . " " . $route->uri() . "\n";
    }
}