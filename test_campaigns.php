<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Campaign;

try {
    $campaigns = Campaign::withCount('campaignLeads')->get();
    echo "<h1>Campaigns: " . $campaigns->count() . "</h1>";
    foreach ($campaigns as $c) {
        echo "<p>" . $c->name . " - " . $c->campaign_leads_count . " leads</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}