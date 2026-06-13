<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Delete all funnel stages first (due to foreign key)
DB::table('funnel_stages')->delete();

// Delete all funnels
DB::table('funnels')->delete();

echo "All funnels and stages deleted successfully!\n";
echo "Funnels remaining: " . DB::table('funnels')->count() . "\n";
echo "Stages remaining: " . DB::table('funnel_stages')->count() . "\n";