<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$funnelId = 26;
$stages = DB::table('funnel_stages')->where('funnel_id', $funnelId)->orderBy('order')->get();

echo "Funnel #$funnelId Stages:\n";
echo "------------------------\n";
foreach ($stages as $s) {
    $content = json_decode($s->content, true);
    $url = $content['url'] ?? 'N/A';
    echo "{$s->order}. {$s->name}\n";
    echo "   Type: {$s->type}\n";
    echo "   URL: {$url}\n\n";
}

echo "Total stages: " . count($stages) . "\n";