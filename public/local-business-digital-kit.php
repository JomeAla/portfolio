<?php
error_reporting(0);
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

session_start();

use Illuminate\Support\Facades\DB;
use App\Models\Funnel;
use App\Models\FunnelLead;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Setting;
use App\Models\EmailQueue;

$step = $_GET['step'] ?? 'landing';
$email = $_GET['email'] ?? $_SESSION['lead_email'] ?? '';
$name = $_SESSION['lead_name'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'capture_lead') {
    $emailInput = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $nameInput = trim($_POST['name'] ?? '');
    $phoneInput = trim($_POST['phone'] ?? '');

    if (!$emailInput) {
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=invalid_email');
        exit;
    }

    $utmData = [
        'utm_source' => $_POST['utm_source'] ?? '',
        'utm_medium' => $_POST['utm_medium'] ?? '',
        'utm_campaign' => $_POST['utm_campaign'] ?? '',
    ];
    foreach ($utmData as $k => $v) { if ($v) $_SESSION[$k] = $v; }
    $_SESSION['referrer_url'] = $_SERVER['HTTP_REFERER'] ?? '';

    $lead = Lead::firstOrCreate(['email' => $emailInput], [
        'name' => $nameInput,
        'phone' => $phoneInput,
        'source' => 'local_business_digital_kit',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(7);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 7)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 7, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'local_business_digital_kit',
                'score' => $newScore,
            ]
        );

        if ($funnel->welcome_sequence_id) {
            $existingQueue = EmailQueue::where('lead_id', $lead->id)
                ->where('sequence_id', $funnel->welcome_sequence_id)
                ->where('status', 'pending')
                ->first();

            if (!$existingQueue) {
                $steps = DB::table('sequence_steps')
                    ->where('sequence_id', $funnel->welcome_sequence_id)
                    ->orderBy('step_order')
                    ->get();

                foreach ($steps as $step) {
                    $delayMin = ($step->delay_days ?? 0) * 24 * 60 + ($step->delay_hours ?? 0) * 60;
                    EmailQueue::create([
                        'lead_id' => $lead->id,
                        'sequence_id' => $funnel->welcome_sequence_id,
                        'subject' => $step->subject ?? 'Your download is ready',
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'Local Business Digital Kit'], $step->body ?? ''),
                        'status' => 'pending',
                        'scheduled_at' => now()->addMinutes($delayMin),
                    ]);
                }
            }
        }
    }

    $_SESSION['lead_email'] = $emailInput;
    $_SESSION['lead_id'] = $lead->id;
    $_SESSION['lead_name'] = $nameInput;

    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?step=checkout&email=' . urlencode($emailInput));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'init_payment') {
    header('Content-Type: application/json');

    $emailInput = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $nameInput = trim($_POST['name'] ?? '');
    $phoneInput = trim($_POST['phone'] ?? '');
    $couponCode = trim($_POST['coupon_code'] ?? '');

    if (!$emailInput || !$nameInput || !$phoneInput) {
        echo json_encode(['error' => 'Please fill in all required fields.']);
        exit;
    }

    $product = Product::where('slug', 'local-business-digital-kit')->where('is_active', true)->first();
    if (!$product) {
        echo json_encode(['error' => 'Product not found.']);
        exit;
    }

    $amount = (float)($product->sale_price ?? $product->price);
    $discount = 0;

    if ($couponCode) {
        $coupon = \App\Models\Coupon::where('code', strtoupper($couponCode))->first();
        if ($coupon && $coupon->isValid()) {
            if ($coupon->min_order_amount && $amount < (float)$coupon->min_order_amount) {
            } else {
                if ($coupon->discount_type === 'percentage') {
                    $discount = $amount * ((float)$coupon->discount_value / 100);
                } else {
                    $discount = (float)$coupon->discount_value;
                }
                if ($coupon->max_discount) {
                    $discount = min($discount, (float)$coupon->max_discount);
                }
            }
        }
    }

    $finalAmount = max(0, $amount - $discount);

    $lead = Lead::firstOrCreate(['email' => $emailInput], [
        'name' => $nameInput,
        'phone' => $phoneInput,
        'source' => 'local_business_digital_kit',
    ]);

    $order = \App\Models\Order::create([
        'order_number' => \App\Models\Order::generateOrderNumber(),
        'product_id' => $product->id,
        'customer_name' => $nameInput,
        'customer_email' => $emailInput,
        'customer_phone' => $phoneInput,
        'amount' => $amount,
        'discount' => $discount,
        'final_amount' => $finalAmount,
        'coupon_code' => $couponCode ?: null,
        'payment_status' => 'pending',
        'download_token' => \App\Models\Order::generateDownloadToken(),
        'download_expires_at' => now()->addHours(24),
        'cart_started_at' => now(),
        'checkout_started_at' => now(),
        'lead_id' => $lead->id,
    ]);

    $_SESSION['lead_email'] = $emailInput;
    $_SESSION['order_id'] = $order->id;
    $_SESSION['order_number'] = $order->order_number;

    $paystackKey = Setting::get('paystack_public_key') ?? 'pk_live_xxxxxxxxxxxx';

    echo json_encode([
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'paystack_key' => $paystackKey,
        'amount' => (int)($finalAmount * 100),
        'email' => $emailInput,
        'reference' => $order->order_number,
    ]);
    exit;
}

