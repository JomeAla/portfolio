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
        'source' => 'done_for_you_email_automation',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(17);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 17)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 17, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'done_for_you_email_automation',
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
                        'subject' => $step->subject ?? 'Your email automation is ready',
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'Done For You Email Automation'], $step->body ?? ''),
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

    $product = Product::where('slug', 'done-for-you-email-automation')->where('is_active', true)->first();
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
        'source' => 'done_for_you_email_automation',
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
            DB::table('funnel_leads')->where('funnel_id', 17)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'done-for-you-email-automation')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Done For You Email Automation';
$productPrice = '150,000';
$productOldPrice = '150,000';
$productPriceRaw = 150000;
$productOldRaw = 150000;
$savings = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Done For You Email Automation — Built For You in 5 Days | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@400;500;600;700;800&family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --bg:#0F172A;
        --card:#1E293B;
        --text:#F8FAFC;
        --accent:#EAB308;
        --accent-dim:#B79404;
        --secondary:#334155;
        --muted:#94A3B8;
        --green:#22C55E;
    }

    html{scroll-behavior:smooth}
    body{font-family:'Jost',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

    .fw{font-family:'Epilogue',sans-serif}

    .container{width:100%;max-width:1200px;margin:0 auto;padding:0 24px}

    .btn{background:var(--accent);color:var(--bg);padding:16px 32px;border-radius:8px;font-weight:600;font-size:1rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;cursor:pointer;border:none;font-family:inherit;transition:all .2s}
    .btn:hover{background:var(--accent-dim);transform:translateY(-2px);box-shadow:0 8px 24px rgba(234,179,8,0.3)}
    .btn-large{background:var(--accent);color:var(--bg);padding:20px 40px;border-radius:10px;font-weight:700;font-size:1.25rem;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;border:none;font-family:inherit;transition:all .2s;width:100%}
    .btn-large:hover{background:var(--accent-dim);transform:translateY(-2px);box-shadow:0 12px 32px rgba(234,179,8,0.35)}

    section{padding:80px 0}

    .hero{padding:100px 0 80px;position:relative;overflow:hidden}
    .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 50% at 50% 0%,rgba(234,179,8,0.08) 0%,transparent 60%)}
    .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative}
    .hero-content{max-width:560px}
    .eyebrow{display:inline-block;background:linear-gradient(135deg,var(--accent),var(--accent-dim));color:var(--bg);padding:8px 16px;border-radius:100px;font-size:0.875rem;font-weight:600;margin-bottom:24px;text-transform:uppercase;letter-spacing:1px}
    .hero h1{font-size:3.5rem;font-weight:800;line-height:1.1;margin-bottom:20px}
    .hero-sub{font-size:1.25rem;color:var(--muted);margin-bottom:32px;max-width:480px}
    .hero-price{font-size:2.5rem;font-weight:700;color:var(--accent);margin-bottom:24px}
    .hero-price span{font-size:1.25rem;color:var(--muted);font-weight:400}
    .hero-timer{background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.2);border-radius:8px;padding:12px 20px;display:inline-flex;align-items:center;gap:16px;margin-bottom:32px}
    .hero-timer .t{display:flex;align-items:center;gap:8px;color:var(--accent)}
    .hero-timer .t span{font-weight:600}
    .hero-timer .t .num{font-family:'Epilogue',sans-serif;font-size:1.5rem;font-weight:700}

    .hero-visual{position:relative;display:flex;justify-content:center;align-items:center}
    .hero-visual svg{width:100%;max-width:480px;height:auto;filter:drop-shadow(0 20px 60px rgba(234,179,8,0.15))}
    .hero-stats{display:flex;gap:32px;margin-top:40px}
    .hero-stat{text-align:center}
    .hero-stat-value{font-family:'Epilogue',sans-serif;font-size:2rem;font-weight:700;color:var(--accent);display:block}
    .hero-stat-label{font-size:0.875rem;color:var(--muted)}

    .features{padding:80px 0;background:linear-gradient(180deg,transparent 0%,rgba(30,41,59,0.5) 100%)}
    .section-header{text-align:center;margin-bottom:60px}
    .section-eyebrow{font-size:0.875rem;color:var(--accent);text-transform:uppercase;letter-spacing:2px;font-weight:600;margin-bottom:16px}
    .section-title{font-size:2.5rem;font-weight:700;margin-bottom:16px}
    .section-sub{color:var(--muted);font-size:1.125rem;max-width:600px;margin:0 auto}

    .features-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px}
    .feature-card{background:var(--card);border:1px solid rgba(234,179,8,0.15);border-radius:16px;padding:32px 24px;text-align:center;transition:all .3s}
    .feature-card:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(0,0,0,0.3),0 0 0 1px rgba(234,179,8,0.2)}
    .feature-icon{width:64px;height:64px;background:linear-gradient(135deg,var(--accent),var(--accent-dim));border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem}
    .feature-card h3{font-size:1.25rem;margin-bottom:12px;font-weight:600}
    .feature-card p{color:var(--muted);font-size:0.95rem}

    .process{background:var(--card)}
    .process-steps{display:flex;justify-content:space-between;position:relative;max-width:900px;margin:0 auto}
    .process-steps::before{content:'';position:absolute;top:40px;left:60px;right:60px;height:2px;background:linear-gradient(90deg,var(--accent),var(--accent-dim))}
    .process-step{position:relative;text-align:center;flex:1;padding:0 20px}
    .process-step-num{width:80px;height:80px;background:var(--bg);border:2px solid var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-family:'Epilogue',sans-serif;font-size:1.75rem;font-weight:700;color:var(--accent);position:relative;z-index:1}
    .process-step h3{font-size:1.25rem;margin-bottom:8px;font-weight:600}
    .process-step p{color:var(--muted);font-size:0.9rem}
    .process-step-days{background:var(--accent);color:var(--bg);padding:4px 12px;border-radius:100px;font-size:0.75rem;font-weight:600;margin-top:12px;display:inline-block}

    .pricing{padding:80px 0}
    .pricing-card{background:var(--card);border:2px solid var(--accent);border-radius:24px;padding:48px;max-width:600px;margin:0 auto;text-align:center;position:relative;overflow:hidden}
    .pricing-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(234,179,8,0.05) 0%,transparent 50%)}
    .pricing-badge{background:var(--accent);color:var(--bg);padding:8px 20px;border-radius:100px;font-size:0.875rem;font-weight:700;display:inline-block;margin-bottom:24px}
    .pricing-title{font-size:1.75rem;font-weight:700;margin-bottom:16px}
    .pricing-price{font-family:'Epilogue',sans-serif;font-size:3.5rem;font-weight:800;color:var(--accent);margin-bottom:8px}
    .pricing-price span{font-size:1.25rem;color:var(--muted)}
    .pricing-old{text-decoration:line-through;color:var(--muted);font-size:1.25rem;margin-bottom:24px}
    .pricing-features{list-style:none;margin:32px 0;text-align:left;max-width:360px;margin-left:auto;margin-right:auto}
    .pricing-features li{padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.05);display:flex;align-items:center;gap:12px}
    .pricing-features li::before{content:'✓';color:var(--accent);font-weight:700}
    .pricing-countdown{background:rgba(234,179,8,0.1);border-radius:12px;padding:24px;margin-top:32px}
    .timer-row{display:flex;justify-content:center;gap:16px;margin-bottom:16px}
    .c-t{text-align:center}
    .c-t .num{font-family:'Epilogue',sans-serif;font-size:2rem;font-weight:700;color:var(--accent);display:block}
    .c-t .lbl{font-size:0.75rem;color:var(--muted);text-transform:uppercase}
    .urgency{color:var(--accent);font-weight:600;margin-bottom:12px}
    .guarantee{color:var(--muted);font-size:0.9rem}

    .proof-section{background:var(--card);padding:60px 0}
    .proof-inner{text-align:center;max-width:700px;margin:0 auto}
    .proof-quote{font-size:1.5rem;font-style:italic;color:var(--text);margin-bottom:32px;line-height:1.6}
    .proof-author{display:flex;align-items:center;justify-content:center;gap:16px}
    .proof-avatar{width:56px;height:56px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem}
    .proof-name{font-weight:700;font-size:1.125rem}
    .proof-title{color:var(--muted);font-size:0.9rem}

    .cta-section{background:linear-gradient(180deg,var(--card) 0%,var(--bg) 100%);text-align:center;padding:100px 0}
    .cta-inner{max-width:600px;margin:0 auto}
    .cta-section h2{font-size:2.5rem;font-weight:700;margin-bottom:16px}
    .cta-section p{color:var(--muted);font-size:1.125rem;margin-bottom:32px}
    .cta-btn-center{background:var(--accent);color:var(--bg);padding:20px 48px;border-radius:10px;font-weight:700;font-size:1.25rem;text-decoration:none;display:inline-flex;align-items:center;gap:12px;transition:all .2s}
    .cta-btn-center:hover{background:var(--accent-dim);transform:translateY(-2px);box-shadow:0 12px 32px rgba(234,179,8,0.35)}
    .cta-arrow{transition:transform .2s}
    .cta-btn-center:hover .cta-arrow{transform:translateX(4px)}
    .cta-countdown{margin-top:24px;display:flex;justify-content:center}
    .countdown-timer{display:flex;gap:12px}
    .countdown-sub-label{color:var(--muted);margin-top:8px;font-size:0.875rem}

    .checkout{padding:60px 0;min-height:100vh}
    .checkout-card{background:var(--card);border:1px solid var(--secondary);border-radius:20px;padding:40px;max-width:520px;margin:0 auto}
    .checkout-title{font-size:1.75rem;font-weight:700;margin-bottom:8px;text-align:center}
    .checkout-sub{color:var(--muted);text-align:center;margin-bottom:32px}
    .form-group{margin-bottom:20px}
    .form-label{display:block;margin-bottom:8px;font-weight:500;font-size:0.9rem}
    .form-input{width:100%;padding:14px 16px;border-radius:8px;border:1px solid var(--secondary);background:var(--bg);color:var(--text);font-size:1rem;font-family:inherit;transition:border-color .2s}
    .form-input:focus{outline:none;border-color:var(--accent)}
    .order-summary{background:var(--bg);border-radius:12px;padding:24px;margin-bottom:24px}
    .order-row{display:flex;justify-content:space-between;padding:8px 0}
    .order-total{border-top:1px solid var(--secondary);margin-top:12px;padding-top:12px;font-weight:700;font-size:1.25rem;color:var(--accent)}
    .coupon-row{display:flex;gap:12px;margin-bottom:20px}
    .coupon-input{flex:1;padding:12px;border-radius:8px;border:1px solid var(--secondary);background:var(--bg);color:var(--text);font-family:inherit}
    .coupon-btn{padding:12px 20px;background:var(--secondary);color:var(--text);border:none;border-radius:8px;cursor:pointer;font-weight:600;font-family:inherit}
    .coupon-applied{border-color:var(--green);background:rgba(34,197,94,0.1);padding:12px;border-radius:8px;color:var(--green);display:flex;align-items:center;justify-content:space-between}
    .payment-info{font-size:0.85rem;color:var(--muted);margin-bottom:24px}

    .popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;visibility:hidden;transition:all .3s}
    .popup-overlay.show{opacity:1;visibility:visible}
    .popup-box{background:var(--card);border:2px solid var(--accent);border-radius:24px;padding:40px;max-width:440px;text-align:center;position:relative;transform:scale(0.9);transition:transform .3s}
    .popup-overlay.show .popup-box{transform:scale(1)}
    .popup-close{position:absolute;top:16px;right:16px;background:none;border:none;color:var(--muted);font-size:2rem;cursor:pointer;line-height:1}
    .popup-icon{font-size:3rem;margin-bottom:16px}
    .popup-title{font-size:1.75rem;font-weight:700;margin-bottom:12px;color:var(--accent)}
    .popup-desc{color:var(--muted);margin-bottom:24px}
    .popup-code{display:flex;align-items:center;justify-content:center;gap:12px;background:var(--bg);border-radius:8px;padding:16px;margin-bottom:12px}
    .popup-code span{font-family:'Epilogue',sans-serif;font-size:1.5rem;font-weight:700;color:var(--accent)}
    .popup-code button{background:var(--accent);color:var(--bg);border:none;padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer;font-family:inherit}
    .popup-note{color:var(--muted);font-size:0.9rem;margin-bottom:8px}
    .popup-savings{color:var(--green);font-weight:600;margin-bottom:24px}
    .popup-cta{background:var(--accent);color:var(--bg);padding:16px 32px;border-radius:8px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
    .popup-timer{color:var(--muted);margin-top:16px;font-size:0.9rem}

    .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--muted);font-size:14px;font-weight:500}
    .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .summary-image{width:80px;height:80px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-dim));display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--muted);font-weight:500}
    .summary-timer strong{color:var(--accent);font-weight:700}

    .footer{background:var(--card);padding:40px 0;border-top:1px solid var(--secondary)}
    .footer-inner{display:flex;justify-content:space-between;align-items:center}
    .footer-brand{color:var(--muted);font-size:0.9rem}
    .footer-links{display:flex;gap:24px}
    .footer-links a{color:var(--muted);text-decoration:none;font-size:0.9rem;transition:color .2s}
    .footer-links a:hover{color:var(--accent)}

    .fi{color:var(--green);margin-right:8px}

    @media(max-width:1024px){
        .hero-grid,.checkout-grid{grid-template-columns:1fr;gap:40px}
        .hero-right{order:-1}
        .features-grid{grid-template-columns:repeat(2,1fr)}
        .checkout-grid{grid-template-columns:1fr}
        .checkout-right{position:static;margin-top:32px}
        .module-list{gap:12px}
        .module-item{padding:16px}
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
        .timer-value{font-size:1.5rem}
        .stats-row{grid-template-columns:repeat(2,1fr);gap:12px}
        .features-grid{grid-template-columns:1fr;gap:16px}
        .section{padding:60px 16px}
        .section-title{font-size:clamp(1.5rem,5vw,2rem)}
        .footer{padding:40px 16px 30px}
        .pricing-card{padding:32px 20px}
        .pricing-price .amount{font-size:2.2rem}
        .module-list{gap:10px}
        .module-item{flex-direction:column;gap:6px}
        .module-num{width:28px;height:28px;font-size:.8rem}
        .module-content h4{font-size:.95rem}
        .cta-section{padding:60px 16px}
        .checkout-page{padding:20px 16px}
        .checkout-form{padding:24px 16px}
        .field-group input{padding:12px 14px;font-size:.9rem}
        .pay-btn{padding:16px;font-size:.95rem;width:100%}
        .timer-sticky{padding:10px 16px;font-size:.8rem;width:calc(100% - 32px);left:16px;transform:none;bottom:12px}
        .exit-popup-box{padding:32px 20px;margin:16px}
        .exit-popup-box h2{font-size:1.4rem}
        .exit-code-wrap input{font-size:1rem;padding:12px}
        .exit-link{padding:14px 24px;font-size:.9rem}
    }
    @media(max-width:480px){
        .hero{padding:90px 12px 32px}
        .hero-title{font-size:1.6rem;letter-spacing:-.02em}
        .eyebrow{padding:4px 12px;font-size:.65rem}
        .price-new{font-size:2rem}
        .timer-box{padding:14px}
        .timer-value{font-size:1.3rem}
        .feature-card,.pricing-card,.module-item{padding:20px}
        .feature-card h3{font-size:.95rem}
        .btn-primary,.cta-btn{font-size:.9rem;padding:12px 20px}
        .strip-bar{padding:8px 12px;font-size:.7rem}
        .nav{top:0;border-radius:0}
        .section{padding:48px 12px}
        .section-title{font-size:1.4rem}
        .cta-section{padding:40px 12px;border-radius:16px}
        .cta-section h2{font-size:1.5rem}
        .pricing-card{border-radius:16px}
        .stats-row{grid-template-columns:1fr 1fr}
    }
    </style>
