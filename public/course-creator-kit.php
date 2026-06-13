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
        'source' => 'course_creator_sales_page',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(5);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 5)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 5, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'course_creator_sales_page',
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
                        'subject' => $step->subject ?? 'Your course kit is ready',
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'Course Creator Kit'], $step->body ?? ''),
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

    $product = Product::where('slug', 'course-creator-kit')->where('is_active', true)->first();
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
        'source' => 'course_creator_sales_page',
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
            DB::table('funnel_leads')->where('funnel_id', 5)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'course-creator-kit')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Course Creator Kit';
$productPrice = $product ? number_format((float)($product->sale_price ?? $product->price), 0) : '18,000';
$productOldPrice = $product ? number_format((float)$product->price, 0) : '35,000';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 18000;
$productOldRaw = $product ? (float)$product->price : 35000;
$savings = number_format($productOldRaw - $productPriceRaw, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> — Launch Your Online Course in 7 Days | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700;9..144,800&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --bg:#FFF9F0;
        --card:#FFFFFF;
        --text:#1C1917;
        --text-muted:#57534E;
        --accent:#D97706;
        --accent-light:#FEF3C7;
        --accent-dark:#B45309;
        --secondary:#059669;
        --secondary-light:#D1FAE5;
        --warm:#FED7AA;
        --warm-light:#FFEDD5;
        --border:#FED7AA;
    }

    html{scroll-behavior:smooth}
    body{font-family:'Nunito',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

    .ff{font-family:'Fraunces',serif}

    .noise{position:fixed;inset:0;z-index:9999;pointer-events:none;opacity:.025;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 300 300' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}

    .nav{position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:100;background:rgba(255,249,240,.9);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid var(--border);border-radius:16px;padding:12px 24px;display:flex;align-items:center;gap:10px}
    .nav-brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-weight:700;font-size:.95rem}
    .nav-brand svg{width:28px;height:28px}
    .nav-tag{font-size:.7rem;background:var(--accent);color:#fff;padding:3px 10px;border-radius:20px;font-weight:700}

    .hero{padding:160px 24px 100px;max-width:1200px;margin:0 auto}
    .hero-eyebrow{font-size:.85rem;color:var(--accent);font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:16px}
    .hero-title{font-size:clamp(2.4rem,5vw,3.8rem);font-weight:700;line-height:1.15;margin-bottom:24px;color:var(--text)}
    .hero-sub{font-size:1.15rem;color:var(--text-muted);max-width:600px;margin-bottom:40px;line-height:1.7}

    .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start}

    .hero-left{position:relative}
    .price-block{background:var(--card);border:2px solid var(--border);border-radius:20px;padding:28px;display:inline-block;margin-bottom:24px;box-shadow:0 12px 40px rgba(217,119,6,.08)}
    .price-current{font-size:2.4rem;font-weight:800;color:var(--text);display:flex;align-items:baseline;gap:8px}
    .price-current .currency{font-size:1.4rem;font-weight:600}
    .price-original{font-size:1.1rem;color:var(--text-muted);text-decoration:line-through;margin-left:12px}
    .price-savings{background:var(--secondary);color:#fff;font-size:.8rem;font-weight:700;padding:6px 14px;border-radius:20px;margin-left:16px;display:inline-block}

    .cta-btn{display:inline-flex;align-items:center;gap:12px;background:var(--accent);color:#fff;font-size:1.1rem;font-weight:700;padding:20px 36px;border-radius:16px;text-decoration:none;transition:all .4s cubic-bezier(.32,.72,0,1);border:none;cursor:pointer;font-family:'Nunito',sans-serif}
    .cta-btn:hover{background:var(--accent-dark);transform:translateY(-3px);box-shadow:0 20px 40px rgba(217,119,6,.35)}
    .cta-arrow{font-size:1.2em}

    .timer-row{display:flex;gap:8px;margin-top:20px}
    .c-t{background:var(--warm-light);border:1px solid var(--border);border-radius:10px;padding:12px 14px;text-align:center;min-width:62px}
    .c-t .num{display:block;font-size:1.3rem;font-weight:800;color:var(--text);font-family:'Nunito',sans-serif}
    .c-t .lbl{font-size:.65rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;font-weight:700}
    .urgency{font-size:.85rem;color:var(--accent);font-weight:600;margin-top:14px;display:flex;align-items:center;gap:6px}
    .guarantee{font-size:.8rem;color:var(--text-muted);margin-top:10px;display:flex;align-items:center;gap:6px}

    .hero-right{position:relative}
    .hero-image{background:linear-gradient(145deg,var(--accent-light),var(--warm-light));border-radius:28px;padding:48px;display:flex;align-items:center;justify-content:center;aspect-ratio:1;position:relative;overflow:hidden;border:1px solid var(--border)}
    .hero-image::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 30%,rgba(255,255,255,.4),transparent 60%)}
    .hero-image svg{width:180px;height:180px;opacity:.9}
    .play-icon{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:80px;height:80px;background:rgba(255,255,255,.95);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 40px rgba(0,0,0,.15)}
    .play-icon svg{width:32px;height:32px;margin-left:4px}

    .stats-row{display:flex;gap:32px;margin-top:40px;flex-wrap:wrap}
    .stat-item{text-align:left}
    .stat-num{font-size:1.6rem;font-weight:800;color:var(--text)}
    .stat-label{font-size:.8rem;color:var(--text-muted)}

    .features{padding:80px 24px;max-width:1100px;margin:0 auto}
    .section-label{text-align:center;margin-bottom:12px}
    .section-eyebrow{font-size:.8rem;color:var(--secondary);font-weight:700;letter-spacing:.1em;text-transform:uppercase}
    .section-title{font-size:clamp(1.8rem,3vw,2.6rem);font-weight:700;text-align:center;margin-bottom:60px}

    .features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    .feature-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:32px;transition:all .4s cubic-bezier(.32,.72,0,1)}
    .feature-card:hover{transform:translateY(-6px);box-shadow:0 20px 50px rgba(217,119,6,.12);border-color:var(--accent)}
    .feature-icon{width:56px;height:56px;background:var(--accent-light);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:20px}
    .feature-icon svg{width:28px;height:28px;color:var(--accent)}
    .feature-card h3{font-size:1.15rem;font-weight:700;margin-bottom:10px;color:var(--text)}
    .feature-card p{font-size:.9rem;color:var(--text-muted);line-height:1.6}

    .modules{padding:80px 24px;max-width:900px;margin:0 auto;background:var(--card);border-radius:28px;border:1px solid var(--border)}
    .modules .section-title{margin-bottom:40px}
    .module-list{list-style:none}
    .module-item{display:flex;align-items:center;gap:20px;padding:24px;border-bottom:1px solid var(--border)}
    .module-item:last-child{border-bottom:none}
    .module-num{width:48px;height:48px;background:var(--accent);color:#fff;font-weight:800;font-size:1.2rem;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .module-content{flex:1}
    .module-content h4{font-size:1.1rem;font-weight:700;margin-bottom:4px}
    .module-content p{font-size:.85rem;color:var(--text-muted)}
    .module-lessons{font-size:.8rem;color:var(--secondary);font-weight:600;background:var(--secondary-light);padding:6px 14px;border-radius:20px}

    .testimonial{padding:80px 24px;max-width:800px;margin:0 auto;text-align:center}
    .testimonial-box{background:var(--warm-light);border-radius:24px;padding:48px;border:1px solid var(--border)}
    .testimonial-quote{font-size:1.35rem;font-style:italic;color:var(--text);margin-bottom:32px;line-height:1.8;font-family:'Fraunces',serif;font-weight:500}
    .testimonial-author{display:flex;align-items:center;justify-content:center;gap:16px}
    .testimonial-avatar{width:56px;height:56px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;font-weight:700}
    .testimonial-info{text-align:left}
    .testimonial-name{font-weight:700;color:var(--text)}
    .testimonial-role{font-size:.85rem;color:var(--text-muted)}

    .pricing{padding:80px 24px;max-width:600px;margin:0 auto}
    .pricing-card{background:var(--card);border:2px solid var(--accent);border-radius:28px;padding:48px;text-align:center;box-shadow:0 20px 60px rgba(217,119,6,.15)}
    .pricing-label{font-size:.8rem;color:var(--accent);font-weight:700;letter-spacing:.08em;text-transform:uppercase;margin-bottom:16px}
    .pricing-price{margin-bottom:32px}
    .pricing-price .amount{font-size:3.2rem;font-weight:800;color:var(--text);font-family:'Fraunces',serif}
    .pricing-price .currency{font-size:1.6rem;vertical-align:super}
    .pricing-price .period{font-size:1rem;color:var(--text-muted);font-weight:500}
    .pricing-features{text-align:left;margin-bottom:32px;list-style:none}
    .pricing-features li{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);font-size:.95rem}
    .pricing-features li::before{content:'✓';color:var(--secondary);font-weight:700;font-size:1rem}
    .pricing-cta{width:100%;display:block;background:var(--accent);color:#fff;font-size:1.15rem;font-weight:700;padding:20px;border-radius:16px;text-decoration:none;transition:all .4s;border:none;cursor:pointer;font-family:'Nunito',sans-serif}
    .pricing-cta:hover{background:var(--accent-dark);transform:translateY(-2px)}
    .pricing-timer{margin-top:24px}
    .pricing-guarantee{font-size:.85rem;color:var(--text-muted);margin-top:16px;display:flex;align-items:center;justify-content:center;gap:8px}

    .cta-section{padding:80px 24px;background:var(--warm-light);text-align:center;border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
    .cta-section h2{font-size:clamp(1.8rem,3vw,2.4rem);font-weight:700;margin-bottom:16px;font-family:'Fraunces',serif}
    .cta-section p{font-size:1.05rem;color:var(--text-muted);margin-bottom:32px;max-width:500px;margin-left:auto;margin-right:auto}
    .cta-btn-center{display:inline-flex;align-items:center;gap:12px;background:var(--accent);color:#fff;font-size:1.1rem;font-weight:700;padding:20px 36px;border-radius:16px;text-decoration:none;transition:all .4s;border:none;cursor:pointer;font-family:'Nunito',sans-serif}
    .cta-btn-center:hover{background:var(--accent-dark);transform:translateY(-3px);box-shadow:0 20px 40px rgba(217,119,6,.35)}
    .cta-countdown{margin-top:24px}
    .countdown-sub-label{font-size:.85rem;color:var(--text-muted);margin-top:12px}

    .footer{padding:48px 24px;text-align:center;border-top:1px solid var(--border)}
    .footer-inner{font-size:.9rem;color:var(--text-muted)}

    /* Checkout Styles */
    .checkout-wrap{padding:120px 24px 60px;max-width:1100px;margin:0 auto}
    .checkout-grid{display:grid;grid-template-columns:1fr 420px;gap:48px;align-items:start}
    .checkout-left h1{font-size:2rem;font-weight:700;margin-bottom:8px;font-family:'Fraunces',serif}
    .checkout-left .sub{color:var(--text-muted);margin-bottom:32px}
    .form-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:32px}
    .field-group{margin-bottom:20px}
    .field-group label{display:block;font-size:.9rem;font-weight:600;margin-bottom:8px;color:var(--text)}
    .field-group input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:14px;padding:16px 20px;font-size:1rem;color:var(--text);font-family:'Nunito',sans-serif;transition:all .4s;outline:none}
    .field-group input::placeholder{color:var(--text-muted);opacity:.6}
    .field-group input:focus{border-color:rgba(217,119,6,.4);background:#fff;box-shadow:0 0 0 3px rgba(217,119,6,.08)}
    .coupon-row{display:flex;gap:12px;margin-bottom:20px}
    .coupon-row input{flex:1}
    .coupon-row button{background:var(--bg);border:1px solid var(--border);border-radius:14px;padding:16px 24px;font-size:.88rem;font-weight:700;color:var(--text-muted);cursor:pointer;font-family:'Nunito',sans-serif;transition:all .4s;white-space:nowrap}
    .coupon-row button:hover{border-color:var(--accent);color:var(--accent)}
    .coupon-msg{font-size:.88rem;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:none}
    .coupon-msg.success{background:rgba(5,150,105,.08);border:1px solid rgba(5,150,105,.15);color:var(--secondary);display:block}
    .coupon-msg.error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#ef4444;display:block}
    .pay-btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:18px;padding:24px;font-size:1.1rem;font-weight:700;cursor:pointer;font-family:'Nunito',sans-serif;transition:all .5s;display:flex;align-items:center;justify-content:center;gap:12px;position:relative}
    .pay-btn:hover{background:var(--accent-dark);transform:translateY(-2px);box-shadow:0 20px 40px rgba(217,119,6,.35)}
    .pay-btn:active{transform:scale(.98)}
    .pay-btn.loading{opacity:.7;cursor:not-allowed}
    .pay-btn .spinner{display:none}
    .pay-btn.loading .spinner{display:flex;align-items:center;gap:10px}
    .pay-btn.loading .btn-text{display:none}
    @keyframes spin{to{transform:rotate(360deg)}}
    .spin{animation:spin 1s linear infinite;width:20px;height:20px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%}
    .security-note{display:flex;align-items:center;justify-content:center;gap:8px;font-size:.78rem;color:var(--text-muted);margin-top:16px}
    .error-msg{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);color:#ef4444;padding:14px 18px;border-radius:12px;font-size:.9rem;margin-bottom:20px;display:none}
    .error-msg.show{display:block}
    .trust-row{display:flex;align-items:center;justify-content:center;gap:24px;margin-top:24px}
    .trust-item{display:flex;align-items:center;gap:6px;font-size:.78rem;color:var(--text-muted)}
    .trust-item svg{width:14px;height:14px}

    .order-summary{position:sticky;top:100px;background:#fff;border:1px solid var(--border);border-radius:24px;overflow:hidden;box-shadow:0 20px 40px rgba(17,16,16,.06)}
    .summary-header{padding:24px 28px;background:rgba(217,119,6,.06%);border-bottom:1px solid var(--border)}
    .summary-header h3{font-size:1rem;font-weight:700;font-family:'Nunito',sans-serif;margin-bottom:4px}
    .summary-header p{font-size:.8rem;color:var(--text-muted)}
    .summary-body{padding:28px}
    .product-row{display:flex;gap:16px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border)}
    .product-img{width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,rgba(217,119,6,.1),rgba(217,119,6,.05));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0}
    .product-img img{width:100%;height:100%;object-fit:cover}
    .product-info{flex:1;min-width:0}
    .product-info h4{font-size:.95rem;font-weight:700;margin-bottom:4px;line-height:1.3}
    .product-info .pdesc{font-size:.8rem;color:var(--text-muted)}
    .product-tag{display:inline-block;background:rgba(5,150,105,.08);border:1px solid rgba(5,150,105,.15);color:var(--secondary);font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:8px;margin-top:8px}
    .price-breakdown{margin-bottom:20px}
    .price-line{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:.9rem}
    .price-line .lbl{color:var(--text-muted)}
    .price-line .val{font-weight:600}
    .price-line.discount .lbl{color:var(--secondary)}
    .price-line.discount .val{color:var(--secondary)}
    .price-line.total{font-size:1.05rem;font-weight:800;padding-top:14px;margin-top:14px;border-top:1px solid var(--border)}
    .price-line.total .val{font-size:1.5rem;color:var(--accent)}
    .summary-countdown{background:rgba(217,119,6,.06);border:1px solid rgba(217,119,6,.12);border-radius:14px;padding:16px;margin-top:20px;text-align:center}
    .sc-label{font-size:.75rem;color:var(--accent);font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;display:flex;align-items:center;justify-content:center;gap:6px}
    .countdown-timer{display:flex;gap:8px;justify-content:center}
    .sc-note{font-size:.75rem;color:var(--text-muted);font-weight:600;margin-top:10px}
    .guarantee-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:20px;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:12px;font-size:.82rem;color:var(--text-muted)}
    .guarantee-row svg{width:16px;height:16px;color:var(--secondary);flex-shrink:0}

    /* Exit Popup */
    .popup-overlay{position:fixed;inset:0;z-index:9990;background:rgba(17,16,16,.55);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;padding:24px}
    .popup-overlay.show{display:flex}
    .popup-box{background:#fff;border-radius:28px;max-width:480px;width:100%;padding:48px;position:relative;text-align:center;box-shadow:0 40px 80px rgba(17,16,16,.25);transform:scale(.9);opacity:0;transition:all .5s}
    .popup-overlay.show .popup-box{transform:scale(1);opacity:1}
    .popup-close{position:absolute;top:16px;right:16px;width:36px;height:36px;border-radius:50%;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--text-muted);transition:all .3s}
    .popup-close:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
    .popup-icon{font-size:3.5rem;margin-bottom:20px}
    .popup-title{font-family:'Fraunces',serif;font-size:1.8rem;font-weight:700;margin-bottom:12px;letter-spacing:-.02em;line-height:1.2}
    .popup-desc{font-size:.95rem;color:var(--text-muted);margin-bottom:20px;line-height:1.7}
    .popup-code{display:flex;align-items:center;justify-content:center;gap:12px;background:var(--bg);border:2px dashed var(--accent);border-radius:12px;padding:16px 24px;margin-bottom:8px}
    .popup-code span{font-size:1.4rem;font-weight:800;color:var(--accent);letter-spacing:.1em}
    .popup-code button{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:.78rem;font-weight:700;cursor:pointer;transition:all .3s}
    .popup-code button:hover{background:var(--accent-dark)}
    .popup-note{font-size:.78rem;color:var(--text-muted);margin-bottom:12px}
    .popup-savings{font-size:.82rem;font-weight:700;color:var(--secondary);margin-bottom:24px}
    .popup-cta{display:block;width:100%;background:var(--accent);color:#fff;font-size:1rem;font-weight:700;padding:18px;border-radius:14px;border:none;cursor:pointer;font-family:'Nunito',sans-serif;transition:all .5s;text-decoration:none}
    .popup-cta:hover{background:var(--accent-dark);transform:translateY(-2px);box-shadow:0 12px 32px rgba(217,119,6,.3)}
    .popup-timer{margin-top:20px;font-size:.78rem;color:var(--text-muted)}
    .popup-timer span{font-weight:700;color:var(--accent)}

    .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--text-muted);font-size:14px;font-weight:500}
    .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .summary-image{width:72px;height:72px;border-radius:16px;background:linear-gradient(135deg,rgba(217,119,6,0.1),rgba(217,119,6,0.05));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--text-muted);font-weight:500}
    .summary-timer strong{color:var(--accent);font-weight:700}

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
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect width="28" height="28" rx="8" fill="#D97706"/><path d="M8 19l6-11 6 11" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Joala Digital
    </a>
    <span class="nav-tag">Course Kit</span>
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
                            &#127891;
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($productTitle); ?></h4>
                            <p class="pdesc">Complete course launch system</p>
                            <span class="product-tag">Instant Download</span>
                        </div>
                    </div>
                    <div class="price-breakdown">
                        <div class="price-line">
                            <span class="lbl">Regular Price</span>
                            <span class="val">&#8358;<?php echo $productOldPrice; ?></span>
                        </div>
                        <div class="price-line">
                            <span class="lbl">Sale Price</span>
                            <span class="val">&#8358;<?php echo $productPrice; ?></span>
                        </div>
                        <div class="price-line discount" id="discountRow" style="display:none">
                            <span class="lbl">Discount</span>
                            <span class="val" id="discountVal">-&#8358;0</span>
                        </div>
                        <div class="price-line total">
                            <span class="lbl">Total</span>
                            <span class="val" id="totalPrice">&#8358;<?php echo $productPrice; ?></span>
                        </div>
                    </div>
                    <div class="summary-countdown">
                        <div class="sc-label">&#9201; Limited Time Offer</div>
                        <div class="countdown-timer">
                            <div class="c-t"><span class="num" id="h1">00</span><span class="lbl">Hrs</span></div>
                            <div class="c-t"><span class="num" id="m1">00</span><span class="lbl">Min</span></div>
                            <div class="c-t"><span class="num" id="s1">00</span><span class="lbl">Sec</span></div>
                        </div>
                        <div class="sc-note">Save &#8358;<?php echo $savings; ?> when you order now</div>
                    </div>
                    <div class="guarantee-row">
                        <svg viewBox="0 0 16 16" fill="none"><path d="M8 1L2 4v4c0 3.5 2.56 6.41 6 7 3.44-.59 6-3.69 6-7V4L8 1z" stroke="currentColor" stroke-width="1.2"/></svg>
                        30-day money-back guarantee &mdash; no questions asked
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<section class="hero">
    <div class="hero-grid">
        <div class="hero-left">
            <div class="hero-eyebrow">For Online Course Creators</div>
            <h1 class="hero-title ff">Launch Your Online Course in 7 Days &mdash; Even If You're Starting From Zero</h1>
            <p class="hero-sub">Complete course launch system with landing page templates, sales page, email sequences & student management</p>
            
            <div class="price-block">
                <div class="price-current"><span class="currency">&#8358;</span><?php echo $productPrice; ?><span class="price-original">&#8358;<?php echo $productOldPrice; ?></span></div>
                <span class="price-savings">Save &#8358;<?php echo $savings; ?></span>
            </div>
            
            <a href="?step=checkout" class="cta-btn">
                Get Instant Access
                <span class="cta-arrow">&#8594;</span>
            </a>
            
            <div class="timer-row">
                <div class="c-t"><span class="num" id="h1">00</span><span class="lbl">Hrs</span></div>
                <div class="c-t"><span class="num" id="m1">00</span><span class="lbl">Min</span></div>
                <div class="c-t"><span class="num" id="s1">00</span><span class="lbl">Sec</span></div>
            </div>
            <div class="urgency">&#9201; Offer expires in the next 24 hours</div>
            <div class="guarantee">&#128274; 30-day money-back guarantee &mdash; no questions asked</div>
            
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-num">200+</div>
                    <div class="stat-label">Course Creators</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">&#10003;</div>
                    <div class="stat-label">Includes Sales Funnel</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">&#10003;</div>
                    <div class="stat-label">Email Templates Included</div>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-image">
                <svg viewBox="0 0 120 120" fill="none">
                    <rect x="20" y="10" width="80" height="100" rx="8" fill="#fff" stroke="#FED7AA" stroke-width="2"/>
                    <rect x="30" y="25" width="60" height="8" rx="2" fill="#D97706"/>
                    <rect x="30" y="40" width="45" height="6" rx="2" fill="#FED7AA"/>
                    <rect x="30" y="52" width="55" height="6" rx="2" fill="#FED7AA"/>
                    <rect x="30" y="64" width="40" height="6" rx="2" fill="#FED7AA"/>
                    <circle cx="60" cy="90" r="15" fill="#D97706" opacity="0.2"/>
                    <path d="M55 90L58 93L65 86" stroke="#D97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div class="play-icon">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M8 5.14v14l11-7-11-7z" fill="#D97706"/></svg>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features">
    <div class="section-label">
        <span class="section-eyebrow">Everything You Need</span>
    </div>
    <h2 class="section-title ff">What's Included in the Kit</h2>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 4h16v16H4V4z" stroke="currentColor" stroke-width="2"/><path d="M8 8h8M8 12h8M8 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <h3>Course Landing Page Template</h3>
            <p>Professional, high-converting landing page template ready for your course. Just customize and publish.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <h3>Sales Page Copywriting Template</h3>
            <p>Proven sales copy framework that converts visitors into paying students. Plug & play.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            <h3>Student Management System</h3>
            <p>Track enrollments, manage students, and deliver content effortlessly with our built-in system.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/><path d="M22 6l-10 7L2 6" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            <h3>Email Sales Sequences (10+)</h3>
            <p>Ready-to-use email sequences for welcome, sales, upsell, and retention. Just connect your ESP.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="2" stroke="currentColor" stroke-width="2"/><path d="M1 10h22" stroke="currentColor" stroke-width="2"/></svg>
            </div>
            <h3>Payment Integration Ready</h3>
            <p>Works with Paystack, Flutterwave & Stripe. Accept payments from Nigerian & international students.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
            <h3>Step-by-Step Launch Guide</h3>
            <p>Detailed video tutorials showing exactly how to set up and launch your course in 7 days.</p>
        </div>
    </div>
