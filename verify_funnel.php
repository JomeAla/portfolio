<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFY ALL DB RECORDS ===\n";

echo "Free product (ID 24): ";
$fp = DB::table('products')->find(24, ['id','title','price','is_active']);
echo json_encode($fp) . "\n";

echo "Premium product (ID 4): ";
$pp = DB::table('products')->find(4, ['id','title','price','sale_price','is_active']);
echo json_encode($pp) . "\n";

echo "Pre-sale seq (ID 71): ";
$s71 = DB::table('email_sequences')->find(71, ['id','name','trigger_type','is_active']);
echo json_encode($s71) . "\n";

echo "Post-purchase seq (ID 72): ";
$s72 = DB::table('email_sequences')->find(72, ['id','name','trigger_type','is_active']);
echo json_encode($s72) . "\n";

echo "Funnel (ID 31): ";
$f = DB::table('funnels')->find(31, ['id','name','product_id','welcome_sequence_id','is_active']);
echo json_encode($f) . "\n";

echo "Landing page (ID 6): ";
$lp = DB::table('landing_pages')->find(6, ['id','slug','funnel_id','sequence_id','is_active']);
echo json_encode($lp) . "\n";

echo "Sequences table sync 71: ";
echo json_encode(DB::table('sequences')->find(71, ['id','name','is_active'])) . "\n";

echo "Sequences table sync 72: ";
echo json_encode(DB::table('sequences')->find(72, ['id','name','is_active'])) . "\n";

echo "Funnel stages: ";
$st = DB::table('funnel_stages')->where('funnel_id', 31)->orderBy('order')->get(['name','type','order']);
echo json_encode($st) . "\n";

echo "Pre-sale steps: " . DB::table('sequence_steps')->where('sequence_id', 71)->count() . "\n";
echo "Post-purchase steps: " . DB::table('sequence_steps')->where('sequence_id', 72)->count() . "\n";

echo "\n=== CHECK FILES ON DISK ===\n";
$freeFile = public_path('uploads/free-products/files/free-whatsapp-marketing-bundle.html');
echo "Free HTML: " . (file_exists($freeFile) ? 'OK (' . filesize($freeFile) . ' bytes)' : 'MISSING') . "\n";
$premFile = storage_path('app/public/products/WhatsApp Marketing Bundle - Complete Templates.zip');
echo "Premium ZIP: " . (file_exists($premFile) ? 'OK (' . filesize($premFile) . ' bytes)' : 'MISSING') . "\n";

echo "\n=== CHECK LATEST LEAD ===\n";
$latest = DB::table('leads')->orderBy('id', 'desc')->first(['id','email','name','sequence_id','landing_page_id','enrolled_at']);
if ($latest) {
    echo "Latest lead: " . json_encode($latest) . "\n";
    $queues = DB::table('email_queue')->where('lead_id', $latest->id)->count();
    echo "Email queues for this lead: " . $queues . "\n";
    $funnelLead = DB::table('funnel_leads')->where('lead_id', $latest->id)->first();
    echo "Funnel lead: " . json_encode($funnelLead) . "\n";
} else {
    echo "No leads found\n";
}

echo "\n=== ALL LEADS ===\n";
$allLeads = DB::table('leads')->orderBy('id', 'desc')->take(5)->get(['id','email','name','sequence_id','landing_page_id','enrolled_at','created_at']);
echo json_encode($allLeads) . "\n";
