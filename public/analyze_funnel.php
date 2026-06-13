<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Live Funnel System Analysis</h1>";

echo "<h2>Database Schema</h2>";
echo "<h3>funnels</h3><pre>";
print_r(DB::getSchemaBuilder()->getColumnListing('funnels'));
echo "</pre>";

echo "<h3>funnel_stages</h3><pre>";
print_r(DB::getSchemaBuilder()->getColumnListing('funnel_stages'));
echo "</pre>";

echo "<h3>funnel_leads</h3><pre>";
print_r(DB::getSchemaBuilder()->getColumnListing('funnel_leads'));
echo "</pre>";

echo "<h2>Record Counts</h2>";
echo "<p>Funnels: " . DB::table('funnels')->count() . "</p>";
echo "<p>Funnel Stages: " . DB::table('funnel_stages')->count() . "</p>";
echo "<p>Funnel Leads: " . DB::table('funnel_leads')->count() . "</p>";

echo "<h2>Funnels List</h2><table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Active</th><th>Goal</th><th>Health</th><th>Stages</th></tr>";
$funnels = DB::table('funnels')->orderBy('id')->get();
foreach($funnels as $f) {
    $stageCount = DB::table('funnel_stages')->where('funnel_id', $f->id)->count();
    echo "<tr><td>$f->id</td><td>$f->name</td><td>" . ($f->is_active ? 'Yes' : 'No') . "</td><td>$f->goal</td><td>$f->health_score</td><td>$stageCount</td></tr>";
}
echo "</table>";

echo "<h2>Funnel Stages Detail</h2>";
foreach($funnels as $f) {
    echo "<h3>$f->name (ID: $f->id)</h3>";
    $stages = DB::table('funnel_stages')->where('funnel_id', $f->id)->orderBy('order')->get();
    echo "<table border='1' cellpadding='3'><tr><th>Order</th><th>Name</th><th>Type</th><th>Content</th><th>Delay</th></tr>";
    foreach($stages as $s) {
        $content = is_array($s->content) ? json_encode($s->content) : $s->content;
        echo "<tr><td>$s->order</td><td>$s->name</td><td>$s->type</td><td>" . substr($content, 0, 100) . "</td><td>$s->delay_days days</td></tr>";
    }
    echo "</table>";
}

echo "<h2>Funnel Leads Summary</h2>";
echo "<table border='1' cellpadding='3'><tr><th>Funnel</th><th>Total Leads</th><th>Hot</th><th>Converted</th></tr>";
foreach($funnels as $f) {
    $total = DB::table('funnel_leads')->where('funnel_id', $f->id)->count();
    $hot = DB::table('funnel_leads')->where('funnel_id', $f->id)->where('is_tagged_hot', true)->count();
    $converted = DB::table('funnel_leads')->where('funnel_id', $f->id)->where('converted', true)->count();
    echo "<tr><td>$f->name</td><td>$total</td><td>$hot</td><td>$converted</td></tr>";
}
echo "</table>";