</section>

<section class="modules">
    <h2 class="section-title ff">Course Modules</h2>
    <ul class="module-list">
        <li class="module-item">
            <div class="module-num">1</div>
            <div class="module-content">
                <h4>Course Planning & Structure</h4>
                <p>Define your niche, map your curriculum, and structure your modules for maximum student success.</p>
            </div>
            <span class="module-lessons">5 Lessons</span>
        </li>
        <li class="module-item">
            <div class="module-num">2</div>
            <div class="module-content">
                <h4>Landing Page & Sales Copy</h4>
                <p>Build your high-converting landing page and write compelling sales copy that sells while you sleep.</p>
            </div>
            <span class="module-lessons">8 Lessons</span>
        </li>
        <li class="module-item">
            <div class="module-num">3</div>
            <div class="module-content">
                <h4>Email Marketing Automation</h4>
                <p>Set up automated email sequences that nurture leads and convert them into paying students.</p>
            </div>
            <span class="module-lessons">6 Lessons</span>
        </li>
        <li class="module-item">
            <div class="module-num">4</div>
            <div class="module-content">
                <h4>Launch & Scale</h4>
                <p>Execute your launch, handle payments, onboard students, and scale your course business.</p>
            </div>
            <span class="module-lessons">7 Lessons</span>
        </li>
    </ul>
