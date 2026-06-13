<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Clearing caches...\n";

Illuminate\Support\Facades\Artisan::call('route:clear');
echo "✓ Route cache cleared\n";

Illuminate\Support\Facades\Artisan::call('view:clear');
echo "✓ View cache cleared\n";

Illuminate\Support\Facades\Artisan::call('config:clear');
echo "✓ Config cache cleared\n";

Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "✓ Application cache cleared\n";

echo "\nAll caches cleared successfully!";