</head>
<body>

<?php if ($step !== 'checkout'): ?>
<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <span class="eyebrow">Done For You Service</span>
                <h1 class="fw">Your Email Marketing System — Built For You, Delivered in 5 Days</h1>
                <p class="hero-sub">We build your complete email automation system from scratch: list setup, sequences, integrations, and launch — you just send</p>
                <div class="hero-price fw">N150,000 <span>done-for-you service</span></div>
                <a href="?step=checkout" class="btn">Get Started Now <span>→</span></a>
                <div class="hero-timer">
                    <div class="t"><span class="num" id="h1">00</span><span class="lbl">:</span><span class="num" id="m1">00</span><span class="lbl">:</span><span class="num" id="s1">00</span></div>
                    <span style="color:var(--muted)">24-hour window</span>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-value">5 Days</span>
                        <span class="hero-stat-label">Delivered in 5 Days</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-value">100%</span>
                        <span class="hero-stat-label">Custom Built For You</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-value">30 Days</span>
                        <span class="hero-stat-label">Support Included</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <svg viewBox="0 0 400 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="40" y="60" width="320" height="200" rx="16" fill="#1E293B" stroke="#EAB308" stroke-width="2"/>
                    <rect x="60" y="80" width="280" height="30" rx="6" fill="#334155"/>
                    <rect x="60" y="120" width="180" height="12" rx="4" fill="#334155"/>
                    <rect x="60" y="140" width="220" height="8" rx="4" fill="#334155"/>
                    <rect x="60" y="156" width="160" height="8" rx="4" fill="#334155"/>
                    <circle cx="200" cy="200" r="40" fill="#EAB308" fill-opacity="0.2" stroke="#EAB308" stroke-width="2"/>
                    <path d="M185 200 L195 210 L215 190" stroke="#EAB308" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="140" y="240" width="120" height="40" rx="8" fill="#EAB308"/>
                    <text x="200" y="265" font-family="system-ui" font-size="14" font-weight="bold" fill="#0F172A" text-anchor="middle">AUTOMATED</text>
                </svg>
            </div>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <div class="section-header">
            <div class="section-eyebrow">What You Get</div>
            <h2 class="section-title fw">Complete Email Automation System</h2>
            <p class="section-sub">Everything you need to run professional email marketing — built for you</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">✉️</div>
                <h3 class="fw">Custom Email Sequences</h3>
                <p>15+ professionally written email sequences tailored to your business</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <h3 class="fw">Full Autoresponder Setup</h3>
                <p>Complete email service configuration and automation workflow setup</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3 class="fw">List Segmentation & Tagging</h3>
                <p>Smart subscriber categorization to send the right message to the right people</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔗</div>
                <h3 class="fw">Integration With Your Tools</h3>
                <p>Connect your CRM, landing pages, and marketing tools seamlessly</p>
            </div>
        </div>
    </div>
