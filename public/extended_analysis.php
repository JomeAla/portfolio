<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Extended Funnel System Analysis</h1>";

echo "<h2>1. All Funnels in System</h2>";
$funnels = DB::table('funnels')->select('id', 'name', 'is_active', 'goal', 'is_template', 'health_score')->orderBy('id')->get();
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Active</th><th>Goal</th><th>Template</th><th>Health</th></tr>";
foreach($funnels as $f) {
    echo "<tr><td>{$f->id}</td><td>{$f->name}</td><td>" . ($f->is_active ? 'Yes' : 'No') . "</td><td>{$f->goal}</td><td>" . ($f->is_template ? 'Yes' : 'No') . "</td><td>" . ($f->health_score ?? '-') . "</td></tr>";
}
echo "</table>";

echo "<h2>2. Landing Pages Not Linked to Funnels</h2>";
$orphanLPs = DB::table('landing_pages')->whereNull('funnel_id')->count();
echo "<p>Orphan Landing Pages: " . $orphanLPs . "</p>";

echo "<h2>3. Funnel vs Landing Page Count</h2>";
echo "<table border='1'><tr><th>Funnel ID</th><th>Stages</th><th>LPs Linked</th></tr>";
foreach($funnels as $f) {
    $stages = DB::table('funnel_stages')->where('funnel_id', $f->id)->count();
    $lps = DB::table('landing_pages')->where('funnel_id', $f->id)->count();
    echo "<tr><td>{$f->id}</td><td>{$stages}</td><td>{$lps}</td></tr>";
}
echo "</table>";

echo "<h2>4. Products for Upsell</h2>";
$upsells = DB::table('funnels')->where('upsell_enabled', true)->select('id', 'name', 'upsell_product_id', 'upsell_discount')->get();
foreach($upsells as $u) {
    echo "<p>Funnel {$u->id} ({$u->name}): Upsell product ID = {$u->upsell_product_id}, Discount = {$u->upsell_discount}%</p>";
}

echo "<h2>5. Checkout/Payment Routes</h2>";
$checkoutRoutes = DB::table('products')->select('id', 'title', 'slug', 'is_free')->limit(10)->get();
echo "<table border='1'><tr><th>ID</th><th>Product</th><th>Slug</th><th>Free</th></tr>";
foreach($checkoutRoutes as $p) {
    echo "<tr><td>{$p->id}</td><td>{$p->title}</td><td>{$p->slug}</td><td>" . ($p->is_free ? 'Yes' : 'No') . "</td></tr>";
}
echo "</table>";

echo "<h2>6. Email Sequence Status</h2>";
$seqs = DB::table('sequences')->select('id', 'name', 'is_active')->limit(10)->get();
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Active</th></tr>";
foreach($seqs as $s) {
    echo "<tr><td>{$s->id}</td><td>{$s->name}</td><td>" . ($s->is_active ? 'Yes' : 'No') . "</td></tr>";
}
echo "</table>";

echo "<h2>7. Settings/Integration</h2>";
$settings = DB::table('settings')->where('key', 'like', '%stripe%')->orWhere('key', 'like', '%paypal%')->orWhere('key', 'like', '%payment%')->get();
echo "<p>Payment settings: " . $settings->count() . " found</p>";
foreach($settings as $st) {
    echo "<li>{$st->key}: " . (strlen($st->value) > 5 ? substr($st->value, 0, 5) . '...' : $st->value) . "</li>";
}

echo "<h2>8. Critical Issues</h2>";
echo "<ul>";
$noLeadsFunnels = DB::table('funnels')->where('is_template', false)->count();
echo "<li>Funnels without leads: Need lead capture integration working</li>";
$healthNull = DB::table('funnels')->whereNull('health_score')->count();
echo "<li>Funnels without health score: {$healthNull} funnels need health calculation run</li>";
echo "</ul>";