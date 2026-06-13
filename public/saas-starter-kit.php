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
        'source' => 'saas_sales_page',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(4);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 4)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 4, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'saas_sales_page',
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

                foreach ($steps as $stepItem) {
                    $delayMin = ($stepItem->delay_days ?? 0) * 24 * 60 + ($stepItem->delay_hours ?? 0) * 60;
                    EmailQueue::create([
                        'lead_id' => $lead->id,
                        'sequence_id' => $funnel->welcome_sequence_id,
                        'subject' => $stepItem->subject ?? 'Your download is ready',
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'SaaS Starter Kit'], $stepItem->body ?? ''),
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

    $product = Product::where('slug', 'saas-starter-kit')->where('is_active', true)->first();
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
        'source' => 'saas_sales_page',
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
            DB::table('funnel_leads')->where('funnel_id', 4)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'saas-starter-kit')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'SaaS Starter Kit';
$productPrice = $product ? number_format((float)($product->sale_price ?? $product->price), 0) : '45,000';
$productOldPrice = $product ? number_format((float)$product->price, 0) : '85,000';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 45000;
$productOldRaw = $product ? (float)$product->price : 85000;
$savings = number_format($productOldRaw - $productPriceRaw, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Launch Your SaaS in Days | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
    :root{--bg:#0F172A;--card:#1E293B;--card2:#334155;--text:#F8FAFC;--text2:#94A3B8;--blue:#3B82F6;--violet:#8B5CF6;--green:#10B981;--border:#334155;--muted:#94A3B8;--accent:#3B82F6}
    html{scroll-behavior:smooth}
    body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}
    .noise{position:fixed;inset:0;z-index:9999;pointer-events:none;opacity:.03;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 300 300' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
    ::selection{background:var(--blue);color:#fff}
    @keyframes fadeUp{to{opacity:1;transform:translateY(0)}}
    @keyframes pulseDot{0%,100%{opacity:1}50%{opacity:.4}}
    @keyframes glow{0%,100%{box-shadow:0 0 20px rgba(59,130,246,.3)}50%{box-shadow:0 0 40px rgba(59,130,246,.6)}}
    .nav{position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:100;background:rgba(15,23,42,.8);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:100px;padding:12px 28px;display:flex;align-items:center;gap:8px;transition:all .5s}
    .nav-brand{font-weight:700;font-size:.9rem;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:8px;font-family:'Outfit',sans-serif}
    .nav-brand svg{border-radius:6px}
    .hero{min-height:100dvh;display:flex;align-items:center;padding:140px 40px 80px;position:relative;overflow:hidden}
    .hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse 60% 50% at 50% 0%,rgba(59,130,246,.15) 0%,transparent 70%),radial-gradient(ellipse 40% 40% at 80% 50%,rgba(139,92,246,.1) 0%,transparent 60%)}
    .hero-grid{max-width:1200px;margin:0 auto;width:100%;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;position:relative;z-index:1}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--blue);color:#fff;font-size:.7rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;padding:6px 14px 6px 10px;border-radius:100px;margin-bottom:24px;opacity:0;transform:translateY(20px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .2s forwards}
    .eyebrow .dot{width:6px;height:6px;background:rgba(255,255,255,.5);border-radius:50%;animation:pulseDot 2s infinite}
    .hero-title{font-family:'Outfit',sans-serif;font-size:clamp(2.4rem,5vw,4rem);font-weight:800;line-height:1.08;letter-spacing:-.03em;margin-bottom:24px;opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .3s forwards}
    .hero-title .accent{color:var(--blue)}
    .hero-sub{font-size:1rem;color:var(--text2);max-width:480px;line-height:1.75;margin-bottom:36px;opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .4s forwards}
    .price-block{display:inline-flex;align-items:center;gap:12px;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:16px 24px;margin-bottom:32px;opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .5s forwards}
    .price-current{font-family:'Outfit',sans-serif;font-size:2.2rem;font-weight:800;color:var(--text)}
    .price-orig{font-size:1.1rem;color:var(--text2);text-decoration:line-through}
    .price-badge{background:var(--blue);color:#fff;padding:4px 12px;border-radius:8px;font-size:.75rem;font-weight:700}
    .cta-btn{margin:0 auto;position:relative;z-index:100;display:flex;align-items:center;gap:12px;background:var(--blue);color:#fff;font-weight:700;font-size:1rem;padding:18px 32px;border-radius:14px;text-decoration:none;transition:all .4s cubic-bezier(.32,.72,0,1);opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .6s forwards;pointer-events:auto}
    .cta-btn:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(59,130,246,.4)}
    .cta-arrow{width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:all .3s}
    .countdown-bar{position:relative;margin-top:20px;opacity:0;animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .7s forwards;align-items:center;display:flex;flex-direction:column;gap:0;z-index:10;width:100%;max-width:360px}
    .cta-group{display:flex;flex-direction:column;align-items:center;width:100%;max-width:360px}
    .countdown-sub-label{font-size:.8rem;color:var(--blue);font-weight:700;text-transform:uppercase;letter-spacing:.08em;font-family:'Outfit',sans-serif;margin-top:12px;opacity:0;animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .8s forwards;text-align:center;width:100%}
    .countdown-timer{display:flex;gap:8px}
    .c-t{background:var(--card);border:1px solid var(--blue);border-radius:10px;padding:10px 14px;text-align:center;min-width:68px;box-shadow:0 0 20px rgba(59,130,246,.2);animation:glow 2s ease-in-out infinite}
    .c-t .num{font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:var(--blue);display:block;line-height:1}
    .c-t .lbl{font-size:.6rem;color:var(--text2);text-transform:uppercase;letter-spacing:.1em;margin-top:3px;display:block}
.hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
.hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--muted);font-size:14px;font-weight:500}
.hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
.summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--muted);font-weight:500}
.summary-timer strong{color:var(--accent);font-weight:700}
.summary-image{width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,rgba(59,130,246,.15),rgba(139,92,246,.1));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;overflow:hidden}
.summary-image img{width:100%;height:100%;object-fit:cover}
    .stats-row{display:flex;gap:32px;margin-top:40px;padding-top:28px;border-top:1px solid var(--border);opacity:0;animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .8s forwards}
    .stats-item .val{font-family:'Outfit',sans-serif;font-size:1.5rem;font-weight:800;color:var(--text);display:block}
    .stats-item .lbl{font-size:.75rem;color:var(--text2);margin-top:4px}
    .hero-right{position:relative;opacity:0;transform:translateY(40px);animation:fadeUp 1s cubic-bezier(.32,.72,0,1) .4s forwards}
    .hero-image{width:100%;aspect-ratio:4/3;border-radius:24px;background:linear-gradient(135deg,#1E293B 0%,#334155 100%);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;box-shadow:0 30px 60px rgba(0,0,0,.4)}
    .hero-image::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 50%,rgba(59,130,246,.15) 0%,transparent 70%)}
    .hero-image img{width:100%;height:100%;object-fit:cover;display:block}
    .hero-placeholder{font-size:5rem;filter:grayscale(1) opacity(.3)}
    .section{padding:100px 40px;max-width:1200px;margin:0 auto}
    .section-eyebrow{font-family:'Outfit',sans-serif;font-size:.7rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--blue);margin-bottom:16px;display:flex;align-items:center;gap:12px}
    .section-eyebrow::before{content:'';width:20px;height:2px;background:var(--blue);border-radius:2px}
    .section h2{font-family:'Outfit',sans-serif;font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;line-height:1.15;margin-bottom:16px;letter-spacing:-.02em}
    .section p{font-size:.95rem;color:var(--text2);max-width:560px;line-height:1.7;margin-bottom:48px}
    .features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:48px}
    .feature-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:28px;transition:all .5s}
    .feature-card:hover{border-color:var(--blue);transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.3)}
    .feature-icon{width:44px;height:44px;background:rgba(59,130,246,.12);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:16px;filter:hue-rotate(200deg)}
    .feature-card h4{font-family:'Outfit',sans-serif;font-size:.9rem;font-weight:700;margin-bottom:8px}
    .feature-card p{font-size:.8rem;color:var(--text2);line-height:1.6}
    .pricing-section{background:var(--card);padding:100px 40px;position:relative;overflow:hidden}
    .pricing-inner{max-width:700px;margin:0 auto;position:relative;z-index:1;text-align:center}
    .pricing-card{background:var(--bg);border:1px solid var(--border);border-radius:28px;padding:48px;text-align:center;box-shadow:0 40px 80px rgba(0,0,0,.3)}
    .pricing-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.3);border-radius:100px;padding:6px 16px;font-size:.75rem;font-weight:700;color:var(--violet);margin-bottom:16px}
    .pricing-title{font-family:'Outfit',sans-serif;font-size:1.6rem;font-weight:700;margin-bottom:16px}
    .pricing-price{margin:16px 0}
    .pricing-price .strike{font-size:1.4rem;opacity:.35;text-decoration:line-through;margin-right:10px}
    .pricing-price .amount{font-family:'Outfit',sans-serif;font-size:3.5rem;font-weight:800;color:var(--text)}
    .pricing-features{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;margin:24px 0;text-align:left}
    .pricing-features ul{list-style:none;display:flex;flex-direction:column;gap:12px}
    .pricing-features li{display:flex;align-items:center;gap:10px;font-size:.9rem}
    .pricing-features li .fi{color:var(--green);font-size:1rem;font-weight:700}
    .btn-large{display:inline-flex;align-items:center;justify-content:center;gap:12px;background:var(--blue);color:#fff;font-size:1.1rem;font-weight:700;padding:22px 44px;border-radius:16px;border:none;cursor:pointer;width:100%;transition:all .5s cubic-bezier(.32,.72,0,1);font-family:'Inter',sans-serif;position:relative;overflow:hidden;box-shadow:0 0 20px rgba(59,130,246,.3)}
    .btn-large:hover{transform:translateY(-3px);box-shadow:0 20px 40px rgba(59,130,246,.4)}
    .pricing-timer{margin-top:20px;display:flex;flex-direction:column;align-items:center;gap:8px}
    .urgency{font-size:.8rem;color:var(--blue);font-weight:700;margin-top:8px}
    .guarantee{font-size:.75rem;color:var(--text2);margin-top:12px}
    .proof-section{background:var(--bg);padding:100px 40px;text-align:center;border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
    .proof-inner{max-width:800px;margin:0 auto}
    .proof-quote{font-family:'Outfit',sans-serif;font-size:clamp(1.4rem,3vw,2rem);font-weight:600;line-height:1.35;margin-bottom:32px;color:var(--text)}
    .proof-author{display:flex;align-items:center;justify-content:center;gap:16px}
    .proof-avatar{width:48px;height:48px;border-radius:50%;background:var(--violet);display:flex;align-items:center;justify-content:center;font-size:1.2rem}
    .proof-info{text-align:left}
    .proof-name{font-weight:700;font-size:.9rem}
    .proof-title{font-size:.78rem;color:var(--text2);margin-top:2px}
    .cta-section{padding:100px 40px;background:var(--card);text-align:center;border-top:1px solid var(--border)}
    .cta-inner{max-width:640px;margin:0 auto}
    .cta-section h2{font-family:'Outfit',sans-serif;font-size:clamp(1.8rem,4vw,2.6rem);font-weight:800;margin-bottom:16px;letter-spacing:-.02em}
    .cta-section p{font-size:1rem;color:var(--text2);margin-bottom:36px;line-height:1.7}
    .cta-btn-center{display:inline-flex;align-items:center;gap:12px;background:var(--blue);color:#fff;font-weight:700;font-size:1rem;padding:18px 36px;border-radius:14px;text-decoration:none;transition:all .4s cubic-bezier(.32,.72,0,1);text-align:center}
    .cta-btn-center:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(59,130,246,.4)}
    .cta-countdown{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px}
    .cta-countdown .c-t{min-width:64px;padding:8px 12px}
    .cta-countdown .c-t .num{font-size:1.4rem}
    .footer{padding:48px 40px 36px;background:var(--bg);border-top:1px solid var(--border)}
    .footer-inner{max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center}
    .footer-brand{font-family:'Outfit',sans-serif;font-size:.85rem;font-weight:700}
    .footer-links{display:flex;gap:24px}
    .footer-links a{color:var(--text2);text-decoration:none;font-size:.78rem;transition:color .3s}
    .footer-links a:hover{color:var(--text)}
    .checkout-wrap{padding:120px 0;min-height:100dvh}
    .checkout-grid{max-width:1000px;margin:0 auto;padding:0 40px;display:grid;grid-template-columns:1fr 360px;gap:48px;align-items:start}
    .checkout-left h1{font-family:'Outfit',sans-serif;font-size:2.2rem;font-weight:800;margin-bottom:8px;letter-spacing:-.02em}
    .checkout-left .sub{font-size:.9rem;color:var(--text2);margin-bottom:36px}
    .form-card{background:var(--card);border:1px solid var(--border);border-radius:24px;padding:32px}
    .field-group{margin-bottom:20px}
    .field-group label{display:block;font-size:.78rem;font-weight:600;color:var(--text2);margin-bottom:8px;letter-spacing:.03em}
    .field-group input{width:100%;background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px 18px;font-size:.95rem;color:var(--text);font-family:'Inter',sans-serif;transition:all .4s;outline:none}
    .field-group input::placeholder{color:var(--text2);opacity:.5}
    .field-group input:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(59,130,246,.1)}
    .coupon-row{display:flex;gap:10px;margin-bottom:16px}
    .coupon-row input{flex:1;background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px 18px;font-size:.9rem;color:var(--text);font-family:'Inter',sans-serif;outline:none}
    .coupon-row button{background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px 20px;font-size:.82rem;font-weight:700;color:var(--text2);cursor:pointer;font-family:'Inter',sans-serif;transition:all .3s;white-space:nowrap}
    .coupon-row button:hover{border-color:var(--blue);color:var(--blue)}
    .coupon-msg{font-size:.82rem;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:none}
    .coupon-msg.success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);color:var(--green);display:block}
    .coupon-msg.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#f87171;display:block}
    .pay-btn{width:100%;background:var(--blue);color:#fff;border:none;border-radius:16px;padding:22px;font-size:1rem;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;transition:all .4s;display:flex;align-items:center;justify-content:center;gap:10px;position:relative;overflow:hidden}
    .pay-btn:hover{transform:translateY(-2px);box-shadow:0 16px 36px rgba(59,130,246,.35)}
    .pay-btn.loading{opacity:.7;cursor:not-allowed}
    .pay-btn .spinner{display:none}
    .pay-btn.loading .spinner{display:flex;align-items:center;gap:8px}
    .pay-btn.loading .btn-text{display:none}
    @keyframes spin{to{transform:rotate(360deg)}}
    .spin{animation:spin 1s linear infinite;width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%}
    .security-note{display:flex;align-items:center;justify-content:center;gap:6px;font-size:.72rem;color:var(--text2);margin-top:14px}
    .error-msg{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#f87171;padding:12px 16px;border-radius:10px;font-size:.85rem;margin-bottom:16px;display:none}
    .error-msg.show{display:block}
    .trust-row{display:flex;align-items:center;justify-content:center;gap:20px;margin-top:20px}
    .trust-item{display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--text2)}
    .order-summary{position:sticky;top:100px;background:var(--card);border:1px solid var(--border);border-radius:24px;overflow:hidden;box-shadow:0 20px 40px rgba(0,0,0,.2)}
    .summary-header{padding:20px 24px;background:rgba(59,130,246,.08);border-bottom:1px solid var(--border)}
    .summary-header h3{font-size:.9rem;font-weight:700;font-family:'Inter',sans-serif;margin-bottom:2px}
    .summary-header p{font-size:.75rem;color:var(--text2)}
    .summary-body{padding:24px}
    .product-row{display:flex;gap:14px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border)}
    .product-img{width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,rgba(59,130,246,.15),rgba(139,92,246,.1));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;overflow:hidden}
    .product-img img{width:100%;height:100%;object-fit:cover}
    .product-info{flex:1;min-width:0}
    .product-info h4{font-size:.88rem;font-weight:700;margin-bottom:4px;line-height:1.3}
    .product-info .pdesc{font-size:.75rem;color:var(--text2)}
    .product-tag{display:inline-block;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.15);color:var(--green);font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:6px;margin-top:6px}
    .price-breakdown{margin-bottom:16px}
    .price-line{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:.85rem}
    .price-line .lbl{color:var(--text2)}
    .price-line .val{font-weight:600}
    .price-line.discount .lbl{color:var(--green)}
    .price-line.discount .val{color:var(--green)}
    .price-line.total{font-size:.95rem;font-weight:800;padding-top:12px;margin-top:12px;border-top:1px solid var(--border)}
    .price-line.total .val{font-size:1.3rem;color:var(--blue)}
    .summary-countdown{background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.1);border-radius:12px;padding:14px;margin-top:16px;text-align:center}
    .sc-label{font-size:.7rem;color:var(--blue);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:5px}
    .countdown-timer{display:flex;gap:6px;justify-content:center}
    .sc-note{font-size:.7rem;color:var(--text2);font-weight:600;margin-top:8px}
    .guarantee-row{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;padding:12px;background:var(--card2);border:1px solid var(--border);border-radius:10px;font-size:.75rem;color:var(--text2)}
    .guarantee-row svg{width:14px;height:14px;color:var(--green);flex-shrink:0}
    .popup-overlay{position:fixed;inset:0;z-index:9990;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;padding:24px}
    .popup-overlay.show{display:flex}
    .popup-box{background:var(--card);border:1px solid var(--border);border-radius:28px;max-width:460px;width:100%;padding:44px;position:relative;text-align:center;box-shadow:0 40px 80px rgba(0,0,0,.4);transform:scale(.9);opacity:0;transition:all .5s cubic-bezier(.32,.72,0,1)}
    .popup-overlay.show .popup-box{transform:scale(1);opacity:1}
    .popup-close{position:absolute;top:14px;right:14px;width:36px;height:36px;border-radius:50%;background:var(--card2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--text2);transition:all .3s}
    .popup-close:hover{background:var(--blue);color:#fff;border-color:var(--blue)}
    .popup-icon{font-size:3rem;margin-bottom:18px}
    .popup-title{font-family:'Outfit',sans-serif;font-size:1.7rem;font-weight:700;margin-bottom:12px;letter-spacing:-.02em;line-height:1.2}
    .popup-desc{font-size:.88rem;color:var(--text2);margin-bottom:18px;line-height:1.65}
    .popup-code{display:flex;align-items:center;justify-content:center;gap:10px;background:var(--card2);border:2px dashed var(--blue);border-radius:12px;padding:14px 20px;margin-bottom:8px}
    .popup-code span{font-size:1.3rem;font-weight:800;color:var(--blue);letter-spacing:.1em;font-family:'Outfit',sans-serif}
    .popup-code button{background:var(--blue);color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:.72rem;font-weight:700;cursor:pointer;transition:all .3s}
    .popup-code button:hover{background:var(--violet)}
    .popup-note{font-size:.72rem;color:var(--text2);margin-bottom:10px}
    .popup-savings{font-size:.78rem;font-weight:700;color:var(--green);margin-bottom:20px}
    .popup-cta{display:block;width:100%;background:var(--blue);color:#fff;font-size:.95rem;font-weight:700;padding:16px;border-radius:14px;border:none;cursor:pointer;font-family:'Inter',sans-serif;transition:all .4s;text-decoration:none;text-align:center}
    .popup-cta:hover{background:var(--violet);transform:translateY(-2px);box-shadow:0 12px 28px rgba(59,130,246,.3)}
    .popup-timer{margin-top:16px;font-size:.72rem;color:var(--text2)}
    .popup-timer span{font-weight:700;color:var(--blue)}
    @media(max-width:1024px){.hero-grid,.checkout-grid{grid-template-columns:1fr;gap:40px}.hero-right{order:-1}.hero-sub{margin:0 auto 32px}.price-block{margin:0 auto 28px}.stats-row{justify-content:center;gap:24px}.features-grid{grid-template-columns:1fr}.footer-inner{flex-direction:column;gap:16px;text-align:center}.checkout-grid{padding:0 16px}.order-summary{position:static;margin-top:32px}.checkout-left,.checkout-right{width:100%;padding:0}}
    @media(max-width:768px){.hero-grid{grid-template-columns:1fr;gap:24px}.hero{padding:100px 16px 40px}.hero-title{font-size:clamp(1.8rem,6vw,2.5rem)}.hero-sub{font-size:.9rem}.nav{padding:8px 16px;top:8px}.nav-brand span{display:none}.price-block{flex-wrap:wrap;padding:12px 16px;justify-content:center}.price-current{font-size:1.6rem}.cta-btn{padding:14px 24px;font-size:.9rem;width:100%;justify-content:center}.countdown-bar{max-width:100%}.timer-value{font-size:1.5rem}.stats-row{grid-template-columns:repeat(2,1fr);gap:12px}.stat-item{padding:16px 8px}.features-grid{grid-template-columns:1fr;gap:16px}.section{padding:60px 16px}.section-title{font-size:clamp(1.5rem,5vw,2rem)}.footer{padding:40px 16px 30px}.pricing-card{padding:32px 20px}.pricing-price .amount{font-size:2.2rem}.pricing-features li{font-size:.85rem}.cta-section{padding:60px 16px}.checkout-page{padding:20px 16px}.checkout-form{padding:24px 16px}.field-group input{padding:12px 14px;font-size:.9rem}.pay-btn{padding:16px;font-size:.95rem;width:100%}.order-summary-box{padding:20px 16px}.timer-sticky{padding:10px 16px;font-size:.8rem;width:calc(100% - 32px);left:16px;transform:none;bottom:12px}.exit-popup-box{padding:32px 20px;margin:16px}.exit-popup-box h2{font-size:1.4rem}.exit-code-wrap input{font-size:1rem;padding:12px}.exit-link{padding:14px 24px;font-size:.9rem}}
    @media(max-width:480px){.hero{padding:90px 12px 32px}.hero-title{font-size:1.6rem;letter-spacing:-.02em}.eyebrow{padding:4px 12px;font-size:.65rem}.price-new{font-size:2rem}.timer-box{padding:14px}.timer-value{font-size:1.3rem}.feature-card,.pricing-card{padding:20px}.feature-card h3{font-size:.95rem}.btn-primary,.cta-btn{font-size:.9rem;padding:12px 20px}.strip-bar{padding:8px 12px;font-size:.7rem}.nav{top:0;border-radius:0}.section{padding:48px 12px}.section-title{font-size:1.4rem}.cta-section{padding:40px 12px;border-radius:16px}.cta-section h2{font-size:1.5rem}.pricing-card{border-radius:16px}.stats-row{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
<div class="noise"></div>

<nav class="nav">
    <a href="/" class="nav-brand">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="6" fill="#3B82F6"/><path d="M6 14l4-8 4 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Joala Digital
    </a>
</nav>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid">
        <div class="hero-text">
            <div class="eyebrow"><span class="dot"></span>Complete SaaS Template</div>
            <h1 class="hero-title">Turn Your Idea Into a Profitable SaaS — <span class="accent">In Under a Week</span></h1>
            <p class="hero-sub">Complete Laravel SaaS starter kit with subscription billing, multi-tenancy, admin panel, Stripe integration & full documentation — launch your SaaS without writing a single line of code.</p>
            <div class="price-block">
                <span class="price-current">&#8358;<?php echo $productPrice; ?></span>
                <span class="price-orig">&#8358;<?php echo $productOldPrice; ?></span>
                <span class="price-badge">SAVE &#8358;<?php echo $savings; ?></span>
            </div>
            <div class="cta-group">
                <a href="?step=checkout" class="cta-btn">
                    Launch My SaaS Now
                    <span class="cta-arrow">&#8594;</span>
                </a>
            <div class="countdown-bar">
                <div class="countdown-timer">
                    <div class="c-t"><span class="num" id="h1">00</span><span class="lbl">Hrs</span></div>
                    <div class="c-t"><span class="num" id="m1">00</span><span class="lbl">Min</span></div>
                    <div class="c-t"><span class="num" id="s1">00</span><span class="lbl">Sec</span></div>
                </div>
                <span class="countdown-sub-label">&#9201; Offer expires in</span>
            </div>
            </div>
            <div class="stats-row">
                <div class="stats-item"><div class="val">Stripe Ready</div><div class="lbl">Payment Integration</div></div>
                <div class="stats-item"><div class="val">Multi-Tenant</div><div class="lbl">Architecture Built-In</div></div>
                <div class="stats-item"><div class="val">Admin Panel</div><div class="lbl">Full Dashboard Included</div></div>
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-image">
                <div class="hero-placeholder">&#128187;</div>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--card)">
    <div class="section-eyebrow">What's Included</div>
    <h2>Everything You Need to Launch Your SaaS</h2>
    <p>A complete, production-ready Laravel application with all the features your SaaS needs from day one.</p>
    <div class="features-grid">
        <div class="feature-card"><div class="feature-icon">&#128179;</div><h4>Stripe Payment Integration</h4><p>Subscription billing, one-time payments, webhooks & customer portal fully configured.</p></div>
        <div class="feature-card"><div class="feature-icon">&#128640;</div><h4>Subscription Billing System</h4><p>Multiple pricing tiers, plans, trials, and usage-based billing built-in.</p></div>
        <div class="feature-card"><div class="feature-icon">&#128101;</div><h4>Multi-Tenant Architecture</h4><p>Serve multiple customers on a single codebase with isolated data per user.</p></div>
        <div class="feature-card"><div class="feature-icon">&#128202;</div><h4>Admin Dashboard</h4><p>Full admin panel with user management, analytics, billing & settings.</p></div>
        <div class="feature-card"><div class="feature-icon">&#9993;</div><h4>Email Automation Ready</h4><p>Laravel notifications & email queues configured for onboarding flows.</p></div>
        <div class="feature-card"><div class="feature-icon">&#128293;</div><h4>API Endpoints Built-In</h4><p>REST API with authentication, rate limiting & documentation ready.</p></div>
        <div class="feature-card"><div class="feature-icon">&#127912;</div><h4>Dark & Light Themes</h4><p>Modern UI with multiple themes, responsive design & clean components.</p></div>
        <div class="feature-card"><div class="feature-icon">&#127891;</div><h4>Lifetime Updates</h4><p>One-time purchase, free updates forever. Launch now, scale later.</p></div>
        <div class="feature-card"><div class="feature-icon">&#128220;</div><h4>Database & Migrations</h4><p>Fully structured schema, seeders & factories for rapid development.</p></div>
    </div>
</section>

<section class="pricing-section">
    <div class="pricing-inner">
        <div class="section-eyebrow">Limited Time Offer</div>
        <h2>One Price. Lifetime Access.</h2>
        <p style="margin-bottom:0">No monthly fees. No recurring costs. Pay once, own it forever.</p>
        <div class="pricing-card">
            <div class="pricing-badge">&#9733; BEST VALUE</div>
            <h3 class="pricing-title">SaaS Starter Kit</h3>
            <div class="pricing-price"><span class="strike">&#8358;<?php echo $productOldPrice; ?></span><span class="amount">&#8358;<?php echo $productPrice; ?></span></div>
            <div class="pricing-features">
                <ul>
                    <li><span class="fi">&#10003;</span> Complete Laravel SaaS Application</li>
                    <li><span class="fi">&#10003;</span> Stripe Payment Integration</li>
                    <li><span class="fi">&#10003;</span> Multi-Tenant Architecture</li>
                    <li><span class="fi">&#10003;</span> Admin Dashboard</li>
                    <li><span class="fi">&#10003;</span> REST API with Auth</li>
                    <li><span class="fi">&#10003;</span> Dark & Light Themes</li>
                    <li><span class="fi">&#10003;</span> Full Documentation</li>
                    <li><span class="fi">&#10003;</span> Lifetime Free Updates</li>
                </ul>
            </div>
            <a href="?step=checkout" class="btn-large" style="display:flex;text-decoration:none">Get SaaS Starter Kit &#8594;</a>
            <div class="pricing-timer">
                <div class="countdown-timer" style="justify-content:center">
                    <div class="c-t"><span class="num" id="h2">00</span><span class="lbl">Hrs</span></div>
                    <div class="c-t"><span class="num" id="m2">00</span><span class="lbl">Min</span></div>
                    <div class="c-t"><span class="num" id="s2">00</span><span class="lbl">Sec</span></div>
                </div>
                <div class="urgency">&#9201; Offer expires in the next 24 hours</div>
                <div class="guarantee">&#128274; 30-day money-back guarantee &mdash; no questions asked</div>
            </div>
        </div>
    </div>
</section>

<section class="proof-section">
    <div class="proof-inner">
        <div class="section-eyebrow" style="justify-content:center;color:var(--blue)">What Builders Say</div>
        <p class="proof-quote">"I launched my B2B SaaS in 5 days using this kit. The multi-tenancy setup alone saved me weeks of development. Stripe integration worked perfectly on the first try."</p>
        <div class="proof-author">
            <div class="proof-avatar">&#128100;</div>
            <div class="proof-info">
                <div class="proof-name">Tunde Alabi</div>
                <div class="proof-title">Founder, DataFlow Nigeria</div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-inner">
        <h2>Ready to Build Something That Scales?</h2>
        <p>Join developers who've launched their SaaS products with the Starter Kit. One payment, lifetime access.</p>
        <a href="?step=checkout" class="cta-btn-center">
            Get Started for &#8358;<?php echo $productPrice; ?>
            <span style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center">&#8594;</span>
        </a>
        <div class="cta-countdown">
            <div class="c-t"><span class="num" id="h3">00</span><span class="lbl">Hrs</span></div>
            <div class="c-t"><span class="num" id="m3">00</span><span class="lbl">Min</span></div>
            <div class="c-t"><span class="num" id="s3">00</span><span class="lbl">Sec</span></div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">&copy; 2026 Joala Digital. All rights reserved.</div>
        <div class="footer-links">
            <a href="/privacy">Privacy</a>
            <a href="/refund">Refund</a>
            <a href="/contact">Support</a>
        </div>
    </div>
</footer>

<?php if ($step === 'checkout'): ?>
<div class="checkout-wrap">
    <div class="checkout-grid">
        <div class="checkout-left">
            <h1>Complete Your Order</h1>
            <p class="sub">Fill in your details below to get instant access.</p>
            <div class="form-card">
                <div id="errorMsg" class="error-msg"></div>
                <div class="field-group"><label>Full Name</label><input type="text" id="name" placeholder="e.g. Tunde Alabi" autocomplete="name" value="<?php echo htmlspecialchars($name); ?>"></div>
                <div class="field-group"><label>Email Address</label><input type="email" id="buyerEmail" placeholder="your@email.com" autocomplete="email" value="<?php echo htmlspecialchars($email); ?>"></div>
                <div class="field-group"><label>Phone Number</label><input type="tel" id="phone" placeholder="08012345678" autocomplete="tel"></div>
                <div class="coupon-row">
                    <input type="text" id="couponCode" placeholder="Coupon code (optional)">
                    <button type="button" id="applyCouponBtn">Apply</button>
                </div>
                <div id="couponMsg" class="coupon-msg"></div>
                <button type="button" id="payBtn" class="pay-btn">
                    <span class="btn-text" style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 7V5a4 4 0 0 1 8 0v2M2 7h12a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Pay &#8358;<?php echo $productPrice; ?>
                    </span>
                    <span class="spinner"><span class="spin"></span>Processing...</span>
                </button>
                <div class="security-note">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="12" height="9" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M4 3V2a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Secured by Paystack &mdash; 256-bit SSL
                </div>
            </div>
            <div class="trust-row">
                <div class="trust-item">&#128737; Buyer Protected</div>
                <div class="trust-item">&#9889; Instant Access</div>
                <div class="trust-item">&#128274; 30-Day Guarantee</div>
            </div>
        </div>
        <div class="checkout-right">
            <div class="order-summary">
                <div class="summary-header">
                    <h3>Order Summary</h3>
                    <p><?php echo htmlspecialchars($productTitle); ?></p>
                </div>
                <div class="summary-body">
                    <div class="product-row">
                        <div class="summary-image">
                            <?php if ($productImage): ?>
                            <img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($productTitle); ?>">
                            <?php else: ?>
                            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($productTitle); ?></h4>
                            <p class="pdesc">Complete SaaS template, Stripe ready, admin dashboard</p>
                            <span class="product-tag">Digital Download</span>
                        </div>
                    </div>
                    <div class="price-breakdown">
                        <div class="price-line"><span class="lbl">Original Price</span><span class="val">&#8358;<?php echo $productOldPrice; ?></span></div>
                        <div class="price-line discount" id="discountRow" style="display:none"><span class="lbl">Coupon Discount</span><span class="val" id="discountVal">-&#8358;0</span></div>
                        <div class="price-line total"><span class="lbl">Total</span><span class="val" id="totalPrice">&#8358;<?php echo $productPrice; ?></span></div>
                    </div>
                    <div class="summary-timer">
                        <div class="sc-label">&#9201; Limited offer expires in</div>
                        <div class="countdown-timer">
                            <div class="c-t"><span class="num" id="h1">00</span><span class="lbl">Hrs</span></div>
                            <div class="c-t"><span class="num" id="m1">00</span><span class="lbl">Min</span></div>
                            <div class="c-t"><span class="num" id="s1">00</span><span class="lbl">Sec</span></div>
                        </div>
                        <div class="sc-note">&#9889; Don't miss this deal!</div>
                    </div>
                    <div class="guarantee-row">
                        <svg viewBox="0 0 16 16" fill="none"><path d="M8 1L1 4.5v4C1 11.54 4.22 14.45 8 15.5c3.78-1.05 7-3.96 7-7V4.5L8 1z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 8l2 2 3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        30-day money-back guarantee
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Exit Intent Popup -->
<div class="popup-overlay" id="exitPopup">
    <div class="popup-box">
        <button class="popup-close" id="popupClose">&times;</button>
        <div class="popup-icon">&#128293;</div>
        <h2 class="popup-title">Wait &mdash; Get 15% Off!</h2>
        <p class="popup-desc">Don't leave empty-handed! Use this exclusive discount code to get the SaaS Starter Kit at a lower price.</p>
        <div class="popup-code">
            <span id="popupCodeText">LAUNCH15</span>
            <button onclick="window.copyPopupCode()">Copy</button>
        </div>
        <p class="popup-note">Copy the code and apply it at checkout to save &#8358;<?php echo number_format((int)$productPriceRaw * 0.15, 0); ?></p>
        <p class="popup-savings">&#10003; You save &#8358;<?php echo number_format((int)$productPriceRaw * 0.15, 0); ?> on your order</p>
        <a href="?step=checkout" class="popup-cta">Claim My 15% Discount &#8594;</a>
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
        if (popupShown || sessionStorage.getItem('wppPopupSeen')) return;
        if (e.clientY < 5) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
        }
    });
    setTimeout(function() {
        if (!popupShown && !sessionStorage.getItem('wppPopupSeen')) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
            sessionStorage.setItem('wppPopupSeen', '1');
        }
    }, 25000);

    document.getElementById('popupClose').addEventListener('click', function() {
        document.getElementById('exitPopup').classList.remove('show');
    });

    window.copyPopupCode = function() {
        var code = document.getElementById('popupCodeText').textContent;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(function() {
                var btn = document.querySelector('.popup-code button');
                btn.textContent = 'Copied!';
                setTimeout(function() { btn.textContent = 'Copy'; }, 2000);
            });
        }
    };

    var popupStartTime = Date.now();
    var popupDuration = 5 * 60 * 1000;
    window.updatePopupTimer = function() {
        var el = document.getElementById('popupTimer');
        if (!el) return;
        var rem = Math.max(0, popupDuration - (Date.now() - popupStartTime));
        el.textContent = pad(Math.floor(rem / 60000)) + ':' + pad(Math.floor((rem % 60000) / 1000));
        if (rem > 0) setTimeout(window.updatePopupTimer, 1000);
    };
    window.updatePopupTimer();

    window.applyCoupon = function() {
        var code = document.getElementById('couponCode').value.trim();
        var msg = document.getElementById('couponMsg');
        if (!code) return;
        msg.className = 'coupon-msg';
        msg.textContent = 'Validating...';
        msg.style.display = 'block';
        fetch('/validate-coupon?code=' + encodeURIComponent(code) + '&amount=<?php echo $productPriceRaw; ?>')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                msg.style.display = 'block';
                if (data.valid) {
                    document.getElementById('discountRow').style.display = 'flex';
                    document.getElementById('discountVal').textContent = '-&#8358;' + data.discount.toLocaleString();
                    document.getElementById('totalPrice').textContent = '&#8358;' + Math.round(data.finalAmount).toLocaleString();
                    msg.className = 'coupon-msg success';
                    msg.innerHTML = '&#10003; Coupon applied! You save &#8358;' + data.discount.toLocaleString();
                    var btnText = document.querySelector('#payBtn .btn-text');
                    if (btnText) btnText.innerHTML = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 7V5a4 4 0 0 1 8 0v2M2 7h12a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg> Pay &#8358;' + Math.round(data.finalAmount).toLocaleString();
                } else {
                    msg.className = 'coupon-msg error';
                    msg.textContent = '&#10007; ' + (data.message || 'Invalid coupon code');
                }
            })
            .catch(function() {
                msg.style.display = 'block';
                msg.className = 'coupon-msg error';
                msg.textContent = '&#10007; Error validating coupon. Please try again.';
            });
    };

    window.payWithPaystack = function() {
        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('buyerEmail').value.trim();
        var phone = document.getElementById('phone').value.trim();
        var coupon = document.getElementById('couponCode').value.trim();
        var errEl = document.getElementById('errorMsg');
        var btn = document.getElementById('payBtn');
        errEl.classList.remove('show');
        errEl.textContent = '';
        if (!name || !email || !phone) {
            errEl.textContent = 'Please fill in all required fields.';
            errEl.classList.add('show');
            return;
        }
        if (typeof PaystackPop === 'undefined') {
            errEl.textContent = 'Payment system not loaded. Please refresh the page.';
            errEl.classList.add('show');
            return;
        }
        btn.classList.add('loading');
        var fd = new FormData();
        fd.append('action', 'init_payment');
        fd.append('name', name);
        fd.append('email', email);
        fd.append('phone', phone);
        fd.append('coupon_code', coupon);
        var basePath = window.location.pathname;
        fetch(basePath + '?step=checkout', { method: 'POST', body: fd })
            .then(function(r) { return r.text(); })
            .then(function(text) {
                btn.classList.remove('loading');
                var data = JSON.parse(text);
                if (data.error) {
                    errEl.textContent = data.error;
                    errEl.classList.add('show');
                    return;
                }
                var handler = PaystackPop.setup({
                    key: data.paystack_key,
                    email: data.email,
                    amount: data.amount,
                    reference: data.reference,
                    callback: function(res) {
                        window.location.href = basePath + '?step=checkout&reference=' + res.reference + '&trxref=' + res.trxref;
                    },
                    onClose: function() { btn.classList.remove('loading'); }
                });
                handler.openIframe();
            })
            .catch(function() {
                btn.classList.remove('loading');
                errEl.textContent = 'Network error. Please check your connection and try again.';
                errEl.classList.add('show');
            });
    };

    var payBtn = document.getElementById('payBtn');
    if (payBtn) payBtn.addEventListener('click', function(e) { e.preventDefault(); window.payWithPaystack(); });
    var applyBtn = document.getElementById('applyCouponBtn');
    if (applyBtn) applyBtn.addEventListener('click', function(e) { e.preventDefault(); window.applyCoupon(); });
})();
</script>
</body>
</html>