</section>

<section class="process">
    <div class="container">
        <div class="section-header">
            <div class="section-eyebrow">How It Works</div>
            <h2 class="section-title fw">5-Day Delivery Process</h2>
            <p class="section-sub">From kickoff to launch — we handle everything</p>
        </div>
        <div class="process-steps">
            <div class="process-step">
                <div class="process-step-num">1</div>
                <h3 class="fw">Discovery Call</h3>
                <p>We learn your business, audience, and goals</p>
                <span class="process-step-days">Day 1</span>
            </div>
            <div class="process-step">
                <div class="process-step-num">2</div>
                <h3 class="fw">Strategy Build</h3>
                <p>We design your automation roadmap</p>
                <span class="process-step-days">Day 2</span>
            </div>
            <div class="process-step">
                <div class="process-step-num">3</div>
                <h3 class="fw">Build & Configure</h3>
                <p>We set up sequences, forms, and integrations</p>
                <span class="process-step-days">Days 3-4</span>
            </div>
            <div class="process-step">
                <div class="process-step-num">4</div>
                <h3 class="fw">Launch & Handover</h3>
                <p>You go live with full training included</p>
                <span class="process-step-days">Day 5</span>
            </div>
        </div>
    </div>
</section>

<section class="pricing">
    <div class="container">
        <div class="pricing-card">
            <span class="pricing-badge fw">Done For You Service</span>
            <h3 class="pricing-title fw">Complete Email Automation System</h3>
            <div class="pricing-price fw">N150,000</div>
            <div class="pricing-old">N150,000</div>
            <ul class="pricing-features">
                <li>15+ Custom Email Sequences</li>
                <li>Full Autoresponder Setup</li>
                <li>List Segmentation & Tagging</li>
                <li>Tool Integrations (CRM, Landing Pages)</li>
                <li>5-Day Delivery</li>
                <li>30-Day Post-Launch Support</li>
            </ul>
            <a href="?step=checkout" class="btn-large">Get Started Now →</a>
            <div class="pricing-countdown">
                <div class="timer-row">
                    <div class="c-t"><span class="num" id="h2">00</span><span class="lbl">Hrs</span></div>
                    <div class="c-t"><span class="num" id="m2">00</span><span class="lbl">Min</span></div>
                    <div class="c-t"><span class="num" id="s2">00</span><span class="lbl">Sec</span></div>
                </div>
                <div class="urgency">⏰ Offer expires in the next 24 hours</div>
                <div class="guarantee">🔒 30-day money-back guarantee — no questions asked</div>
            </div>
        </div>
    </div>
