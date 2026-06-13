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
        'source' => 'email_marketing_premium_bundle',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(16);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 16)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 16, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'email_marketing_premium_bundle',
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
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'Email Marketing Premium Bundle'], $step->body ?? ''),
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

    $product = Product::where('slug', 'email-marketing-premium-bundle')->where('is_active', true)->first();
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
        'source' => 'email_marketing_premium_bundle',
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
            DB::table('funnel_leads')->where('funnel_id', 16)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'email-marketing-premium-bundle')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Email Marketing Premium Bundle';
$productPrice = '65,000';
$productOldPrice = '65,000';
$productPriceRaw = 65000;
$productOldRaw = 65000;
$savings = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Complete Email Marketing Suite | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --bg:#0C0A09;
        --card:#1C1917;
        --text:#FAFAFA;
        --accent:#F59E0B;
        --accent-light:#FBBF24;
        --secondary:#78716C;
        --border:#292524;
    }

    html{scroll-behavior:smooth}
    body{font-family:'DM Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

    .syne{font-family:'Syne',sans-serif}

    html{scroll-behavior:smooth}
    body{font-family:'DM Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

    .syne{font-family:'Syne',sans-serif}
    .noise{position:fixed;inset:0;z-index:9999;pointer-events:none;opacity:.03;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 300 300' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}

    .hero{position:relative;padding:80px 24px 100px;overflow:hidden}
    .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 50% at 50% -20%,rgba(245,158,11,0.15),transparent 70%)}
    .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;max-width:1400px;margin:0 auto;align-items:center;position:relative}
    .hero-text{position:relative}
    .eyebrow{display:inline-flex;align-items:center;gap:10px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);padding:8px 16px;border-radius:100px;font-size:14px;font-weight:500;color:var(--accent);margin-bottom:24px}
    .dot{width:6px;height:6px;background:var(--accent);border-radius:50%}
    .hero-title{font-family:'Syne',sans-serif;font-size:clamp(36px,5vw,56px);font-weight:800;line-height:1.1;margin-bottom:20px;letter-spacing:-0.02em}
    .hero-sub{font-size:18px;color:#A8A29E;margin-bottom:32px;max-width:520px}
    .price-block{display:flex;align-items:center;gap:16px;margin-bottom:28px;flex-wrap:wrap}
    .price-current{font-family:'Syne',sans-serif;font-size:42px;font-weight:800;color:var(--accent)}
    .price-orig{font-size:20px;color:var(--secondary);text-decoration:line-through}
    .price-badge{background:linear-gradient(135deg,var(--accent),var(--accent-light));color:var(--bg);font-weight:700;padding:6px 14px;border-radius:100px;font-size:13px}
    .cta-group{display:flex;flex-direction:column;gap:20px}
    .cta-btn{display:inline-flex;align-items:center;gap:12px;background:linear-gradient(135deg,var(--accent),#D97706);color:var(--bg);font-weight:700;font-size:18px;padding:18px 32px;border-radius:12px;text-decoration:none;transition:all .3s;box-shadow:0 4px 24px rgba(245,158,11,0.3)}
    .cta-btn:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(245,158,11,0.4)}
    .cta-arrow{transition:transform .3s}
    .cta-btn:hover .cta-arrow{transform:translateX(4px)}
    .countdown-bar{display:flex;align-items:center;gap:16px;padding:12px 20px;background:rgba(245,158,11,0.05);border:1px solid rgba(245,158,11,0.15);border-radius:8px;width:fit-content}
    .countdown-timer{display:flex;gap:8px}
    .c-t{text-align:center}
    .num{font-family:'Syne',sans-serif;font-size:24px;font-weight:700;color:var(--accent);display:block;min-width:36px}
    .lbl{font-size:11px;color:var(--secondary);text-transform:uppercase;letter-spacing:.5px}
    .countdown-sub-label{font-size:13px;color:var(--secondary)}
    .stats-row{display:flex;gap:32px;margin-top:40px;padding-top:32px;border-top:1px solid var(--border)}
    .stats-item{text-align:left}
    .stats-item .val{font-family:'Syne',sans-serif;font-size:28px;font-weight:700;color:var(--text)}
    .stats-item .lbl{font-size:13px;color:var(--secondary)}
    .hero-right{position:relative}
    .hero-image{position:relative;background:linear-gradient(145deg,var(--card),#272524);border:1px solid var(--border);border-radius:24px;padding:40px;aspect-ratio:1;display:flex;align-items:center;justify-content:center}
    .hero-image svg{width:100%;height:100%;max-width:400px}
    .hero-badge{position:absolute;top:20px;left:20px;background:var(--card);border:1px solid var(--border);padding:16px 20px;border-radius:12px;display:flex;flex-direction:column;gap:4px}
    .badge-num{font-family:'Syne',sans-serif;font-size:24px;font-weight:800;color:var(--accent)}
    .badge-lbl{font-size:11px;color:var(--secondary);text-transform:uppercase}
    .hero-badge-r{position:absolute;bottom:20px;right:20px;background:var(--card);border:1px solid var(--border);padding:16px 20px;border-radius:12px;display:flex;align-items:center;gap:12px}
    .star-row{font-size:14px;color:var(--secondary);margin-top:16px}
    .star-row span{color:var(--accent)}

    .section{padding:100px 24px;max-width:1200px;margin:0 auto}
    .section-eyebrow{font-size:13px;font-weight:600;color:var(--accent);text-transform:uppercase;letter-spacing:2px;margin-bottom:12px}
    .section h2{font-family:'Syne',sans-serif;font-size:clamp(32px,4vw,44px);font-weight:800;margin-bottom:16px;line-height:1.2}
    .section > p{font-size:18px;color:var(--secondary);max-width:600px;margin:0 auto 60px;text-align:center}

    .features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    .feature-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px;transition:all .3s}
    .feature-card:hover{border-color:var(--accent);transform:translateY(-4px)}
    .feature-icon{width:56px;height:56px;background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(245,158,11,0.05));border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;font-size:28px}
    .feature-card h4{font-family:'Syne',sans-serif;font-size:20px;font-weight:700;margin-bottom:12px}
    .feature-card p{color:var(--secondary);font-size:15px;line-height:1.6}
    .feature-check{color:var(--accent);font-size:20px;margin-bottom:12px}

    .pricing-section{position:relative;padding:100px 24px;background:var(--card);overflow:hidden}
    .pricing-section::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 60% 40% at 50% 100%,rgba(245,158,11,0.1),transparent 70%)}
    .pricing-inner{position:relative;max-width:700px;margin:0 auto;text-align:center}
    .pricing-card{background:var(--bg);border:1px solid var(--border);border-radius:24px;padding:48px;margin-top:40px}
    .pricing-badge{display:inline-block;background:linear-gradient(135deg,var(--accent),#D97706);color:var(--bg);font-weight:700;padding:8px 20px;border-radius:100px;font-size:13px;margin-bottom:24px}
    .pricing-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;margin-bottom:16px}
    .pricing-price{display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:32px}
    .pricing-price .strike{font-size:24px;color:var(--secondary);text-decoration:line-through}
    .pricing-price .amount{font-family:'Syne',sans-serif;font-size:48px;font-weight:800;color:var(--accent)}
    .pricing-features{text-align:left;margin-bottom:32px}
    .pricing-features ul{list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .pricing-features li{display:flex;align-items:center;gap:12px;font-size:15px;color:#D6D3D1}
    .pricing-features .fi{color:var(--accent);font-size:18px}
    .btn-large{width:100%;background:linear-gradient(135deg,var(--accent),#D97706);color:var(--bg);font-weight:700;font-size:18px;padding:20px 32px;border:none;border-radius:12px;cursor:pointer;transition:all .3s;box-shadow:0 4px 24px rgba(245,158,11,0.3)}
    .btn-large:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(245,158,11,0.4)}
    .pricing-countdown{margin-top:32px;padding-top:32px;border-top:1px solid var(--border)}
    .urgency{font-size:14px;color:#FBBF24;margin-bottom:16px}
    .guarantee{font-size:14px;color:var(--secondary);margin-top:16px}

    .cta-section{padding:100px 24px;background:linear-gradient(180deg,var(--bg) 0%,#0C0A09 100%);text-align:center}
    .cta-inner{max-width:700px;margin:0 auto}
    .cta-section h2{font-family:'Syne',sans-serif;font-size:clamp(36px,5vw,48px);font-weight:800;margin-bottom:20px}
    .cta-section p{font-size:18px;color:var(--secondary);margin-bottom:40px}
    .cta-btn-center{background:linear-gradient(135deg,var(--accent),#D97706);color:var(--bg);font-weight:700;font-size:20px;padding:20px 40px;border-radius:12px;text-decoration:none;display:inline-flex;align-items:center;gap:12px;transition:all .3s;box-shadow:0 4px 24px rgba(245,158,11,0.3)}
    .cta-btn-center:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(245,158,11,0.4)}
    .cta-countdown{margin-top:32px;display:flex;justify-content:center}

    .proof-section{padding:80px 24px;background:var(--card);text-align:center}
    .proof-inner{max-width:700px;margin:0 auto}
    .proof-quote{font-size:22px;font-style:italic;color:#D6D3D1;margin-bottom:32px;line-height:1.6}
    .proof-author{display:flex;align-items:center;justify-content:center;gap:16px}
    .proof-avatar{width:56px;height:56px;background:linear-gradient(135deg,var(--accent),#D97706);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px}
    .proof-name{font-weight:700;font-size:18px}
    .proof-title{font-size:14px;color:var(--secondary)}

    .footer{padding:40px 24px;border-top:1px solid var(--border)}
    .footer-inner{max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center}
    .footer-brand{font-size:14px;color:var(--secondary)}
    .footer-links{display:flex;gap:24px}
    .footer-links a{font-size:14px;color:var(--secondary);text-decoration:none;transition:color .3s}
    .footer-links a:hover{color:var(--accent)}

    .checkout-wrap{padding:60px 24px;max-width:1200px;margin:0 auto}
    .checkout-grid{display:grid;grid-template-columns:1fr 450px;gap:48px}
    .checkout-left h1{font-family:'Syne',sans-serif;font-size:36px;font-weight:800;margin-bottom:8px}
    .checkout-left .sub{color:var(--secondary);margin-bottom:32px}
    .form-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px}
    .error-msg{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#FCA5A5;padding:12px 16px;border-radius:8px;margin-bottom:20px;display:none}
    .error-msg.show{display:block}
    .field-group{margin-bottom:20px}
    .field-group label{display:block;font-size:14px;font-weight:600;margin-bottom:8px}
    .field-group input{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:14px 16px;font-size:16px;color:var(--text);transition:border-color .3s}
    .field-group input:focus{outline:none;border-color:var(--accent)}
    .coupon-row{display:flex;gap:12px;margin-bottom:20px}
    .coupon-row input{flex:1;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:14px 16px;font-size:16px;color:var(--text)}
    .coupon-row button{background:transparent;border:1px solid var(--accent);color:var(--accent);padding:14px 24px;border-radius:8px;font-weight:600;cursor:pointer;transition:all .3s}
    .coupon-row button:hover{background:var(--accent);color:var(--bg)}
    .coupon-msg{font-size:14px;margin-bottom:20px;display:none}
    .coupon-msg.success{color:#4ADE80}
    .coupon-msg.error{color:#FCA5A5}
    .pay-btn{width:100%;background:linear-gradient(135deg,var(--accent),#D97706);color:var(--bg);font-weight:700;font-size:18px;padding:18px 32px;border:none;border-radius:12px;cursor:pointer;transition:all .3s;position:relative;overflow:hidden}
    .pay-btn:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(245,158,11,0.4)}
    .pay-btn .spinner{display:none}
    .pay-btn.loading .btn-text{display:none}
    .pay-btn.loading .spinner{display:inline-flex;align-items:center;gap:8px}
    .spin{width:18px;height:18px;border:2px solid rgba(0,0,0,0.2);border-top-color:var(--bg);border-radius:50%;animation:spin .8s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .security-note{display:flex;align-items:center;justify-content:center;gap:8px;font-size:13px;color:var(--secondary);margin-top:16px}
    .trust-row{display:flex;justify-content:center;gap:32px;margin-top:32px;padding-top:32px;border-top:1px solid var(--border)}
    .trust-item{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--secondary)}
    .trust-item svg{color:var(--accent)}
    .checkout-right{position:sticky;top:40px;height:fit-content}
    .order-summary{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden}
    .summary-header{background:rgba(245,158,11,0.1);border-bottom:1px solid var(--border);padding:24px}
    .summary-header h3{font-family:'Syne',sans-serif;font-size:20px;font-weight:700;margin-bottom:4px}
    .summary-header p{color:var(--secondary);font-size:14px}
    .summary-body{padding:24px}
    .product-row{display:flex;gap:16px;padding-bottom:24px;border-bottom:1px solid var(--border);margin-bottom:24px}
    .product-img{width:80px;height:80px;background:var(--bg);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:36px;flex-shrink:0}
    .product-info h4{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:4px}
    .product-info .pdesc{font-size:13px;color:var(--secondary);margin-bottom:8px}
    .product-tag{font-size:11px;background:var(--border);padding:4px 10px;border-radius:100px;color:#A8A29E}
    .price-breakdown{margin-bottom:24px}
    .price-line{display:flex;justify-content:space-between;margin-bottom:12px;font-size:14px}
    .price-line .lbl{color:var(--secondary)}
    .price-line.discount{color:#4ADE80}
    .price-line.total{font-weight:700;font-size:18px;padding-top:12px;border-top:1px solid var(--border)}
    .summary-countdown{background:rgba(245,158,11,0.05);border:1px solid rgba(245,158,11,0.15);border-radius:12px;padding:20px;margin-bottom:24px}
    .sc-label{font-size:13px;color:var(--secondary);margin-bottom:12px}
    .sc-note{font-size:13px;color:#FBBF24;margin-top:12px}
    .guarantee-row{display:flex;align-items:center;justify-content:center;gap:8px;font-size:13px;color:#4ADE80}

    .popup-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.8);z-index:9999;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
    .popup-overlay.show{display:flex}
    .popup-box{background:var(--card);border:1px solid var(--border);border-radius:24px;padding:40px;max-width:440px;width:90%;text-align:center;position:relative;animation:popupIn .3s ease}
    @keyframes popupIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
    .popup-close{position:absolute;top:16px;right:16px;background:none;border:none;color:var(--secondary);font-size:28px;cursor:pointer;line-height:1}
    .popup-icon{font-size:48px;margin-bottom:16px}
    .popup-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;margin-bottom:12px}
    .popup-desc{color:var(--secondary);margin-bottom:24px}
    .popup-code{display:flex;align-items:center;justify-content:center;gap:12px;background:var(--bg);border:1px dashed var(--border);border-radius:8px;padding:16px;margin-bottom:12px}
    .popup-code span{font-family:'Syne',sans-serif;font-size:24px;font-weight:700;color:var(--accent)}
    .popup-code button{background:var(--accent);color:var(--bg);border:none;padding:8px 16px;border-radius:6px;font-weight:600;cursor:pointer}
    .popup-note{font-size:13px;color:var(--secondary);margin-bottom:16px}
    .popup-savings{color:#4ADE80;font-weight:600;margin-bottom:24px}
    .popup-cta{display:inline-block;background:linear-gradient(135deg,var(--accent),#D97706);color:var(--bg);font-weight:700;padding:16px 32px;border-radius:12px;text-decoration:none;transition:all .3s}
    .popup-cta:hover{transform:translateY(-2px)}
    .popup-timer{font-size:13px;color:var(--secondary);margin-top:16px}

    .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--secondary);font-size:14px;font-weight:500}
    .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .summary-image{width:80px;height:80px;border-radius:8px;background:var(--bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--secondary);font-weight:500}
    .summary-timer strong{color:var(--accent);font-weight:700}

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
                            &#128231;
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($productTitle); ?></h4>
                            <p class="pdesc">Complete email marketing suite with templates, automation & setup</p>
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
    <div class="hero-grid">
        <div class="hero-text">
            <div class="eyebrow"><span class="dot"></span>Premium Email Marketing Suite</div>
            <h1 class="hero-title syne">The Complete Email<br>Business-in-a-Box for<br><span style="color:var(--accent)">Serious Marketers</span></h1>
            <p class="hero-sub">Everything you need: templates, automation setup, and done-for-you integration &mdash; launch your email business in 7 days.</p>
            <div class="price-block">
                <span class="price-current">&#8358;<?php echo $productPrice; ?></span>
                <span class="price-orig">&#8358;<?php echo $productOldPrice; ?></span>
            </div>
            <div class="cta-group">
                <a href="?step=checkout" class="cta-btn">
                    Get Instant Access
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
                <div class="stats-item"><div class="val">&#10003;</div><div class="lbl">Complete Setup Included</div></div>
                <div class="stats-item"><div class="val">50+</div><div class="lbl">Premium Templates</div></div>
                <div class="stats-item"><div class="val">&#8734;</div><div class="lbl">Lifetime Access</div></div>
            </div>
            <div class="star-row"><span>&#9733;&#9733;&#9733;&#9733;&#9733;</span> Trusted by serious marketers across Nigeria</div>
        </div>
        <div class="hero-right">
            <div class="hero-image">
                <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="40" y="40" width="320" height="320" rx="24" fill="#1C1917" stroke="#292524" stroke-width="2"/>
                    <rect x="60" y="60" width="280" height="40" rx="8" fill="#0C0A09"/>
                    <circle cx="90" cy="80" r="6" fill="#EF4444"/>
                    <circle cx="110" cy="80" r="6" fill="#F59E0B"/>
                    <circle cx="130" cy="80" r="6" fill="#22C55E"/>
                    <rect x="60" y="120" width="120" height="80" rx="8" fill="#0C0A09"/>
                    <rect x="60" y="210" width="180" height="24" rx="4" fill="#272524"/>
                    <rect x="60" y="244" width="140" height="16" rx="4" fill="#272524"/>
                    <rect x="60" y="270" width="100" height="16" rx="4" fill="#272524"/>
                    <rect x="200" y="120" width="140" height="60" rx="8" fill="#0C0A09"/>
                    <rect x="200" y="190" width="140" height="16" rx="4" fill="#F59E0B"/>
                    <rect x="200" y="216" width="100" height="12" rx="4" fill="#272524"/>
                    <rect x="200" y="238" width="120" height="12" rx="4" fill="#272524"/>
                    <rect x="60" y="310" width="280" height="30" rx="6" fill="#F59E0B"/>
                    <text x="200" y="330" text-anchor="middle" fill="#0C0A09" font-family="system-ui" font-size="14" font-weight="700">SEND CAMPAIGN</text>
                </svg>
            </div>
            <div class="hero-badge">
                <div class="badge-num">50+</div>
                <div class="badge-lbl">Templates</div>
            </div>
            <div class="hero-badge-r">
                <div class="badge-num" style="color:var(--accent)">&#10003;</div>
                <div>
                    <div class="badge-lbl">Automation</div>
                    <div class="badge-lbl" style="color:var(--text);font-weight:600">Included</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-eyebrow">What's Included</div>
    <h2 class="syne">Everything You Need to<br>Build Your Email Business</h2>
    <p>The complete toolkit that top email marketers use to scale.</p>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">&#128231;</div>
            <h4>All Email Templates (50+)</h4>
            <p>Professional, high-converting templates for welcome sequences, promotions, newsletters, and more.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#9881;</div>
            <h4>Done-For-You Automation Setup</h4>
            <p>Complete automation workflows pre-built and ready to activate. Just connect and launch.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128172;</div>
            <h4>Autoresponder Integration</h4>
            <p>Seamless setup with all major email platforms. Get your autoresponder running in hours.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128196;</div>
            <h4>Landing Page Templates</h4>
            <p>High-converting landing pages optimized for list building and product launches.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128101;</div>
            <h4>List Building Strategies</h4>
            <p>Proven tactics to grow your email list faster with quality subscribers.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#127760;</div>
            <h4>Exclusive Community Access</h4>
            <p>Join a private group of email marketers sharing strategies and support.</p>
        </div>
    </div>
</section>

<section class="pricing-section">
    <div class="pricing-inner">
        <div class="section-eyebrow">Limited Time Offer</div>
        <h2 class="syne">Get Everything for<br>One Price</h2>
        <p style="margin-bottom:0">One-time payment. Lifetime access. No recurring fees.</p>
        <div class="pricing-card">
            <div class="pricing-badge">&#9733; BEST VALUE</div>
            <h3 class="pricing-title"><?php echo htmlspecialchars($productTitle); ?></h3>
            <div class="pricing-price">
                <span class="strike">&#8358;<?php echo $productOldPrice; ?></span>
                <span class="amount">&#8358;<?php echo $productPrice; ?></span>
            </div>
            <div class="pricing-features">
                <ul>
                    <li><span class="fi">&#10003;</span> 50+ Email Templates</li>
                    <li><span class="fi">&#10003;</span> Automation Workflows</li>
                    <li><span class="fi">&#10003;</span> Autoresponder Setup</li>
                    <li><span class="fi">&#10003;</span> Landing Pages</li>
                    <li><span class="fi">&#10003;</span> List Building Guide</li>
                    <li><span class="fi">&#10003;</span> Community Access</li>
                    <li><span class="fi">&#10003;</span> Lifetime Updates</li>
                    <li><span class="fi">&#10003;</span> Instant Delivery</li>
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

<section class="cta-section">
    <div class="cta-inner">
        <h2 class="syne">Ready to Launch Your<br>Email Business?</h2>
        <p>Join hundreds of marketers who've built profitable email businesses with our premium bundle.</p>
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
        </div>
        <div class="countdown-sub-label" style="text-align:center;margin-top:12px">&#9201; Offer expires in</div>
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
<?php endif; ?>

<!-- Exit Intent Popup -->
<div class="popup-overlay" id="exitPopup">
    <div class="popup-box">
        <button class="popup-close" id="popupClose">&times;</button>
        <div class="popup-icon">&#128293;</div>
        <h2 class="popup-title">Wait &mdash; Get 15% Off!</h2>
        <p class="popup-desc">Don't leave empty-handed! Use this exclusive discount code to get the Email Marketing Premium Bundle at a lower price.</p>
        <div class="popup-code">
            <span id="popupCodeText">LAUNCH15</span>
            <button onclick="copyPopupCode()">Copy</button>
        </div>
        <p class="popup-note">Copy the code and apply it at checkout to save &#8358;9,750</p>
        <p class="popup-savings">&#10003; You save &#8358;9,750 on your order</p>
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
        if (popupShown || sessionStorage.getItem('empPopupSeen')) return;
        if (e.clientY < 5) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
        }
    });
    setTimeout(function() {
        if (!popupShown && !sessionStorage.getItem('empPopupSeen')) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
            sessionStorage.setItem('empPopupSeen', '1');
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
        console.log('Applying coupon:', code);
        fetch('/validate-coupon?code=' + encodeURIComponent(code) + '&amount=65000')
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

    var payBtn = document.getElementById('payBtn');
    if (payBtn) {
        payBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.payWithPaystack();
        });
    }

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