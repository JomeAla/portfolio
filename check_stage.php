<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FunnelStage;

$stage = FunnelStage::find(11);
if ($stage) {
    echo "Stage: " . $stage->name . "\n";
    echo "Type: " . $stage->type . "\n";
    echo "Content: ";
    print_r($stage->content);
}