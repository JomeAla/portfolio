<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use App\Models\BlogPost;
use App\Models\LandingPage;
use App\Models\EmailQueue;
use App\Models\EmailSequence;

echo "<pre style='background:#1a1a1a;color:#00ff00;padding:20px;'>";
echo "=== Marketing Dashboard Stats ===\n\n";

echo "Leads:\n";
echo "  Total: " . Lead::count() . "\n";
echo "  Recent: " . Lead::latest()->limit(5)->count() . "\n";
$recentLeads = Lead::latest()->limit(5)->get();
foreach($recentLeads as $lead) {
    echo "  - " . $lead->name . " (" . $lead->email . ")\n";
}

echo "\nBlog Posts:\n";
echo "  Total: " . BlogPost::count() . "\n";
echo "  Published: " . BlogPost::where('is_published', true)->count() . "\n";

echo "\nLanding Pages:\n";
echo "  Total: " . LandingPage::count() . "\n";

echo "\nEmail Sequences:\n";
echo "  Total: " . EmailSequence::count() . "\n";
echo "  Active: " . EmailSequence::where('is_active', true)->count() . "\n";

echo "\nEmail Queue:\n";
echo "  Queued: " . EmailQueue::where('status', 'pending')->count() . "\n";
echo "  Sent: " . EmailQueue::where('status', 'sent')->count() . "\n";

echo "\n=== Done ===\n";
echo "</pre>";