</section>

<section class="proof-section">
    <div class="proof-inner">
        <div class="section-eyebrow" style="justify-content:center;color:#EAB308">What Clients Say</div>
        <p class="proof-quote">"They built my entire email system in 5 days. I went from zero to having professional automated sequences that actually convert. Worth every naira."</p>
        <div class="proof-author">
            <div class="proof-avatar">👤</div>
            <div class="proof-info">
                <div class="proof-name">Adesuwa Okonkwo</div>
                <div class="proof-title">Digital Marketer, Lagos</div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-inner">
        <h2 class="fw">Ready to Automate Your<br>Email Marketing?</h2>
        <p>Join entrepreneurs who've transformed their business with done-for-you email automation.</p>
        <a href="?step=checkout" class="cta-btn-center">
            Get Started for N150,000
            <span class="cta-arrow">→</span>
        </a>
        <div class="cta-countdown">
            <div class="countdown-timer">
                <div class="c-t"><span class="num" id="h3">00</span><span class="lbl">Hrs</span></div>
                <div class="c-t"><span class="num" id="m3">00</span><span class="lbl">Min</span></div>
                <div class="c-t"><span class="num" id="s3">00</span><span class="lbl">Sec</span></div>
            </div>
        </div>
        <div class="countdown-sub-label">⏰ Offer expires in</div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">© 2026 Joala Digital. All rights reserved.</div>
            <div class="footer-links">
                <a href="/privacy">Privacy</a>
                <a href="/refund">Refund</a>
                <a href="/contact">Support</a>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