</section>

<section class="testimonial">
    <div class="testimonial-box">
        <p class="testimonial-quote">"I launched my first online course in just 5 days using this kit. The templates saved me weeks of work, and my first 30 students enrolled within the first week. The email sequences alone were worth the price!"</p>
        <div class="testimonial-author">
            <div class="testimonial-avatar">&#128105;&#8205;&#127891;</div>
            <div class="testimonial-info">
                <div class="testimonial-name">Chioma Azubike</div>
                <div class="testimonial-role">Digital Marketing Coach, Abuja</div>
            </div>
        </div>
    </div>
</section>

<section class="pricing">
    <div class="pricing-card">
        <div class="pricing-label">One-Time Payment</div>
        <div class="pricing-price">
            <span class="amount"><span class="currency">&#8358;</span><?php echo $productPrice; ?></span>
            <span class="period">one-time</span>
        </div>
        <ul class="pricing-features">
            <li>Complete Course Launch System</li>
            <li>Landing Page Template</li>
            <li>Sales Page Copywriting Template</li>
            <li>Student Management System</li>
            <li>10+ Email Sequences</li>
            <li>Payment Integration</li>
            <li>Step-by-Step Launch Guide</li>
            <li>26 Video Lessons</li>
            <li>30-Day Money-Back Guarantee</li>
        </ul>
        <a href="?step=checkout" class="pricing-cta">Get Started Now &mdash; &#8358;<?php echo $productPrice; ?></a>
        <div class="pricing-timer">
            <div class="timer-row" style="justify-content:center">
                <div class="c-t"><span class="num" id="h2">00</span><span class="lbl">Hrs</span></div>
                <div class="c-t"><span class="num" id="m2">00</span><span class="lbl">Min</span></div>
                <div class="c-t"><span class="num" id="s2">00</span><span class="lbl">Sec</span></div>
            </div>
        </div>
        <div class="pricing-guarantee">&#128274; 30-day money-back guarantee &mdash; no questions asked</div>
    </div>
