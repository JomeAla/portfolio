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
        'source' => 'wordpress_sales_page',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(2);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 2)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 2, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'wordpress_sales_page',
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
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'WordPress Starter Kit'], $step->body ?? ''),
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

    $product = Product::where('slug', 'wordpress-starter-kit')->where('is_active', true)->first();
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
                // min order not met, skip discount silently
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
        'source' => 'wordpress_sales_page',
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
            DB::table('funnel_leads')->where('funnel_id', 2)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'wordpress-starter-kit')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'WordPress Starter Kit';
$productPrice = $product ? number_format((float)($product->sale_price ?? $product->price), 0) : '12,000';
$productOldPrice = $product ? number_format((float)$product->price, 0) : '28,000';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 12000;
$productOldRaw = $product ? (float)$product->price : 28000;
$savings = number_format($productOldRaw - $productPriceRaw, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Build Your Dream Website in Minutes | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Clash+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --cream:#FAF8F4;
        --warm:#F3EEE6;
        --ink:#111010;
        --ember:#c45a3b;
        --ember-dark:#a84e30;
        --stone:#6b6560;
        --mist:#e6e0d8;
        --sage:#4a5d52;
        --green:#22c55e;
    }

    html{scroll-behavior:smooth}
    body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--cream);color:var(--ink);line-height:1.6;overflow-x:hidden}

    .cd{font-family:'Clash Display',sans-serif}
    .noise{position:fixed;inset:0;z-index:9999;pointer-events:none;opacity:.025;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 300 300' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}

    ::selection{background:var(--sage);color:#fff}

    @keyframes fadeUp{to{opacity:1;transform:translateY(0)}}
    @keyframes pulseDot{0%,100%{opacity:1}50%{opacity:.4}}
    @keyframes pulse-timer{0%,100%{box-shadow:0 8px 32px rgba(196,90,59,.25)}50%{box-shadow:0 8px 48px rgba(196,90,59,.45),0 0 0 4px rgba(196,90,59,.1)}}

    /* Nav */
    .nav{position:fixed;top:24px;left:50%;transform:translateX(-50%);background:rgba(250,248,244,.88);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(17,16,16,.07);border-radius:100px;padding:12px 24px;transition:all .5s}
    .nav-brand{font-weight:700;font-size:.9rem;color:var(--ink);text-decoration:none;display:flex;align-items:center;gap:8px}
    .nav-brand svg{border-radius:6px}

    /* Hero */
    .hero{min-height:100dvh;display:flex;align-items:center;padding:140px 40px 80px;position:relative;overflow:hidden}
    .hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 30%,rgba(196,90,59,.08) 0%,transparent 70%)}
    .hero-grid{max-width:1200px;margin:0 auto;width:100%;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;position:relative;z-index:1}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--sage);color:#fff;font-size:.75rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;padding:6px 14px 6px 10px;border-radius:100px;margin-bottom:28px;opacity:0;transform:translateY(20px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .2s forwards}
    .eyebrow .dot{width:7px;height:7px;background:#fff;border-radius:50%;animation:pulseDot 2s infinite}
    .hero-title{font-family:'Clash Display',sans-serif;font-size:clamp(2.8rem,5vw,4.2rem);font-weight:700;line-height:1.06;letter-spacing:-.03em;margin-bottom:24px;opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .3s forwards}
    .hero-title span{color:var(--ember)}
    .hero-sub{font-size:1.05rem;color:var(--stone);max-width:480px;line-height:1.75;margin-bottom:36px;opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .4s forwards}
    .price-block{background:#fff;border:1px solid var(--mist);border-radius:20px;padding:18px 28px;display:inline-flex;align-items:center;gap:14px;margin-bottom:32px;opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .5s forwards}
    .price-current{font-size:2.4rem;font-weight:800;letter-spacing:-.02em;color:var(--ink)}
    .price-orig{font-size:1.2rem;color:var(--stone);text-decoration:line-through}
    .price-badge{background:var(--ember);color:#fff;padding:4px 12px;border-radius:8px;font-size:.78rem;font-weight:700;letter-spacing:.05em}
    .cta-btn{margin:0 auto;position:relative;z-index:100;display:flex;align-items:center;gap:14px;background:var(--ink);color:#fff;font-weight:700;font-size:1rem;padding:20px 36px;border-radius:16px;text-decoration:none;transition:all .5s cubic-bezier(.32,.72,0,1);opacity:0;transform:translateY(30px);animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .6s forwards;overflow:hidden;pointer-events:auto}
    .cta-btn:hover{transform:translateY(-3px);box-shadow:0 20px 40px rgba(17,16,16,.15)}
    .cta-btn:hover .cta-arrow{transform:translate(4px,-2px) scale(1.1)}
    .cta-arrow{position:relative;z-index:100;width:40px;height:40px;background:rgba(255,255,255,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:all .4s cubic-bezier(.32,.72,0,1);flex-shrink:0}
    .countdown-bar{position:relative;margin-top:20px;opacity:0;animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .7s forwards;align-items:center;display:flex;flex-direction:column;gap:0;z-index:10;width:100%;max-width:400px}
    .cta-group{display:flex;flex-direction:column;align-items:center;width:100%;max-width:400px}
    .countdown-bar::before{content:'';position:absolute;inset:-12px;background:linear-gradient(135deg,rgba(196,90,59,.06) 0%,transparent 60%);border-radius:32px;z-index:-1;pointer-events:none}
    .countdown-sub-label{font-size:.9rem;color:var(--ember);font-weight:700;text-transform:uppercase;letter-spacing:.05em;font-family:'Clash Display',sans-serif;margin-top:14px;opacity:0;animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .8s forwards;text-align:center;width:100%}
    .countdown-timer{display:flex;gap:8px}
    .c-t{background:var(--ember);border:2px solid rgba(196,90,59,.3);border-radius:12px;padding:12px 16px;text-align:center;min-width:72px;box-shadow:0 6px 20px rgba(196,90,59,.2);animation:pulse-timer 2s ease-in-out infinite}
    .c-t .num{font-size:1.6rem;font-weight:800;color:#fff;display:block;line-height:1;font-family:'Clash Display',sans-serif}
    .c-t .lbl{font-size:.65rem;color:rgba(255,255,255,.85);text-transform:uppercase;letter-spacing:.1em;margin-top:4px;display:block;font-weight:600}
    .stats-row{display:flex;gap:40px;margin-top:48px;padding-top:32px;border-top:1px solid var(--mist);opacity:0;animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .8s forwards}
    .stats-item{}
    .stats-item .val{font-size:1.8rem;font-weight:800;color:var(--ink);display:block}
    .stats-item .lbl{font-size:.8rem;color:var(--stone);margin-top:4px}
    .hero-right{position:relative;opacity:0;transform:translateY(40px);animation:fadeUp 1s cubic-bezier(.32,.72,0,1) .4s forwards}
    .hero-image{width:100%;aspect-ratio:3/2.5;border-radius:28px;background:linear-gradient(135deg,#f9f6f0 0%,#f0ebe3 50%,#e8e1d6 100%);border:1px solid var(--mist);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;box-shadow:0 30px 60px rgba(17,16,16,.08)}
    .hero-image::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 30% 40%,rgba(196,90,59,.1) 0%,transparent 60%)}
    .hero-image img{width:100%;height:100%;object-fit:cover;display:block}
    .hero-image-placeholder{font-size:5rem}
    .hero-badge{position:absolute;bottom:24px;left:24px;background:rgba(17,16,16,.88);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:14px 20px}
    .hero-badge .badge-num{font-size:1.6rem;font-weight:800;color:var(--ember)}
    .hero-badge .badge-lbl{font-size:.75rem;color:rgba(255,255,255,.6);margin-top:2px}
    .hero-badge-r{position:absolute;top:24px;right:24px;background:#fff;border:1px solid var(--mist);border-radius:16px;padding:14px 20px;display:flex;align-items:center;gap:10px}
    .hero-badge-r .badge-num{font-size:1.4rem;font-weight:800}
    .hero-badge-r .badge-lbl{font-size:.75rem;color:var(--stone)}
    .star-row{font-size:.85rem;color:var(--stone);margin-top:16px;display:flex;align-items:center;gap:8px;opacity:0;animation:fadeUp .8s cubic-bezier(.32,.72,0,1) .9s forwards}
    .star-row span{color:#f59e0b;font-size:1rem}

    /* Sections */
    .section{padding:120px 40px;max-width:1200px;margin:0 auto}
    .section-eyebrow{font-size:.75rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;color:var(--ember);margin-bottom:20px;display:flex;align-items:center;gap:12px}
    .section-eyebrow::before{content:'';width:24px;height:2px;background:var(--ember);border-radius:2px}
    .section h2{font-family:'Clash Display',sans-serif;font-size:clamp(2rem,4vw,3.2rem);font-weight:700;line-height:1.1;margin-bottom:20px;letter-spacing:-.02em}
    .section p{font-size:1.05rem;color:var(--stone);max-width:560px;line-height:1.7;margin-bottom:60px}

    /* Dark section */
    .dark-section{background:var(--ink);color:#fff;padding:120px 40px}
    .dark-inner{max-width:1200px;margin:0 auto}
    .dark-section .section-eyebrow{color:#e87d5a}
    .dark-section .section-eyebrow::before{background:#e87d5a}
    .dark-section h2{color:#fff}
    .dark-section .sub{color:rgba(255,255,255,.5)}

    /* Problems */
    .problems-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:60px}
    .problem-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:24px;padding:36px 28px;transition:all .6s cubic-bezier(.32,.72,0,1)}
    .problem-card:hover{background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.12);transform:translateY(-4px)}
    .problem-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:20px}
    .problem-card:nth-child(1) .problem-icon{background:rgba(239,68,68,.12)}
    .problem-card:nth-child(2) .problem-icon{background:rgba(245,158,11,.12)}
    .problem-card:nth-child(3) .problem-icon{background:rgba(196,90,59,.12)}
    .problem-card h3{font-size:1.1rem;font-weight:700;margin-bottom:10px;font-family:'Plus Jakarta Sans',sans-serif}
    .problem-card p{font-size:.9rem;color:rgba(255,255,255,.5);line-height:1.65}

    /* Features */
    .features-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:60px}
    .feature-card{background:#fff;border:1px solid var(--mist);border-radius:20px;padding:28px;display:flex;gap:20px;align-items:flex-start;transition:all .6s cubic-bezier(.32,.72,0,1)}
    .feature-card:hover{border-color:rgba(196,90,59,.15);box-shadow:0 8px 24px rgba(17,16,16,.06);transform:translateY(-2px)}
    .feature-check{width:36px;height:36px;flex-shrink:0;background:rgba(34,197,94,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:var(--green)}
    .feature-card h4{font-size:.95rem;font-weight:700;margin-bottom:6px}
    .feature-card p{font-size:.85rem;color:var(--stone);line-height:1.6}

    /* Pricing */
    .pricing-section{background:var(--warm);padding:120px 40px;position:relative;overflow:hidden}
    .pricing-glow{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:700px;height:700px;background:radial-gradient(ellipse,rgba(196,90,59,.1) 0%,transparent 65%);pointer-events:none}
    .pricing-inner{max-width:1200px;margin:0 auto;position:relative;z-index:1;text-align:center}
    .pricing-card{max-width:680px;margin:60px auto 0;background:#fff;border:1px solid var(--mist);border-radius:32px;padding:56px;text-align:center;box-shadow:0 40px 80px rgba(17,16,16,.08)}
    .pricing-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(196,90,59,.1);border:1px solid rgba(196,90,59,.2);border-radius:100px;padding:6px 16px;font-size:.8rem;font-weight:700;color:var(--ember);margin-bottom:20px}
    .pricing-title{font-family:'Clash Display',sans-serif;font-size:clamp(1.5rem,3vw,2rem);font-weight:700;margin-bottom:16px}
    .pricing-price{margin:20px 0}
    .pricing-price .strike{font-size:1.6rem;opacity:.35;text-decoration:line-through;margin-right:12px}
    .pricing-price .amount{font-size:4rem;font-weight:800;letter-spacing:-.03em;color:var(--ink)}
    .pricing-features{background:var(--warm);border:1px solid var(--mist);border-radius:18px;padding:28px;margin:24px 0;text-align:left}
    .pricing-features ul{list-style:none;display:flex;flex-direction:column;gap:14px}
    .pricing-features li{display:flex;align-items:center;gap:12px;font-size:.95rem}
    .pricing-features li .fi{color:var(--green);font-size:1.1rem;font-weight:700}
    .btn-large{display:inline-flex;align-items:center;justify-content:center;gap:14px;background:var(--ember);color:#fff;font-size:1.15rem;font-weight:700;padding:24px 48px;border-radius:18px;border:none;cursor:pointer;width:100%;transition:all .5s cubic-bezier(.32,.72,0,1);font-family:'Plus Jakarta Sans',sans-serif;position:relative;overflow:hidden}
    .btn-large:hover{background:var(--ember-dark);transform:translateY(-3px);box-shadow:0 24px 48px rgba(196,90,59,.35)}
    .btn-large:active{transform:scale(.98)}
    .pricing-countdown{margin-top:28px;display:flex;flex-direction:column;align-items:center;gap:12px}
    .timer-row{display:flex;gap:8px;justify-content:center}
    .urgency{font-size:.82rem;color:var(--ember);font-weight:700;margin-top:8px}
    .guarantee{font-size:.78rem;color:var(--stone);margin-top:14px;display:flex;align-items:center;justify-content:center;gap:8px}

    /* Proof */
    .proof-section{background:var(--ink);color:#fff;padding:120px 40px;text-align:center}
    .proof-inner{max-width:900px;margin:0 auto}
    .proof-quote{font-family:'Clash Display',sans-serif;font-size:clamp(1.6rem,3vw,2.5rem);font-weight:600;line-height:1.3;margin-bottom:40px;letter-spacing:-.02em}
    .proof-author{display:flex;align-items:center;justify-content:center;gap:16px}
    .proof-avatar{width:48px;height:48px;border-radius:50%;background:var(--sage);display:flex;align-items:center;justify-content:center;font-size:1.2rem}
    .proof-info{text-align:left}
    .proof-name{font-weight:700}
    .proof-title{font-size:.8rem;color:rgba(255,255,255,.5);margin-top:2px}

    /* CTA */
    .cta-section{padding:120px 40px;background:var(--cream);text-align:center}
    .cta-inner{max-width:700px;margin:0 auto}
    .cta-section h2{font-family:'Clash Display',sans-serif;font-size:clamp(2rem,4vw,3rem);font-weight:700;margin-bottom:16px;letter-spacing:-.02em}
    .cta-section p{font-size:1.05rem;color:var(--stone);margin-bottom:40px;line-height:1.7}
    .cta-btn-center{display:inline-flex;align-items:center;gap:14px;background:var(--ink);color:#fff;font-weight:700;font-size:1.1rem;padding:22px 42px;border-radius:16px;text-decoration:none;transition:all .5s cubic-bezier(.32,.72,0,1);text-align:center}
    .cta-btn-center:hover{transform:translateY(-3px);box-shadow:0 20px 40px rgba(17,16,16,.15)}
    .cta-btn-center:hover .cta-arrow{transform:translate(4px,-2px) scale(1.1)}
    .cta-countdown{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px}
    .cta-countdown .c-t{border-radius:12px;padding:10px 14px;min-width:72px;box-shadow:0 6px 20px rgba(196,90,59,.2)}
    .cta-countdown .c-t .num{font-size:1.6rem}

    /* Footer */
    .footer{padding:60px 40px 40px;background:var(--cream);border-top:1px solid var(--mist)}
    .footer-inner{max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center}
    .footer-brand{font-size:.9rem;font-weight:700}
    .footer-links{display:flex;gap:24px}
    .footer-links a{color:var(--stone);text-decoration:none;font-size:.82rem;transition:color .3s}
    .footer-links a:hover{color:var(--ink)}

    /* Checkout */
    .checkout-wrap{padding:120px 0;min-height:100dvh}
    .checkout-grid{max-width:1000px;margin:0 auto;padding:0 40px;display:grid;grid-template-columns:1fr 380px;gap:60px;align-items:start}
    .checkout-left{}
    .checkout-left h1{font-family:'Clash Display',sans-serif;font-size:2.4rem;font-weight:700;margin-bottom:8px;letter-spacing:-.02em}
    .checkout-left .sub{font-size:.95rem;color:var(--stone);margin-bottom:40px}
    .form-card{background:#fff;border:1px solid var(--mist);border-radius:24px;padding:36px}
    .field-group{margin-bottom:24px}
    .field-group label{display:block;font-size:.82rem;font-weight:600;color:var(--stone);margin-bottom:8px;letter-spacing:.03em}
    .field-group input{width:100%;background:var(--warm);border:1px solid var(--mist);border-radius:14px;padding:16px 20px;font-size:1rem;color:var(--ink);font-family:'Plus Jakarta Sans',sans-serif;transition:all .4s cubic-bezier(.32,.72,0,1);outline:none}
    .field-group input::placeholder{color:var(--stone);opacity:.5}
    .field-group input:focus{border-color:rgba(196,90,59,.4);background:#fff;box-shadow:0 0 0 3px rgba(196,90,59,.08)}
    .coupon-row{display:flex;gap:12px;margin-bottom:20px}
    .coupon-row input{flex:1}
    .coupon-row button{background:var(--warm);border:1px solid var(--mist);border-radius:14px;padding:16px 24px;font-size:.88rem;font-weight:700;color:var(--stone);cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .4s cubic-bezier(.32,.72,0,1);white-space:nowrap}
    .coupon-row button:hover{border-color:var(--ember);color:var(--ember)}
    .coupon-msg{font-size:.88rem;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:none}
    .coupon-msg.success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.15);color:var(--green);display:block}
    .coupon-msg.error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#ef4444;display:block}
    .pay-btn{width:100%;background:var(--ember);color:#fff;border:none;border-radius:18px;padding:24px;font-size:1.1rem;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .5s cubic-bezier(.32,.72,0,1);display:flex;align-items:center;justify-content:center;gap:12px;position:relative;overflow:hidden}
    .pay-btn:hover{background:var(--ember-dark);transform:translateY(-2px);box-shadow:0 20px 40px rgba(196,90,59,.35)}
    .pay-btn:active{transform:scale(.98)}
    .pay-btn.loading{opacity:.7;cursor:not-allowed}
    .pay-btn .spinner{display:none}
    .pay-btn.loading .spinner{display:flex;align-items:center;gap:10px}
    .pay-btn.loading .btn-text{display:none}
    @keyframes spin{to{transform:rotate(360deg)}}
    .spin{animation:spin 1s linear infinite;width:20px;height:20px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%}
    .security-note{display:flex;align-items:center;justify-content:center;gap:8px;font-size:.78rem;color:var(--stone);margin-top:16px}
    .error-msg{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#ef4444;padding:14px 18px;border-radius:12px;font-size:.9rem;margin-bottom:20px;display:none}
    .error-msg.show{display:block}
    .trust-row{display:flex;align-items:center;justify-content:center;gap:24px;margin-top:24px}
    .trust-item{display:flex;align-items:center;gap:6px;font-size:.78rem;color:var(--stone)}
    .trust-item svg{width:14px;height:14px}

    .order-summary{position:sticky;top:100px;background:#fff;border:1px solid var(--mist);border-radius:24px;overflow:hidden;box-shadow:0 20px 40px rgba(17,16,16,.06)}
    .summary-header{padding:24px 28px;background:rgba(196,90,59,.06);border-bottom:1px solid var(--mist)}
    .summary-header h3{font-size:1rem;font-weight:700;font-family:'Plus Jakarta Sans',sans-serif;margin-bottom:4px}
    .summary-header p{font-size:.8rem;color:var(--stone)}
    .summary-body{padding:28px}
    .product-row{display:flex;gap:16px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--mist)}
    .product-img{width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,rgba(196,90,59,.1),rgba(196,90,59,.05));border:1px solid var(--mist);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;overflow:hidden}
    .product-img img{width:100%;height:100%;object-fit:cover}
    .product-info{flex:1;min-width:0}
    .product-info h4{font-size:.95rem;font-weight:700;margin-bottom:4px;line-height:1.3}
    .product-info .pdesc{font-size:.8rem;color:var(--stone)}
    .product-tag{display:inline-block;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.15);color:var(--green);font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:8px;margin-top:8px}
    .price-breakdown{margin-bottom:20px}
    .price-line{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:.9rem}
    .price-line .lbl{color:var(--stone)}
    .price-line .val{font-weight:600}
    .price-line.discount .lbl{color:var(--green)}
    .price-line.discount .val{color:var(--green)}
    .price-line.total{font-size:1.05rem;font-weight:800;padding-top:14px;margin-top:14px;border-top:1px solid var(--mist)}
    .price-line.total .val{font-size:1.5rem;color:var(--ember)}
    .summary-countdown{background:rgba(196,90,59,.06);border:1px solid rgba(196,90,59,.12);border-radius:14px;padding:16px;margin-top:20px;text-align:center}
    .sc-label{font-size:.75rem;color:var(--ember);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;display:flex;align-items:center;justify-content:center;gap:6px}
    .countdown-timer{display:flex;gap:8px;justify-content:center}
    .sc-note{font-size:.75rem;color:var(--stone);font-weight:600;margin-top:10px}
    .guarantee-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:20px;padding:14px;background:var(--warm);border:1px solid var(--mist);border-radius:12px;font-size:.82rem;color:var(--stone)}
    .guarantee-row svg{width:16px;height:16px;color:var(--green);flex-shrink:0}

    /* Exit Popup */
    .popup-overlay{position:fixed;inset:0;z-index:9990;background:rgba(17,16,16,.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;padding:24px}
    .popup-overlay.show{display:flex}
    .popup-box{background:#fff;border-radius:28px;max-width:480px;width:100%;padding:48px;position:relative;text-align:center;box-shadow:0 40px 80px rgba(17,16,16,.25);transform:scale(.9);opacity:0;transition:all .5s cubic-bezier(.32,.72,0,1)}
    .popup-overlay.show .popup-box{transform:scale(1);opacity:1}
    .popup-close{position:absolute;top:16px;right:16px;width:36px;height:36px;border-radius:50%;background:var(--warm);border:1px solid var(--mist);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--stone);transition:all .3s}
    .popup-close:hover{background:var(--ember);color:#fff;border-color:var(--ember)}
    .popup-icon{font-size:3.5rem;margin-bottom:20px}
    .popup-title{font-family:'Clash Display',sans-serif;font-size:1.8rem;font-weight:700;margin-bottom:12px;letter-spacing:-.02em;line-height:1.2}
    .popup-desc{font-size:.95rem;color:var(--stone);margin-bottom:20px;line-height:1.7}
    .popup-code{display:flex;align-items:center;justify-content:center;gap:12px;background:var(--warm);border:2px dashed var(--ember);border-radius:12px;padding:16px 24px;margin-bottom:8px}
    .popup-code span{font-size:1.4rem;font-weight:800;color:var(--ember);letter-spacing:.1em}
    .popup-code button{background:var(--ember);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:.78rem;font-weight:700;cursor:pointer;transition:all .3s}
    .popup-code button:hover{background:var(--ember-dark)}
    .popup-note{font-size:.78rem;color:var(--stone);margin-bottom:12px}
    .popup-savings{font-size:.82rem;font-weight:700;color:var(--green);margin-bottom:24px}
    .popup-cta{display:block;width:100%;background:var(--ember);color:#fff;font-size:1rem;font-weight:700;padding:18px;border-radius:14px;border:none;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .5s cubic-bezier(.32,.72,0,1);text-decoration:none}
    .popup-cta:hover{background:var(--ember-dark);transform:translateY(-2px);box-shadow:0 12px 32px rgba(196,90,59,.3)}
    .popup-timer{margin-top:20px;font-size:.78rem;color:var(--stone)}
    .popup-timer span{font-weight:700;color:var(--ember)}

    .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--stone);font-size:14px;font-weight:500}
    .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--ember);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .summary-image{width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,rgba(196,90,59,0.1),rgba(196,90,59,0.05));border:1px solid var(--mist);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--stone);font-weight:500}
    .summary-timer strong{color:var(--ember);font-weight:700}

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

<div class="noise"></div>

<nav class="nav">
    <a href="/" class="nav-brand">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="6" fill="#c45a3b"/><path d="M6 14l4-8 4 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Joala Digital
    </a>
</nav>

<?php if ($step === 'checkout'): ?>
<div class="checkout-wrap">
    <div class="checkout-grid">
        <div class="checkout-left">
            <h1>Complete Your Order</h1>
            <p class="sub">Fill in your details below to get instant access.</p>

            <div class="form-card">
                <div id="errorMsg" class="error-msg"></div>
                <div class="field-group">
                    <label>Full Name</label>
                    <input type="text" id="name" placeholder="e.g. Amara Okafor" autocomplete="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="field-group">
                    <label>Email Address</label>
                    <input type="email" id="buyerEmail" placeholder="your@email.com" autocomplete="email" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="field-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phone" placeholder="08012345678" autocomplete="tel">
                </div>
                <div class="coupon-row">
                    <input type="text" id="couponCode" placeholder="Coupon code (optional)">
                    <button type="button" onclick="applyCoupon()">Apply</button>
                </div>
                <div id="couponMsg" class="coupon-msg"></div>

                <button type="button" id="payBtn" class="pay-btn">
                    <span class="btn-text">
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
                <div class="trust-item"><svg viewBox="0 0 14 14" fill="none"><path d="M7 1L1 4v4c0 3.31 2.56 6.41 6 7 3.44-.59 6-3.69 6-7V4L7 1z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>Buyer Protected</div>
                <div class="trust-item"><svg viewBox="0 0 14 14" fill="none"><path d="M2 7h10M5 10l2-2 2 2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>Instant Access</div>
                <div class="trust-item"><svg viewBox="0 0 14 14" fill="none"><path d="M1 4h12M3 7h8M2 10h10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>30-Day Guarantee</div>
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
                        <div class="product-img">
                            <?php if ($productImage): ?>
                            <img src="/<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($productTitle); ?>">
                            <?php else: ?>
                            &#128640;
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($productTitle); ?></h4>
                            <p class="pdesc">50+ pages, 20+ plugins, step-by-step guide</p>
                            <span class="product-tag">Digital Download</span>
                        </div>
                    </div>
                    <div class="price-breakdown">
                        <div class="price-line"><span class="lbl">Original Price</span><span class="val">&#8358;<?php echo $productOldPrice; ?></span></div>
                        <div class="price-line discount" id="discountRow" style="display:none"><span class="lbl">Coupon Discount</span><span class="val" id="discountVal">-&#8358;0</span></div>
                        <div class="price-line total"><span class="lbl">Total</span><span class="val" id="totalPrice">&#8358;<?php echo $productPrice; ?></span></div>
                    </div>
                    <div class="summary-countdown">
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
<?php else: ?>
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid">
        <div class="hero-text">
            <div class="eyebrow"><span class="dot"></span>Instant Digital Download</div>
            <h1 class="hero-title">Build Your Dream Website<br>in Under <span>30 Minutes</span></h1>
            <p class="hero-sub">50+ professional WordPress pages, templates &amp; plugins &mdash; everything you need to launch a conversion-ready website without hiring a developer.</p>
            <div class="price-block">
                <span class="price-current">&#8358;<?php echo $productPrice; ?></span>
                <span class="price-orig">&#8358;<?php echo $productOldPrice; ?></span>
                <span class="price-badge">SAVE &#8358;<?php echo $savings; ?></span>
            </div>
            <div class="cta-group">
                <a href="?step=checkout" class="cta-btn">
                    Get <?php echo htmlspecialchars($productTitle); ?>
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
                <div class="stats-item"><div class="val">50+</div><div class="lbl">Pages &amp; Templates</div></div>
                <div class="stats-item"><div class="val">20+</div><div class="lbl">Premium Plugins</div></div>
                <div class="stats-item"><div class="val">2,400+</div><div class="lbl">Happy Users</div></div>
            </div>
            <div class="star-row"><span>&#9733;&#9733;&#9733;&#9733;&#9733;</span> Loved by 2,400+ entrepreneurs across Nigeria</div>
        </div>
        <div class="hero-right">
            <div class="hero-image">
                <?php if ($productImage): ?>
                <img src="/<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($productTitle); ?>">
                <?php else: ?>
                <div class="hero-image-placeholder">&#128640;</div>
                <?php endif; ?>
            </div>
            <div class="hero-badge">
                <div class="badge-num">57%</div>
                <div class="badge-lbl">Discount Applied</div>
            </div>
            <div class="hero-badge-r">
                <div class="badge-num" style="color:var(--green)">&#10003;</div>
                <div>
                    <div class="badge-lbl">Lifetime</div>
                    <div class="badge-lbl" style="color:var(--ink);font-weight:600">Access</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dark-section">
    <div class="dark-inner">
        <div class="section-eyebrow">The Problem</div>
        <h2>Building a WordPress Site<br>Shouldn't Be This Hard</h2>
        <p class="sub">Most entrepreneurs waste weeks and thousands of naira trying to get their website right.</p>
        <div class="problems-grid">
            <div class="problem-card">
                <div class="problem-icon">&#128338;</div>
                <h3>Months of Setup Time</h3>
                <p>Starting from scratch means hours of research, configuration, and design work before you can publish.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">&#128176;</div>
                <h3>Huge Developer Costs</h3>
                <p>Hiring a professional WordPress developer costs &#8358;80,000+. Premium themes add up to thousands more.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">&#128552;</div>
                <h3>Endless Tutorial Rabbit Holes</h3>
                <p>Too many plugins, too many options, and too many conflicting tutorials online.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-eyebrow">What's Inside</div>
    <h2>Everything You Need<br>to Launch Fast</h2>
    <p>The WordPress Starter Kit gives you a complete head start.</p>
    <div class="features-grid">
        <div class="feature-card"><div class="feature-check">&#10003;</div><div><h4>10 Pre-Built Homepage Designs</h4><p>Business, portfolio, e-commerce, agency, blog &mdash; pick and launch in minutes.</p></div></div>
        <div class="feature-card"><div class="feature-check">&#10003;</div><div><h4>15+ Inner Page Templates</h4><p>About, services, contact, pricing, FAQ, team &mdash; all professionally designed.</p></div></div>
        <div class="feature-card"><div class="feature-check">&#10003;</div><div><h4>20+ Premium Plugins Included</h4><p>SEO, forms, security, speed, analytics &mdash; pre-configured and ready.</p></div></div>
        <div class="feature-card"><div class="feature-check">&#10003;</div><div><h4>WooCommerce Ready</h4><p>Start selling products or services immediately with pre-built shop templates.</p></div></div>
        <div class="feature-card"><div class="feature-check">&#10003;</div><div><h4>Step-by-Step Setup Guide</h4><p>From domain purchase to launch &mdash; a complete walkthrough in plain English.</p></div></div>
        <div class="feature-card"><div class="feature-check">&#10003;</div><div><h4>4 Bonus Packs Worth &#8358;15,000</h4><p>Email templates, lead capture forms, 90-day content calendar &amp; more.</p></div></div>
    </div>
</section>

<section class="pricing-section">
    <div class="pricing-glow"></div>
    <div class="pricing-inner">
        <div class="section-eyebrow">Limited Time Offer</div>
        <h2>Get Everything<br>for One Price</h2>
        <p style="margin-bottom:0">One-time payment. Lifetime access. No recurring fees.</p>
        <div class="pricing-card">
            <div class="pricing-badge">&#9733; BEST VALUE</div>
            <h3 class="pricing-title"><?php echo htmlspecialchars($productTitle); ?></h3>
            <div class="pricing-price"><span class="strike">&#8358;<?php echo $productOldPrice; ?></span><span class="amount">&#8358;<?php echo $productPrice; ?></span></div>
            <div class="pricing-features">
                <ul>
                    <li><span class="fi">&#10003;</span> 50+ Page Templates</li>
                    <li><span class="fi">&#10003;</span> 20+ Premium Plugins</li>
                    <li><span class="fi">&#10003;</span> WooCommerce Ready</li>
                    <li><span class="fi">&#10003;</span> Step-by-Step Setup Guide</li>
                    <li><span class="fi">&#10003;</span> All 4 Bonus Packs</li>
                    <li><span class="fi">&#10003;</span> Lifetime Free Updates</li>
                    <li><span class="fi">&#10003;</span> Instant Digital Delivery</li>
                </ul>
            </div>
            <form method="POST" action="?step=checkout" style="display:contents">
                <input type="hidden" name="action" value="capture_lead">
                <button type="submit" class="btn-large">Get Instant Access Now <span style="font-size:1.2rem">&#8594;</span></button>
            </form>
            <div class="pricing-countdown">
                <div class="timer-row">
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
        <div class="section-eyebrow" style="justify-content:center;color:#e87d5a">What Customers Say</div>
        <p class="proof-quote">"I launched my consulting website in 2 hours instead of 3 weeks. The templates are clean, fast, and actually look professional. Worth every kobo."</p>
        <div class="proof-author">
            <div class="proof-avatar">&#128100;</div>
            <div class="proof-info">
                <div class="proof-name">Chidinma Nwankwo</div>
                <div class="proof-title">Business Consultant, Lagos</div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-inner">
        <h2>Ready to Launch Your<br>Dream Website?</h2>
        <p>Join 2,400+ entrepreneurs who've built their sites with the Starter Kit.</p>
        <a href="?step=checkout" class="cta-btn-center" style="margin:0 auto;display:inline-flex;text-align:center">
                Get Started for &#8358;<?php echo $productPrice; ?>
                <span class="cta-arrow">&#8594;</span>
            </a>
        <div class="cta-countdown">
            <div class="countdown-timer">
                <div class="c-t"><span class="num" id="h3">00</span><span class="lbl">Hrs</span></div>
                <div class="c-t"><span class="num" id="m3">00</span><span class="lbl">Min</span></div>
                <div class="c-t"><span class="num" id="s3">00</span><span class="lbl">Sec</span></div>
            </div>
        </div>
        <div class="countdown-sub-label" style="text-align:center">&#9201; Offer expires in</div>
    </div>
</section>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">&copy; <?php echo date('Y'); ?> Joala Digital. All rights reserved.</div>
        <div class="footer-links">
            <a href="/privacy">Privacy</a>
            <a href="/refund">Refund</a>
            <a href="/contact">Support</a>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Exit Intent Popup -->
<div class="popup-overlay" id="exitPopup">
    <div class="popup-box">
        <button class="popup-close" id="popupClose">&times;</button>
        <div class="popup-icon">&#128293;</div>
        <h2 class="popup-title">Wait &mdash; Get 15% Off!</h2>
        <p class="popup-desc">Don't leave empty-handed! Use this exclusive discount code to get the WordPress Starter Kit at a lower price.</p>
        <div class="popup-code">
            <span id="popupCodeText">LAUNCH15</span>
            <button onclick="copyPopupCode()">Copy</button>
        </div>
        <p class="popup-note">Copy the code and apply it at checkout to save &#8358;1,800</p>
        <p class="popup-savings">&#10003; You save &#8358;1,800 on your order</p>
        <a href="?step=checkout" class="popup-cta">Claim My 15% Discount &#8594;</a>
        <div class="popup-timer">Offer expires in <span id="popupTimer">05:00</span></div>
    </div>
</div>

<script src="https://js.paystack.co/v2/inline.js"></script>
<script>
(function() {
    function pad(n) { return String(n).padStart(2, '0'); }

    // Timer: resets to 24h from NOW on every visit
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

    // Exit popup
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

    // Popup timer
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

    // Coupon apply
    window.applyCoupon = function() {
        var code = document.getElementById('couponCode').value.trim();
        var msg = document.getElementById('couponMsg');
        if (!code) return;
        msg.className = 'coupon-msg';
        msg.textContent = 'Validating...';
        msg.style.display = 'block';
        console.log('Applying coupon:', code);
        fetch('/validate-coupon?code=' + encodeURIComponent(code) + '&amount=12000')
            .then(function(r) {
                console.log('Coupon response status:', r.status);
                return r.json();
            })
            .then(function(data) {
                console.log('Coupon response data:', data);
                msg.style.display = 'block';
                if (data.valid) {
                    document.getElementById('discountRow').style.display = 'flex';
                    document.getElementById('discountVal').textContent = '-₦' + data.discount.toLocaleString();
                    document.getElementById('totalPrice').textContent = '₦' + Math.round(data.finalAmount).toLocaleString();
                    msg.className = 'coupon-msg success';
                    msg.innerHTML = '✓ Coupon applied! You save ₦' + data.discount.toLocaleString();
                    var btnText = document.querySelector('#payBtn .btn-text');
                    if (btnText) {
                        btnText.innerHTML = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 7V5a4 4 0 0 1 8 0v2M2 7h12a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg> Pay ₦' + Math.round(data.finalAmount).toLocaleString();
                    }
                } else {
                    msg.className = 'coupon-msg error';
                    msg.textContent = '✗ ' + (data.message || 'Invalid coupon code');
                }
            })
            .catch(function(err) {
                console.error('Coupon error:', err);
                msg.style.display = 'block';
                msg.className = 'coupon-msg error';
                msg.textContent = '✗ Error validating coupon. Please try again.';
            });
    };

    // Pay button
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

        console.log('Pay button clicked. Email:', email, 'Phone:', phone);

        if (typeof PaystackPop === 'undefined') {
            console.error('PaystackPop not loaded!');
            errEl.textContent = 'Payment system not loaded. Please refresh the page.';
            errEl.classList.add('show');
            return;
        }

        console.log('PaystackPop available, initializing payment...');
        btn.classList.add('loading');

        var fd = new FormData();
        fd.append('action', 'init_payment');
        fd.append('name', name);
        fd.append('email', email);
        fd.append('phone', phone);
        fd.append('coupon_code', coupon);

        var pathParts = window.location.pathname.split('/');
        var basePath = '/' + pathParts[1] + '/' + pathParts[2] + '.php';
        var fetchUrl = basePath + '?step=checkout';
        console.log('Fetching:', fetchUrl);

        fetch(fetchUrl, { method: 'POST', body: fd })
            .then(function(r) {
                console.log('Init payment response status:', r.status);
                return r.text();
            })
            .then(function(text) {
                console.log('Init payment response:', text);
                btn.classList.remove('loading');
                var data = JSON.parse(text);
                if (data.error) {
                    errEl.textContent = data.error;
                    errEl.classList.add('show');
                    return;
                }
                console.log('Opening Paystack with key:', data.paystack_key, 'amount:', data.amount);
                var handler = PaystackPop.setup({
                    key: data.paystack_key,
                    email: data.email,
                    amount: data.amount,
                    reference: data.reference,
                    callback: function(res) {
                        console.log('Payment callback:', res);
                        window.location.href = basePath + '?step=checkout&reference=' + res.reference + '&trxref=' + res.trxref;
                    },
                    onClose: function() {
                        console.log('Payment closed');
                        btn.classList.remove('loading');
                    }
                });
                handler.openIframe();
            })
            .catch(function(err) {
                console.error('Pay error:', err);
                btn.classList.remove('loading');
                errEl.textContent = 'Network error. Please check your connection and try again.';
                errEl.classList.add('show');
            });
    };

    // Attach pay button via event listener too (backup)
    var payBtn = document.getElementById('payBtn');
    if (payBtn) {
        payBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.payWithPaystack();
        });
    }

    // Attach coupon apply button via event listener
    var applyBtn = document.querySelector('.coupon-row button');
    if (applyBtn) {
        applyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.applyCoupon();
        });
    }
})();
</script>
</body>
</html>