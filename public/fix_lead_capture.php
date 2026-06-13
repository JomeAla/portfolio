<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>Step 1: Fix Lead Capture</h1>";

// Check landing page
$lp = DB::table('landing_pages')->where('slug', 'free-wordpress-starter-kit')->first();
echo "<h2>Current Landing Page Status</h2>";
echo "<p>ID: {$lp->id}</p>";
echo "<p>Title: {$lp->title}</p>";
echo "<p>Slug: {$lp->slug}</p>";
echo "<p>Funnel ID: " . ($lp->funnel_id ?? 'NULL - NOT LINKED!') . "</p>";

// Link to funnel 26
echo "<h2>Linking to Funnel 26...</h2>";
DB::table('landing_pages')->where('id', $lp->id)->update(['funnel_id' => 26]);

// Verify
$lp2 = DB::table('landing_pages')->where('id', $lp->id)->first();
echo "<p>Funnel ID (updated): {$lp2->funnel_id}</p>";

echo "<h2>Verifying Funnel Stages</h2>";
$stages = DB::table('funnel_stages')->where('funnel_id', 26)->orderBy('order')->get();
foreach ($stages as $s) {
    echo "<li>Stage {$s->order}: {$s->name} ({$s->type}) - Content: " . json_encode($s->content) . "</li>";
}

echo "<h2>Checking Funnel Lead Creation Logic</h2>";
// Check if the funnel has welcome sequence
$funnel = DB::table('funnels')->where('id', 26)->first();
echo "<p>Welcome Sequence ID: " . ($funnel->welcome_sequence_id ?? 'NULL') . "</p>";
echo "<p>Follow-up Sequence ID: " . ($funnel->followup_sequence_id ?? 'NULL') . "</p>";

echo "<p>DONE - Landing page now linked to funnel!</p>";