</section>

<section class="cta-section">
    <h2 class="ff">Ready to Launch Your Course?</h2>
    <p>Join 200+ course creators who've launched their courses with the Course Creator Kit.</p>
    <a href="?step=checkout" class="cta-btn-center">
        Get Started for &#8358;<?php echo $productPrice; ?>
        <span class="cta-arrow">&#8594;</span>
    </a>
    <div class="cta-countdown">
        <div class="countdown-timer">
            <div class="c-t"><span class="num" id="h3">00</span><span class="lbl">Hrs</span></div>
            <div class="c-t"><span class="num" id="m3">00</span><span class="lbl">Min</span></div>
            <div class="c-t"><span class="num" id="s3">00</span><span class="lbl">Sec</span></div>
        </div>
        <div class="countdown-sub-label">&#9201; Offer expires in</div>
    </div>
</section>

<footer class="footer">
    <div class="footer-inner">
        &copy; <?php echo date('Y'); ?> Joala Digital. All rights reserved.
    </div>
</footer>
<?php endif; ?>

<!-- Exit Intent Popup -->
<div class="popup-overlay" id="exitPopup">
    <div class="popup-box">
        <button class="popup-close" id="popupClose">&times;</button>
        <div class="popup-icon">&#128293;</div>
        <h2 class="popup-title">Wait &mdash; Get 15% Off!</h2>
        <p class="popup-desc">Don't leave empty-handed! Use this exclusive discount code to get the Course Creator Kit at a lower price.</p>
        <div class="popup-code">
            <span id="popupCodeText">LAUNCH15</span>
            <button onclick="copyPopupCode()">Copy</button>
        </div>
        <p class="popup-note">Copy the code and apply it at checkout to save &#8358;2,700</p>
        <p class="popup-savings">&#10003; You save &#8358;2,700 on your order</p>
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
        if (popupShown || sessionStorage.getItem('cckPopupSeen')) return;
        if (e.clientY < 5) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
        }
    });
    setTimeout(function() {
        if (!popupShown && !sessionStorage.getItem('cckPopupSeen')) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
            sessionStorage.setItem('cckPopupSeen', '1');
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
        fetch('/validate-coupon?code=' + encodeURIComponent(code) + '&amount=18000')
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