if (isset($_GET['reference']) && isset($_GET['trxref'])) {
    $order = DB::table('orders')->where('order_number', $_GET['reference'])->first();
    if ($order && $order->payment_status === 'pending') {
        DB::table('orders')->where('order_number', $_GET['reference'])->update([
            'payment_status' => 'success',
            'payment_reference' => $_GET['trxref'],
            'updated_at' => now(),
        ]);

        if ($order->coupon_code) {
            DB::table('coupons')->where('code', $order->coupon_code)->increment('used_count');
        }

        if ($order->customer_email) {
            DB::table('funnel_leads')->where('funnel_id', 7)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'local-business-digital-kit')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Local Business Digital Kit';
$productPrice = $product ? number_format((float)($product->sale_price ?? $product->price), 0) : '12,000';
$productOldPrice = $product ? number_format((float)$product->price, 0) : '25,000';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 12000;
$productOldRaw = $product ? (float)$product->price : 25000;
$savings = number_format($productOldRaw - $productPriceRaw, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Get More Customers With a Simple Digital Presence | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://js.paystack.co/v2/inline.js"></script>
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --bg:#F0F9FF;
        --card:#FFFFFF;
        --text:#0C4A6E;
        --accent:#F97316;
        --accent-dark:#EA580C;
        --secondary:#0EA5E9;
        --light-border:#BAE6FD;
        --success:#22C55E;
    }

    html{scroll-behavior:smooth}
    body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

    h1,h2,h3,h4,h5,h6{font-family:'Manrope',sans-serif;font-weight:700;line-height:1.2}

    .container{max-width:1100px;margin:0 auto;padding:0 20px}

    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:16px 32px;font-size:16px;font-weight:600;border-radius:12px;cursor:pointer;border:none;transition:all .3s ease;text-decoration:none}
    .btn-primary{background:var(--accent);color:#fff;box-shadow:0 4px 14px rgba(249,115,22,0.4)}
    .btn-primary:hover{background:var(--accent-dark);transform:translateY(-2px);box-shadow:0 6px 20px rgba(249,115,22,0.5)}
    .btn-secondary{background:var(--card);color:var(--text);border:2px solid var(--light-border)}
    .btn-secondary:hover{border-color:var(--secondary);background:#F0F9FF}

    .timer{display:inline-flex;align-items:center;gap:6px;font-size:14px;font-weight:500;color:var(--accent);background:rgba(249,115,22,0.1);padding:8px 16px;border-radius:20px;margin-top:12px}
    .timer svg{width:16px;height:16px}

    .card{background:var(--card);border-radius:16px;padding:24px;border:1px solid var(--light-border);box-shadow:0 4px 20px rgba(12,74,110,0.08)}

    header{padding:20px 0;background:var(--card);border-bottom:1px solid var(--light-border)}
    header .container{display:flex;justify-content:space-between;align-items:center}
    .logo{font-family:'Manrope',sans-serif;font-weight:800;font-size:24px;color:var(--text);text-decoration:none}
    .logo span{color:var(--accent)}

    .hero{padding:80px 0;background:linear-gradient(180deg,var(--bg) 0%,#E0F2FE 100%)}
    .hero .container{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
    @media(max-width:768px){.hero .container{grid-template-columns:1fr;text-align:center}}
    .hero-content{max-width:520px}
    @media(max-width:768px){.hero-content{margin:0 auto}}
    .eyebrow{display:inline-block;font-size:13px;font-weight:600;color:var(--secondary);background:var(--card);padding:8px 16px;border-radius:20px;margin-bottom:20px;text-transform:uppercase;letter-spacing:1px}
    .hero h1{font-size:48px;margin-bottom:20px;color:var(--text)}
    @media(max-width:768px){.hero h1{font-size:32px}}
    .hero-sub{font-size:18px;color:#0C4A6E;opacity:0.8;margin-bottom:24px}
    .price-tag{display:flex;align-items:baseline;gap:12px;margin-bottom:8px}
    .price-tag .current{font-family:'Manrope',sans-serif;font-size:42px;font-weight:800;color:var(--text)}
    .price-tag .original{font-size:20px;color:#64748B;text-decoration:line-through}
    .savings{font-size:14px;font-weight:600;color:var(--success);background:rgba(34,197,94,0.1);padding:4px 12px;border-radius:12px}
    .hero-stats{display:flex;gap:20px;margin-top:30px;flex-wrap:wrap}
    @media(max-width:768px){.hero-stats{justify-content:center}}
    .stat-item{display:flex;align-items:center;gap:8px;font-size:14px;color:var(--text)}
    .stat-item svg{width:20px;height:20px;color:var(--secondary)}
    .hero-visual{display:flex;justify-content:center;align-items:center}
    .hero-visual svg{width:100%;max-width:450px;height:auto}

    .features{padding:80px 0}
    .section-header{text-align:center;max-width:600px;margin:0 auto 50px}
    .section-header h2{font-size:36px;margin-bottom:16px;color:var(--text)}
    .section-header p{font-size:18px;color:#0C4A6E;opacity:0.8}
    .features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    @media(max-width:900px){.features-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:600px){.features-grid{grid-template-columns:1fr}}
    .feature-card{text-align:center;padding:32px 24px}
    .feature-icon{width:64px;height:64px;background:linear-gradient(135deg,var(--secondary),#38BDF8);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px}
    .feature-icon svg{width:32px;height:32px;color:#fff}
    .feature-card h3{font-size:18px;margin-bottom:12px;color:var(--text)}
    .feature-card p{font-size:14px;color:#64748B}

    .testimonial{padding:80px 0;background:var(--card)}
    .testimonial-box{max-width:800px;margin:0 auto;text-align:center;padding:40px;background:linear-gradient(135deg,var(--bg),#E0F2FE);border-radius:24px;border:1px solid var(--light-border)}
    .testimonial-quote{font-size:20px;font-style:italic;color:var(--text);margin-bottom:20px;line-height:1.8}
    .testimonial-author{display:flex;align-items:center;justify-content:center;gap:12px}
    .testimonial-avatar{width:48px;height:48px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px}
    .testimonial-info{text-align:left}
    .testimonial-name{font-weight:600;color:var(--text)}
    .testimonial-role{font-size:14px;color:#64748B}

    .pricing{padding:80px 0;background:linear-gradient(180deg,var(--bg) 0%,var(--card) 100%)}
    .pricing-card{max-width:500px;margin:0 auto;text-align:center;padding:48px 40px;background:var(--card);border-radius:24px;border:2px solid var(--light-border);box-shadow:0 8px 30px rgba(12,74,110,0.1)}
    .pricing-card.featured{border-color:var(--accent);box-shadow:0 8px 40px rgba(249,115,22,0.2)}
    .pricing-badge{display:inline-block;font-size:12px;font-weight:600;color:var(--accent);background:rgba(249,115,22,0.1);padding:6px 14px;border-radius:20px;margin-bottom:20px}
    .pricing-price{font-family:'Manrope',sans-serif;font-size:56px;font-weight:800;color:var(--text);margin-bottom:8px}
    .pricing-price span{font-size:18px;font-weight:500;color:#64748B}
    .pricing-old{font-size:18px;color:#64748B;text-decoration:line-through;margin-bottom:20px}
    .pricing-features{text-align:left;margin:30px 0;list-style:none}
    .pricing-features li{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--light-border);color:var(--text)}
    .pricing-features li:last-child{border-bottom:none}
    .pricing-features svg{width:20px;height:20px;color:var(--success);flex-shrink:0}
    .pricing-note{font-size:14px;color:#64748B;margin-top:20px}

    .final-cta{padding:80px 0;background:linear-gradient(135deg,var(--bg),#E0F2FE)}
    .final-cta .container{max-width:700px;text-align:center}
    .final-cta h2{font-size:36px;margin-bottom:20px;color:var(--text)}
    .final-cta p{font-size:18px;color:#0C4A6E;opacity:0.8;margin-bottom:30px}

    .checkout{padding:80px 0}
    .checkout-container{max-width:500px;margin:0 auto}
    .checkout-card{background:var(--card);border-radius:20px;padding:40px;border:1px solid var(--light-border);box-shadow:0 8px 30px rgba(12,74,110,0.08)}
    .checkout-header{text-align:center;margin-bottom:30px}
    .checkout-header h2{font-size:28px;margin-bottom:8px}
    .checkout-header p{color:#64748B}
    .form-group{margin-bottom:20px}
    .form-group label{display:block;font-size:14px;font-weight:500;margin-bottom:8px;color:var(--text)}
    .form-group input{width:100%;padding:14px 16px;font-size:16px;border:2px solid var(--light-border);border-radius:10px;transition:border-color .3s;background:var(--card)}
    .form-group input:focus{outline:none;border-color:var(--secondary)}
    .order-summary{background:var(--bg);border-radius:12px;padding:20px;margin-bottom:24px}
    .order-summary-row{display:flex;justify-content:space-between;padding:8px 0;color:#64748B}
    .order-summary-row.total{border-top:2px solid var(--light-border);margin-top:12px;padding-top:12px;color:var(--text);font-weight:600;font-size:18px}
    .coupon-row{display:flex;gap:10px;margin-bottom:20px}
    .coupon-row input{flex:1}
    .coupon-row button{padding:14px 20px;background:var(--secondary);color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer}
    .checkout-btn{width:100%;padding:18px;font-size:18px;background:var(--accent);color:#fff;border:none;border-radius:12px;font-weight:700;cursor:pointer;transition:all .3s}
    .checkout-btn:hover{background:var(--accent-dark);transform:translateY(-2px)}
    .checkout-btn:disabled{opacity:0.6;cursor:not-allowed}
    .secure-note{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;font-size:13px;color:#64748B}
    .checkout-error{background:#FEE2E2;color:#DC2626;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px;display:none}
    .checkout-success{background:#DCFCE7;color:#16A34A;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px;display:none}

    .exit-popup{position:fixed;inset:0;background:rgba(12,74,110,0.8);display:none;justify-content:center;align-items:center;z-index:9999;padding:20px}
    .exit-popup.active{display:flex}
    .exit-popup-content{background:var(--card);border-radius:20px;padding:40px;max-width:450px;width:100%;text-align:center;position:relative;animation:slideUp .3s ease}
    @keyframes slideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
    .exit-popup-close{position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#64748B}
    .exit-popup h3{font-size:24px;margin-bottom:16px;color:var(--text)}
    .exit-popup p{color:#64748B;margin-bottom:20px}
    .exit-popup .price{font-family:'Manrope',sans-serif;font-size:32px;font-weight:800;color:var(--text);margin-bottom:20px}
    .exit-popup-btn{display:inline-block;padding:14px 28px;background:var(--accent);color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;text-decoration:none}

    .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer span,.pricing-timer span,.final-cta-timer span{color:#64748B;font-size:14px;font-weight:500}
    .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .summary-image{width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,rgba(249,115,22,0.1),rgba(249,115,22,0.05));border:1px solid var(--light-border);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:#64748B;font-weight:500}
    .summary-timer strong{color:var(--accent);font-weight:700}

    footer{background:var(--text);color:#fff;padding:40px 0;text-align:center}
    footer p{opacity:0.8;font-size:14px}
    footer a{color:var(--secondary);text-decoration:none}

    @media(max-width:1024px){
        .hero-grid,.checkout-grid{grid-template-columns:1fr;gap:40px}
        .hero-right{order:-1}
        .hero-sub{margin:0 auto 32px}
        .price-block{margin:0 auto 28px}
        .stats-row{justify-content:center;gap:24px}
        .features-grid,.problems-grid{grid-template-columns:1fr}
        .footer-inner{flex-direction:column;gap:16px;text-align:center}
        .checkout-grid{padding:0 16px}
        .order-summary{position:static;margin-top:32px}
        .checkout-left,.checkout-right{width:100%;padding:0}
    }
    @media(max-width:768px){
        .hero-grid{grid-template-columns:1fr;gap:24px}
        .hero{padding:100px 16px 40px}
        .hero-title{font-size:clamp(1.8rem,6vw,2.5rem)}
        .hero-sub{font-size:.9rem}
        .nav{padding:8px 16px;top:8px}
        .nav-brand span{display:none}
        .price-block{flex-wrap:wrap;padding:12px 16px;justify-content:center}
        .price-current{font-size:1.6rem}
        .cta-btn{padding:14px 24px;font-size:.9rem;width:100%;justify-content:center}
        .countdown-bar{max-width:100%}
        .timer-value{font-size:1.5rem}
        .stats-row{grid-template-columns:repeat(2,1fr);gap:12px}
        .stat-item{padding:16px 8px}
        .features-grid,.problems-grid,.testimonials-grid{grid-template-columns:1fr;gap:16px}
        .section{padding:60px 16px}
        .section-title{font-size:clamp(1.5rem,5vw,2rem)}
        .footer{padding:40px 16px 30px}
        .pricing-card{padding:32px 20px}
        .pricing-price .amount{font-size:2.2rem}
        .pricing-features li{font-size:.85rem}
        .dark-section,.features-section,.pricing-section,.proof-section,.cta-section{padding:60px 16px}
        .checkout-page{padding:20px 16px}
        .checkout-form{padding:24px 16px}
        .field-group input{padding:12px 14px;font-size:.9rem}
        .pay-btn{padding:16px;font-size:.95rem;width:100%}
        .order-summary-box{padding:20px 16px}
        .timer-sticky{padding:10px 16px;font-size:.8rem;width:calc(100% - 32px);left:16px;transform:none;bottom:12px}
        .exit-popup-box{padding:32px 20px;margin:16px}
        .exit-popup-box h2{font-size:1.4rem}
        .exit-popup-code input{font-size:1rem;padding:12px}
        .exit-popup-link{padding:14px 24px;font-size:.9rem}
    }
    @media(max-width:480px){
        .hero{padding:90px 12px 32px}
        .hero-title{font-size:1.6rem;letter-spacing:-.02em}
        .eyebrow{padding:4px 12px;font-size:.65rem}
        .price-new{font-size:2rem}
        .timer-box{padding:14px}
        .timer-value{font-size:1.3rem}
        .feature-card,.problem-card,.pricing-card,.module-card{padding:20px}
        .feature-card h3,.problem-card h3{font-size:.95rem}
        .btn-primary,.cta-btn{font-size:.9rem;padding:12px 20px}
        .strip-bar{padding:8px 12px;font-size:.7rem}
        .nav{top:0;border-radius:0}
        .section{padding:48px 12px}
        .section-title{font-size:1.4rem}
        .cta-section{padding:40px 12px;border-radius:16px}
        .cta-section h2{font-size:1.5rem}
        .pricing-card{border-radius:16px}
        .stats-row{grid-template-columns:1fr 1fr}
        .testimonial-card{padding:20px 16px}
    }
    </style>
</head>
<body>
    <?php if ($step === 'landing'): ?>
    <header>
        <div class="container">
            <a href="/" class="logo">Joala<span>Digital</span></a>
            <a href="?step=checkout" class="btn btn-secondary">Get Started</a>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <span class="eyebrow">For Shops, Clinics, Salons & Traders</span>
                <h1>Get More Customers With a Simple Digital Presence — Even With Zero Tech Skills</h1>
                <p class="hero-sub">Complete digital kit for Nigerian local businesses: Google Business setup, WhatsApp integration, social media templates & customer management</p>
                
                <div class="price-tag">
                    <span class="current">N<?php echo $productPrice; ?></span>
                    <span class="original">N<?php echo $productOldPrice; ?></span>
                    <span class="savings">Save N<?php echo $savings; ?></span>
                </div>
                
                <a href="?step=checkout" class="btn btn-primary">
                    Get Instant Access
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                
                <div class="timer" id="landingTimer">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span>Offer expires in <strong id="landingTimerDisplay">59:59</strong></span>
                </div>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>For Any Local Business</span>
                    </div>
                    <div class="stat-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Includes Google Setup</span>
                    </div>
                    <div class="stat-item">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>WhatsApp Integration</span>
                    </div>
                </div>
            </div>
            
            <div class="hero-visual">
                <svg viewBox="0 0 400 350" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="40" y="60" width="200" height="240" rx="12" fill="#FFFFFF" stroke="#BAE6FD" stroke-width="2"/>
                    <rect x="60" y="80" width="160" height="30" rx="6" fill="#0C4A6E"/>
                    <circle cx="90" cy="145" r="25" fill="#F97316"/>
                    <rect x="70" y="180" width="140" height="8" rx="4" fill="#E2E8F0"/>
                    <rect x="70" y="200" width="120" height="8" rx="4" fill="#E2E8F0"/>
                    <rect x="70" y="220" width="100" height="8" rx="4" fill="#E2E8F0"/>
                    <rect x="260" y="80" width="100" height="80" rx="8" fill="#0EA5E9" opacity="0.2"/>
                    <rect x="260" y="170" width="100" height="50" rx="8" fill="#F97316" opacity="0.2"/>
                    <rect x="260" y="230" width="100" height="50" rx="8" fill="#22C55E" opacity="0.2"/>
                    <path d="M300 100 L330 70 L360 100 L330 130 Z" fill="#F97316" opacity="0.3"/>
                    <circle cx="320" cy="200" r="20" fill="#0EA5E9" opacity="0.3"/>
                </svg>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Everything You Need to Go Digital</h2>
                <p>Six powerful tools to help your business get found online and manage customers effortlessly</p>
            </div>
            
            <div class="features-grid">
                <div class="card feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3>Google Business Profile Setup</h3>
                    <p>Get your business on Google Maps so customers can find you instantly</p>
                </div>
                
                <div class="card feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3>WhatsApp Business Integration</h3>
                    <p>Connect WhatsApp directly so customers can message you with one tap</p>
                </div>
                
                <div class="card feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3>Social Media Templates (30+)</h3>
                    <p>Ready-made posts for Instagram, Facebook & WhatsApp Status</p>
                </div>
                
                <div class="card feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3>Customer Contact System</h3>
                    <p>Keep track of your customers and send follow-up messages easily</p>
                </div>
                
                <div class="card feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    </div>
                    <h3>Digital Business Card</h3>
                    <p>Share your business info instantly via link or QR code</p>
                </div>
                
                <div class="card feature-card">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3>Setup Video Walkthrough</h3>
                    <p>Step-by-step video guide to set everything up in minutes</p>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonial">
        <div class="container">
            <div class="testimonial-box">
                <p class="testimonial-quote">"I run a small pharmacy in Lagos. After setting up my Google Business profile through this kit, I started getting calls from people who found me on Google Maps. This N12,000 kit paid for itself in one week!"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">E</div>
                    <div class="testimonial-info">
                        <div class="testimonial-name">Emeka Okonkwo</div>
                        <div class="testimonial-role">Pharmacist, Ikeja Lagos</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing">
        <div class="container">
            <div class="pricing-card featured">
                <span class="pricing-badge">Limited Time Offer</span>
                <div class="pricing-price">N<?php echo $productPrice; ?><span>/one-time</span></div>
                <div class="pricing-old">Was N<?php echo $productOldPrice; ?></div>
                
                <ul class="pricing-features">
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Google Business Profile Setup Guide
                    </li>
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        WhatsApp Business Integration Kit
                    </li>
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        30+ Social Media Templates
                    </li>
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Customer Contact System Template
                    </li>
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Digital Business Card Template
                    </li>
                    <li>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Step-by-Step Video Walkthrough
                    </li>
                </ul>
                
                <a href="?step=checkout" class="btn btn-primary" style="width:100%;justify-content:center">
                    Get Started Now
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                
                <div class="timer" id="pricingTimer" style="justify-content:center;margin-top:16px">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span>Offer expires in <strong id="pricingTimerDisplay">59:59</strong></span>
                </div>
                
                <p class="pricing-note">30-day money-back guarantee • No monthly fees • Instant download</p>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="container">
            <h2>Ready to Grow Your Business?</h2>
            <p>Join hundreds of Nigerian local businesses already getting more customers through their digital presence.</p>
            <a href="?step=checkout" class="btn btn-primary" style="padding:20px 40px;font-size:18px">
                Get Your Digital Kit Now
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <div class="timer" id="finalTimer" style="justify-content:center;margin-top:16px">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <span>Offer expires in <strong id="finalTimerDisplay">59:59</strong></span>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($step === 'checkout'): ?>
    <header>
        <div class="container">
            <a href="/" class="logo">Joala<span>Digital</span></a>
            <a href="local-business-digital-kit.php" class="btn btn-secondary">← Back</a>
        </div>
    </header>

    <section class="checkout">
        <div class="container">
            <div class="checkout-container">
                <div class="checkout-card">
                    <div class="checkout-header">
                        <h2>Complete Your Order</h2>
                        <p>Local Business Digital Kit</p>
                    </div>
                    
                    <div class="checkout-error" id="checkoutError"></div>
                    <div class="checkout-success" id="checkoutSuccess">Payment successful! Redirecting...</div>
                    
                    <form id="checkoutForm">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" placeholder="e.g., 08012345678" required>
                        </div>
                        
                        <div class="coupon-row">
                            <input type="text" id="coupon" name="coupon" placeholder="Coupon code (optional)">
                            <button type="button" id="applyCoupon">Apply</button>
                        </div>
                        
                        <div class="order-summary">
                            <div class="order-summary-row">
                                <span>Original Price</span>
                                <span>N<?php echo $productOldPrice; ?></span>
                            </div>
                            <div class="order-summary-row discount" style="display:none">
                                <span>Discount</span>
                                <span id="discountAmount">-N0</span>
                            </div>
                            <div class="order-summary-row total">
                                <span>Total</span>
                                <span id="totalAmount">N<?php echo $productPrice; ?></span>
                            </div>
                        </div>
                        
                        <button type="submit" class="checkout-btn" id="payBtn">Pay N<?php echo $productPrice; ?></button>
                        
                        <div class="secure-note">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Secure payment powered by Paystack
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="exit-popup" id="exitPopup">
        <div class="exit-popup-content">
            <button class="exit-popup-close" id="exitClose">&times;</button>
            <h3>Wait! Don't Miss Out</h3>
            <p>Get the complete Local Business Digital Kit at our special launch price.</p>
            <div class="price">N12,000 <span style="font-size:16px;text-decoration:line-through;color:#64748B">N25,000</span></div>
            <p style="font-size:14px;color:#64748B;margin-bottom:16px">Use code <strong>LAUNCH15</strong> for extra N2,000 off!</p>
            <a href="?step=checkout" class="exit-popup-btn">Claim Offer Now</a>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 Joala Digital. All rights reserved.</p>
        </div>
    </footer>

    <script>
    (function() {
        const TIMER_KEY = 'lbdkit_offer_end';
        const TOTAL_TIME = 3600000;
        
        function getTimerEnd() {
            const stored = localStorage.getItem(TIMER_KEY);
            if (stored) {
                const end = parseInt(stored);
                if (end > Date.now()) return end;
            }
            const newEnd = Date.now() + TOTAL_TIME;
            localStorage.setItem(TIMER_KEY, newEnd);
            return newEnd;
        }
        
        function updateTimers() {
            const end = getTimerEnd();
            const remaining = end - Date.now();
            if (remaining <= 0) {
                localStorage.removeItem(TIMER_KEY);
                window.location.reload();
                return;
            }
            
            const hours = Math.floor(remaining / (1000 * 60 * 60));
            const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
            
            const pad = n => n.toString().padStart(2, '0');
            const timeStr = pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
            
            document.querySelectorAll('[id$="TimerDisplay"]').forEach(el => {
                el.textContent = timeStr;
            });
        }
        
        updateTimers();
        setInterval(updateTimers, 1000);
        
        const exitPopup = document.getElementById('exitPopup');
        const exitClose = document.getElementById('exitClose');
        
        if (exitPopup && exitClose) {
            let exitShown = sessionStorage.getItem('exit_popup_shown');
            
            document.addEventListener('mouseleave', function(e) {
                if (e.clientY <= 0 && !exitShown) {
                    exitPopup.classList.add('active');
                    sessionStorage.setItem('exit_popup_shown', '1');
                    exitShown = '1';
                }
            });
            
            exitClose.addEventListener('click', function() {
                exitPopup.classList.remove('active');
            });
            
            exitPopup.addEventListener('click', function(e) {
                if (e.target === exitPopup) {
                    exitPopup.classList.remove('active');
                }
            });
        }
        
        const checkoutForm = document.getElementById('checkoutForm');
        const applyCouponBtn = document.getElementById('applyCoupon');
        
        let currentTotal = <?php echo $productPriceRaw; ?>;
        let currentDiscount = 0;
        
        if (applyCouponBtn) {
            applyCouponBtn.addEventListener('click', function() {
                const code = document.getElementById('coupon').value.trim();
                if (!code) return;
                
                if (code.toUpperCase() === 'LAUNCH15') {
                    currentDiscount = 2000;
                    updateTotal();
                    alert('Coupon applied! N2,000 discount');
                } else {
                    alert('Invalid coupon code');
                }
            });
        }
        
        function updateTotal() {
            const total = Math.max(0, currentTotal - currentDiscount);
            const totalEl = document.getElementById('totalAmount');
            const discountEl = document.querySelector('.order-summary-row.discount');
            const payBtn = document.getElementById('payBtn');
            
            if (totalEl) totalEl.textContent = 'N' + total.toLocaleString();
            if (discountEl) discountEl.style.display = currentDiscount > 0 ? 'flex' : 'none';
            if (discountEl) document.getElementById('discountAmount').textContent = '-N' + currentDiscount.toLocaleString();
            if (payBtn) payBtn.textContent = 'Pay N' + total.toLocaleString();
        }
        
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const payBtn = document.getElementById('payBtn');
                const errorEl = document.getElementById('checkoutError');
                const successEl = document.getElementById('checkoutSuccess');
                
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const coupon = document.getElementById('coupon').value.trim();
                
                if (!name || !email || !phone) {
                    errorEl.textContent = 'Please fill in all required fields.';
                    errorEl.style.display = 'block';
                    return;
                }
                
                errorEl.style.display = 'none';
                successEl.style.display = 'none';
                payBtn.disabled = true;
                payBtn.textContent = 'Processing...';
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'init_payment');
                    formData.append('name', name);
                    formData.append('email', email);
                    formData.append('phone', phone);
                    formData.append('coupon_code', coupon);
                    
                    const response = await fetch(window.location.href.split('?')[0], {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    const finalAmount = data.amount - (currentDiscount * 100);
                    
                    PaystackPop.setup({
                        key: data.paystack_key,
                        email: data.email,
                        amount: finalAmount,
                        reference: data.reference,
                        onSuccess: function(transaction) {
                            window.location.href = '?reference=' + data.reference + '&trxref=' + transaction.reference;
                        },
                        onCancel: function() {
                            payBtn.disabled = false;
                            payBtn.textContent = 'Pay N' + ((data.amount / 100) - currentDiscount).toLocaleString();
                        }
                    }).open();
                    
                    payBtn.disabled = false;
                    payBtn.textContent = 'Pay N' + ((data.amount / 100) - currentDiscount).toLocaleString();
                    
                } catch (err) {
                    errorEl.textContent = err.message || 'An error occurred. Please try again.';
                    errorEl.style.display = 'block';
                    payBtn.disabled = false;
                    payBtn.textContent = 'Pay N' + currentTotal.toLocaleString();
                }
            });
        }
    })();
    </script>
</body>
</html>