<?php if ($step === 'checkout'): ?>
<section class="checkout">
    <div class="container">
        <div class="checkout-card">
            <h2 class="checkout-title fw">Complete Your Order</h2>
            <p class="checkout-sub">Done For You Email Automation</p>
            <form id="checkoutForm">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-input" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="Enter your email" required value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-input" placeholder="Enter your phone number" required>
                </div>
                <div class="order-summary">
                    <div class="order-row">
                        <span>Done For You Email Automation</span>
                        <span>N150,000</span>
                    </div>
                    <div class="order-row">
                        <span>Discount</span>
                        <span style="color:var(--green)">-N0</span>
                    </div>
                    <div class="order-row order-total">
                        <span>Total</span>
                        <span>N150,000</span>
                    </div>
                </div>
                <div class="coupon-row" id="couponRow">
                    <input type="text" id="couponCode" class="coupon-input" placeholder="Coupon code">
                    <button type="button" id="applyCoupon" class="coupon-btn">Apply</button>
                </div>
                <input type="hidden" id="couponApplied" name="coupon_code" value="">
                <p class="payment-info">🔒 Secure payment powered by Paystack</p>
                <button type="submit" class="btn-large" id="payBtn">Pay N150,000 →</button>
            </form>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">© 2026 Joala Digital. All rights reserved.</div>
            <div class="footer-links">
                <a href="/privacy">Privacy</a>
                <a href="/refund">Refund</a>
                <a href="/contact">Support</a>
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Exit Intent Popup -->
<div class="popup-overlay" id="exitPopup">
    <div class="popup-box">
        <button class="popup-close" id="popupClose">×</button>
        <div class="popup-icon">🔥</div>
        <h2 class="popup-title fw">Wait — Get 15% Off!</h2>
        <p class="popup-desc">Don't leave empty-handed! Use this exclusive discount code to get the Done For You Email Automation at a lower price.</p>
        <div class="popup-code">
            <span id="popupCodeText">LAUNCH15</span>
            <button onclick="copyPopupCode()">Copy</button>
        </div>
        <p class="popup-note">Copy the code and apply it at checkout to save N22,500</p>
        <p class="popup-savings">✓ You save N22,500 on your order</p>
        <a href="?step=checkout" class="popup-cta">Claim My 15% Discount →</a>
        <div class="popup-timer">Offer expires in <span id="popupTimer">05:00</span></div>
    </div>
