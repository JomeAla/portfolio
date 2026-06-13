<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Funnel;

echo "<h1>Comprehensive Live Funnel Analysis</h1>";

echo "<h2>1. Funnel Overview</h2>";
$funnels = Funnel::with('stages')->get();
foreach($funnels as $f) {
    echo "<h3>{$f->name} (ID: {$f->id})</h3>";
    echo "<ul>";
    echo "<li><strong>Active:</strong> " . ($f->is_active ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>Goal:</strong> {$f->goal}</li>";
    echo "<li><strong>Health Score:</strong> " . ($f->health_score ?? 'Not calculated') . "</li>";
    echo "<li><strong>Template:</strong> " . ($f->is_template ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>A/B Testing:</strong> " . ($f->ab_testing_enabled ? 'Enabled' : 'Disabled') . "</li>";
    echo "<li><strong>Welcome Sequence:</strong> " . ($f->welcome_sequence_id ?? 'None') . "</li>";
    echo "<li><strong>Follow-up Sequence:</strong> " . ($f->followup_sequence_id ?? 'None') . "</li>";
    echo "<li><strong>Upsell Enabled:</strong> " . ($f->upsell_enabled ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>Exit Popup:</strong> " . ($f->exit_popup_enabled ? 'Yes' : 'No') . "</li>";
    echo "<li><strong>Countdown:</strong> " . ($f->countdown_enabled ? 'Yes (' . $f->countdown_hours . ' hours)' : 'No') . "</li>";
    echo "<li><strong>Webhook:</strong> " . ($f->webhook_enabled ? $f->webhook_url : 'Disabled') . "</li>";
    echo "</ul>";
    
    echo "<h4>Stages:</h4><table border='1' cellpadding='3'><tr><th>#</th><th>Name</th><th>Type</th><th>URL/Content</th><th>Condition</th><th>Action</th></tr>";
    foreach($f->stages->sortBy('order') as $s) {
        $content = is_array($s->content) ? ($s->content['url'] ?? json_encode($s->content)) : $s->content;
        echo "<tr><td>{$s->order}</td><td>{$s->name}</td><td>{$s->type}</td><td>" . substr($content, 0, 60) . "</td><td>{$s->condition_type}</td><td>{$s->action_on_complete}</td></tr>";
    }
    echo "</table>";
    
    $leads = DB::table('funnel_leads')->where('funnel_id', $f->id)->get();
    echo "<h4>Lead Stats:</h4>";
    echo "<ul>";
    echo "<li>Total Leads: " . $leads->count() . "</li>";
    echo "<li>Hot Leads: " . $leads->where('is_tagged_hot', true)->count() . "</li>";
    echo "<li>Converted: " . $leads->where('converted', true)->count() . "</li>";
    echo "</ul>";
}

echo "<h2>2. Features Configuration</h2>";
$f = $funnels->first();
if ($f) {
    echo "<h3>Scoring Config (Funnel ID: {$f->id})</h3>";
    echo "<table border='1'><tr><th>Setting</th><th>Value</th></tr>";
    echo "<tr><td>Score per page view</td><td>{$f->score_per_page}</td></tr>";
    echo "<tr><td>Score per email open</td><td>{$f->score_per_email}</td></tr>";
    echo "<tr><td>Score per checkout</td><td>{$f->score_per_checkout}</td></tr>";
    echo "<tr><td>Score per click</td><td>" . ($f->score_per_click ?? 'Not set') . "</td></tr>";
    echo "<tr><td>Hot threshold</td><td>{$f->score_hot_threshold}</td></tr>";
    echo "<tr><td>Hot lead tag</td><td>" . ($f->hot_lead_tag ?? 'None') . "</td></tr>";
    echo "</table>";
}

echo "<h2>3. Products & Services</h2>";
echo "<p>Products: " . DB::table('products')->count() . "</p>";
echo "<p>Services: " . DB::table('services')->count() . "</p>";

echo "<h2>4. Email Sequences</h2>";
echo "<p>Sequences: " . DB::table('sequences')->count() . "</p>";
echo "<p>Email Sequences: " . DB::table('email_sequences')->count() . "</p>";

echo "<h2>5. Landing Pages</h2>";
$lps = DB::table('landing_pages')->select('id', 'title', 'slug', 'funnel_id')->limit(10)->get();
echo "<table border='1'><tr><th>ID</th><th>Title</th><th>Slug</th><th>Funnel ID</th></tr>";
foreach($lps as $lp) {
    echo "<tr><td>{$lp->id}</td><td>{$lp->title}</td><td>{$lp->slug}</td><td>{$lp->funnel_id}</td></tr>";
}
echo "</table>";

echo "<h2>6. Lead Scoring Model Fields</h2>";
$leadScores = DB::table('lead_scores')->count();
echo "<p>Lead Scores Records: " . $leadScores . "</p>";

echo "<h2>7. Integration Status</h2>";
echo "<ul>";
echo "<li><strong>Stripe:</strong> " . (DB::table('settings')->where('key', 'stripe_secret_key')->exists() ? 'Configured' : 'Not configured') . "</li>";
echo "<li><strong>PayPal:</strong> " . (DB::table('settings')->where('key', 'paypal_client_id')->exists() ? 'Configured' : 'Not configured') . "</li>";
echo "<li><strong>SMTP:</strong> " . (DB::table('settings')->where('key', 'mail_host')->exists() ? 'Configured' : 'Not configured') . "</li>";
echo "</ul>";

echo "<h2>8. Summary Assessment</h2>";
echo "<h3>What's Working:</h3>";
echo "<ul>";
echo "<li>Funnel structure with multiple stage types</li>";
echo "<li>Lead scoring system with configurable points</li>";
echo "<li>Health score calculation</li>";
echo "<li>A/B testing framework (configured but not used)</li>";
echo "<li>Email sequence integration</li>";
echo "<li>Webhook support</li>";
echo "<li>Upsell configuration</li>";
echo "<li>Exit popup & countdown timer settings</li>";
echo "</ul>";

echo "<h3>What's Missing/Broken:</h3>";
echo "<ul>";
$leadsTotal = DB::table('funnel_leads')->count();
echo "<li><strong>No leads in funnel:</strong> " . $leadsTotal . " total leads</li>";
$lpsWithFunnel = DB::table('landing_pages')->whereNotNull('funnel_id')->count();
echo "<li><strong>Landing pages linked to funnels:</strong> " . $lpsWithFunnel . "</li>";
echo "<li><strong>No checkout/payment integration visible</li>";
echo "<li><strong>No membership/course delivery system</li>";
echo "<li><strong>A/B test not actively running</li>";
echo "<li><strong>Health score not calculated</li>";
echo "</ul>";