</div>

<script src="https://js.paystack.co/v2/inline.js"></script>
<script>
(function() {
    function pad(n) { return String(n).padStart(2, '0'); }

    var timerEnd = new Date(Date.now() + 3600000);
    function tickTimers() {
        var now = new Date();
        var diff = Math.max(0, timerEnd - now);
        var h = Math.floor(diff / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        var parts = [['h1','m1','s1'],['h2','m2','s2'],['h3','m3','s3']];
        parts.forEach(function(p) {
            var he = document.getElementById(p[0]);
            if (he) he.textContent = pad(h);
            var me = document.getElementById(p[1]);
            if (me) me.textContent = pad(m);
            var se = document.getElementById(p[2]);
            if (se) se.textContent = pad(s);
        });
        requestAnimationFrame(tickTimers);
    }
    tickTimers();

    var popupShown = false;
    document.addEventListener('mouseleave', function(e) {
        if (popupShown || sessionStorage.getItem('dfyPopupSeen')) return;
        if (e.clientY < 5) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
            sessionStorage.setItem('dfyPopupSeen', '1');
            startPopupTimer();
        }
    });

    document.getElementById('popupClose').addEventListener('click', function() {
        document.getElementById('exitPopup').classList.remove('show');
    });

    document.getElementById('exitPopup').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('show');
    });

    window.copyPopupCode = function() {
        var code = document.getElementById('popupCodeText').textContent;
        navigator.clipboard.writeText(code).then(function() {
            var btn = document.querySelector('.popup-code button');
            btn.textContent = 'Copied!';
            setTimeout(function() { btn.textContent = 'Copy'; }, 2000);
        });
    };

    var popupTimeLeft = 5 * 60;
    function startPopupTimer() {
        setInterval(function() {
            popupTimeLeft--;
            if (popupTimeLeft <= 0) popupTimeLeft = 0;
            var m = Math.floor(popupTimeLeft / 60);
            var s = popupTimeLeft % 60;
            var el = document.getElementById('popupTimer');
            if (el) el.textContent = pad(m) + ':' + pad(s);
        }, 1000);
    }

    var checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        var basePath = window.location.pathname;

        document.getElementById('applyCoupon').addEventListener('click', function() {
            var code = document.getElementById('couponCode').value.trim().toUpperCase();
            if (!code) return;
            
            fetch(basePath, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=validate_coupon&code=' + encodeURIComponent(code)
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.valid) {
                    var row = document.getElementById('couponRow');
                    row.innerHTML = '<div class="coupon-applied"><span>' + code + '</span><span style="color:var(--green)">-' + d.discount + '</span></div>';
                    document.getElementById('couponApplied').value = code;
                    var summary = document.querySelector('.order-summary');
                    var rows = summary.querySelectorAll('.order-row');
                    rows[1].querySelector('span:last-child').textContent = '-' + d.discount;
                    var total = summary.querySelector('.order-total span:last-child');
                    total.textContent = d.final;
                    document.getElementById('payBtn').textContent = 'Pay ' + d.final + ' →';
                } else {
                    alert('Invalid coupon code');
                }
            })
            .catch(function() { alert('Error validating coupon'); });
        });

        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            var formData = new FormData(checkupForm);
            formData.append('action', 'init_payment');

            fetch(basePath, {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.error) {
                    alert(res.error);
                    btn.disabled = false;
                    btn.textContent = 'Pay N150,000 →';
                    return;
                }

                var payload = {
                    key: res.paystack_key,
                    email: res.email,
                    amount: res.amount,
                    reference: res.reference,
                    onSuccess: function() {
                        window.location.href = basePath + '?step=checkout&reference=' + res.reference + '&trxref=' + res.reference;
                    },
                    onCancel: function() {
                        btn.disabled = false;
                        btn.textContent = 'Pay N150,000 →';
                    }
                };

                if (window.PaystackPop) {
                    var handler = PaystackPop.setup(payload);
                    handler.open();
                }
            })
            .catch(function(err) {
                alert('Payment initialization failed');
                btn.disabled = false;
                btn.textContent = 'Pay N150,000 →';
            });
        });
    }
})();
</script>

</body>
</html>