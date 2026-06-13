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
        'source' => 'instagram_growth_system',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(9);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 9)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 9, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'instagram_growth_system',
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
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'Instagram Growth System'], $step->body ?? ''),
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

    $product = Product::where('slug', 'instagram-growth-system')->where('is_active', true)->first();
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
        'source' => 'instagram_growth_system',
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
            DB::table('funnel_leads')->where('funnel_id', 9)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'instagram-growth-system')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Instagram Growth System';
$productPrice = '12,000';
$productOldPrice = '20,000';
$productPriceRaw = 12000;
$productOldRaw = 20000;
$savings = '8,000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Grow to 10K Followers in 30 Days | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --bg:#ffffff;
        --card:rgba(255,255,255,0.95);
        --text:#1E1B4B;
        --accent:#EC4899;
        --secondary:#8B5CF6;
        --gradient-start:#667eea;
        --gradient-end:#f093fb;
    }

    html{scroll-behavior:smooth}
    body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%) fixed, var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden;min-height:100vh}

    h1,h2,h3,h4{font-family:'Bricolage Grotesque',sans-serif;letter-spacing:-0.02em}

    .noise{position:fixed;inset:0;z-index:9999;pointer-events:none;opacity:.03;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 300 300' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}

    .nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:20px 40px;display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-bottom:1px solid rgba(102,126,234,0.1)}
    .nav-brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:1.1rem;color:var(--text);text-decoration:none}
    .nav-brand svg{width:28px;height:28px}
    .nav-brand svg rect{fill:var(--accent)}
    .nav-brand svg path{stroke:#fff;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

    .hero{padding:140px 40px 80px;position:relative;overflow:hidden}
    .hero::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.85) 100%);z-index:-1}
    .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;max-width:1400px;margin:0 auto;align-items:center}
    .hero-text{position:relative;z-index:2}
    .eyebrow{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg, var(--accent), var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-weight:600;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:16px}
    .eyebrow .dot{width:8px;height:8px;background:var(--accent);border-radius:50%;-webkit-appearance:none;-webkit-text-fill-color:var(--accent)}
    .hero-title{font-size:3.2rem;font-weight:800;line-height:1.1;margin-bottom:20px;background:linear-gradient(135deg, var(--text) 0%, var(--secondary) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .hero-title span{color:var(--accent)}
    .hero-sub{font-size:1.15rem;color:rgba(30,27,75,0.7);margin-bottom:32px;max-width:520px}
    .price-block{display:flex;align-items:center;gap:16px;margin-bottom:28px;flex-wrap:wrap}
    .price-current{font-size:2.5rem;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;color:var(--text)}
    .price-orig{font-size:1.3rem;color:rgba(30,27,75,0.5);text-decoration:line-through}
    .price-badge{background:linear-gradient(135deg, var(--accent), var(--secondary));color:#fff;padding:8px 16px;border-radius:50px;font-size:0.85rem;font-weight:700}
    .cta-btn{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg, var(--accent), var(--secondary));color:#fff;padding:18px 36px;border-radius:12px;font-weight:700;font-size:1.05rem;text-decoration:none;transition:all .3s;box-shadow:0 10px 40px rgba(236,72,153,0.3)}
    .cta-btn:hover{transform:translateY(-3px);box-shadow:0 15px 50px rgba(236,72,153,0.4)}
    .cta-arrow{transition:transform .3s}
    .cta-btn:hover .cta-arrow{transform:translateX(5px)}
    .countdown-bar{display:flex;align-items:center;gap:16px;margin-top:20px}
    .countdown-timer{display:flex;gap:4px}
    .c-t{display:flex;flex-direction:column;align-items:center;background:rgba(102,126,234,0.1);padding:8px 12px;border-radius:8px;min-width:50px}
    .c-t .num{font-size:1.2rem;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;color:var(--accent)}
    .c-t .lbl{font-size:0.65rem;color:rgba(30,27,75,0.5);text-transform:uppercase}
    .countdown-sub-label{font-size:0.85rem;color:rgba(30,27,75,0.6)}
    .stats-row{display:flex;gap:32px;margin-top:40px;flex-wrap:wrap}
    .stats-item{text-align:center}
    .stats-item .val{font-size:1.8rem;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;background:linear-gradient(135deg, var(--accent), var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .stats-item .lbl{font-size:0.8rem;color:rgba(30,27,75,0.6)}
    .hero-right{position:relative}
    .hero-image{position:relative;z-index:2}
    .hero-image-placeholder{width:100%;aspect-ratio:1;max-width:400px;margin:0 auto;background:linear-gradient(135deg, var(--gradient-start), var(--gradient-end));border-radius:24px;display:flex;align-items:center;justify-content:center;font-size:6rem;box-shadow:0 30px 60px rgba(102,126,234,0.3)}
    .hero-image svg{width:100%;height:auto;border-radius:24px;box-shadow:0 30px 60px rgba(102,126,234,0.3)}

    .features-section{padding:80px 40px;background:linear-gradient(180deg, rgba(255,255,255,0.9) 0%, var(--bg) 100%)}
    .features-grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    .feature-card{background:var(--card);border:1px solid rgba(102,126,234,0.15);border-radius:20px;padding:32px;transition:all .3s;position:relative;overflow:hidden}
    .feature-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(135deg, var(--accent), var(--secondary))}
    .feature-card:hover{transform:translateY(-5px);box-shadow:0 20px 40px rgba(102,126,234,0.2)}
    .feature-icon{width:56px;height:56px;background:linear-gradient(135deg, var(--accent), var(--secondary));border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:20px}
    .feature-card h3{font-size:1.25rem;font-weight:700;margin-bottom:12px;color:var(--text)}
    .feature-card p{font-size:0.95rem;color:rgba(30,27,75,0.6);line-height:1.6}

    .pricing-section{padding:80px 40px;background:var(--bg)}
    .pricing-card{max-width:600px;margin:0 auto;background:var(--card);border-radius:32px;padding:48px;text-align:center;position:relative;overflow:hidden;border:2px solid transparent;background-image:linear-gradient(var(--card), var(--card)),linear-gradient(135deg, var(--accent), var(--secondary));background-origin:border-box;background-clip:padding-box,border-box}
    .pricing-card::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg, rgba(102,126,234,0.05), rgba(240,147,251,0.05));z-index:-1}
    .pricing-label{background:linear-gradient(135deg, var(--accent), var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-weight:700;font-size:0.9rem;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:16px}
    .pricing-price{display:flex;align-items:baseline;justify-content:center;gap:12px;margin-bottom:8px}
    .pricing-price .amount{font-size:4rem;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;background:linear-gradient(135deg, var(--text), var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .pricing-price .currency{font-size:1.5rem;color:rgba(30,27,75,0.5)}
    .pricing-old{font-size:1.2rem;color:rgba(30,27,75,0.5);text-decoration:line-through;margin-bottom:24px}
    .pricing-savings{background:linear-gradient(135deg, rgba(236,72,153,0.1), rgba(139,92,246,0.1));color:var(--accent);padding:12px 24px;border-radius:50px;font-weight:700;display:inline-block;margin-bottom:32px}
    .pricing-features{text-align:left;margin-bottom:32px}
    .pricing-features li{list-style:none;padding:12px 0;border-bottom:1px solid rgba(102,126,234,0.1);display:flex;align-items:center;gap:12px;color:rgba(30,27,75,0.8)}
    .pricing-features li::before{content:'✓';color:var(--accent);font-weight:700}
    .pricing-btn{display:block;width:100%;padding:20px;background:linear-gradient(135deg, var(--accent), var(--secondary));color:#fff;border:none;border-radius:14px;font-size:1.1rem;font-weight:700;cursor:pointer;transition:all .3s;box-shadow:0 10px 40px rgba(236,72,153,0.3)}
    .pricing-btn:hover{transform:translateY(-3px);box-shadow:0 15px 50px rgba(236,72,153,0.4)}
    .pricing-note{color:rgba(30,27,75,0.5);font-size:0.85rem;margin-top:20px}

    .cta-section{padding:80px 40px;background:linear-gradient(135deg, var(--gradient-start), var(--gradient-end));position:relative;overflow:hidden}
    .cta-section::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");opacity:0.3}
    .cta-content{max-width:700px;margin:0 auto;text-align:center;position:relative;z-index:2}
    .cta-content h2{font-size:2.5rem;font-weight:800;margin-bottom:16px;color:#fff}
    .cta-content p{font-size:1.1rem;color:rgba(255,255,255,0.8);margin-bottom:32px}
    .cta-section .cta-btn{background:#fff;color:var(--accent);box-shadow:0 10px 40px rgba(0,0,0,0.2)}
    .cta-section .cta-btn:hover{transform:translateY(-3px);box-shadow:0 15px 50px rgba(0,0,0,0.3)}

    .footer{padding:40px;background:var(--text);color:rgba(255,255,255,0.6);text-align:center}
    .footer a{color:rgba(255,255,255,0.8)}

    .checkout-wrap{padding:120px 0;min-height:100dvh;background:linear-gradient(135deg, var(--gradient-start), var(--gradient-end)) fixed, var(--bg)}
    .checkout-grid{max-width:1000px;margin:0 auto;padding:0 40px;display:grid;grid-template-columns:1fr 380px;gap:60px;align-items:start}
    .checkout-left{}
    .checkout-left h1{font-family:'Bricolage Grotesque',sans-serif;font-size:2.4rem;font-weight:700;margin-bottom:8px;letter-spacing:-.02em;color:var(--text)}
    .checkout-left .sub{font-size:.95rem;color:rgba(30,27,75,0.6);margin-bottom:40px}
    .form-card{background:var(--card);border:1px solid rgba(102,126,234,0.2);border-radius:16px;padding:32px}
    .error-msg{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:20px;display:none;font-size:.9rem}
    .error-msg.show{display:block}
    .field-group{margin-bottom:20px}
    .field-group label{display:block;font-weight:600;margin-bottom:8px;font-size:.9rem;color:var(--text)}
    .field-group input{width:100%;padding:14px 16px;border:1px solid rgba(102,126,234,0.2);border-radius:10px;font-size:1rem;font-family:inherit;transition:border-color .2s}
    .field-group input:focus{outline:none;border-color:var(--accent)}
    .coupon-row{display:flex;gap:12px;margin-bottom:16px}
    .coupon-row input{flex:1}
    .coupon-row button{padding:14px 24px;background:rgba(102,126,234,0.1);border:1px solid rgba(102,126,234,0.2);border-radius:10px;font-weight:600;cursor:pointer;transition:all .2s;color:var(--text)}
    .coupon-row button:hover{background:rgba(102,126,234,0.2)}
    .coupon-msg{font-size:.85rem;margin-bottom:20px;display:none}
    .coupon-msg.success{color:#16a34a}
    .coupon-msg.error{color:#dc2626}
    .pay-btn{width:100%;padding:18px;background:linear-gradient(135deg, var(--accent), var(--secondary));color:#fff;border:none;border-radius:12px;font-size:1.1rem;font-weight:700;cursor:pointer;transition:all .3s;box-shadow:0 10px 30px rgba(236,72,153,0.3);display:flex;align-items:center;justify-content:center;gap:10px}
    .pay-btn:hover{transform:translateY(-2px);box-shadow:0 15px 40px rgba(236,72,153,0.4)}
    .pay-btn.loading{opacity:0.7;pointer-events:none}
    .pay-btn .btn-text{display:flex;align-items:center;gap:8px}
    .pay-btn .spinner{display:none}
    .pay-btn.loading .btn-text{display:none}
    .pay-btn.loading .spinner{display:flex;align-items:center;gap:8px}
    .spin{width:18px;height:18px;border:2px solid rgba(255,255,255,0.3);border-top-color:#fff;border-radius:50%;animation:spin .8s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .security-note{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;font-size:.8rem;color:rgba(30,27,75,0.5)}
    .trust-row{display:flex;justify-content:center;gap:24px;margin-top:24px;flex-wrap:wrap}
    .trust-item{display:flex;align-items:center;gap:6px;font-size:.8rem;color:rgba(30,27,75,0.6)}
    .checkout-right{}
    .order-summary{background:var(--card);border:1px solid rgba(102,126,234,0.2);border-radius:16px;padding:28px;position:sticky;top:20px}
    .summary-header{border-bottom:1px solid rgba(102,126,234,0.1);padding-bottom:16px;margin-bottom:16px}
    .summary-header h3{font-size:1.2rem;font-weight:700;margin-bottom:4px}
    .summary-header p{font-size:.9rem;color:rgba(30,27,75,0.6)}
    .product-row{display:flex;gap:16px;padding:16px 0;border-bottom:1px solid rgba(102,126,234,0.1)}
    .product-img{width:64px;height:64px;background:linear-gradient(135deg, var(--accent), var(--secondary));border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.8rem}
    .product-info{flex:1}
    .product-info h4{font-size:.95rem;font-weight:600;margin-bottom:4px}
    .product-info .pdesc{font-size:.8rem;color:rgba(30,27,75,0.5);margin-bottom:8px}
    .product-tag{background:rgba(102,126,234,0.1);color:var(--secondary);padding:4px 10px;border-radius:50px;font-size:.7rem;font-weight:600}
    .price-breakdown{padding:16px 0}
    .price-line{display:flex;justify-content:space-between;margin-bottom:10px;font-size:.9rem}
    .price-line .lbl{color:rgba(30,27,75,0.6)}
    .price-line .val{font-weight:600}
    .price-line.discount{color:var(--accent)}
    .price-line.total{border-top:1px solid rgba(102,126,234,0.1);padding-top:12px;font-size:1.1rem;font-weight:700}
    .price-line.total .val{color:var(--accent);font-size:1.3rem}
    .summary-countdown{background:rgba(102,126,234,0.08);border-radius:12px;padding:16px;text-align:center;margin-top:16px}
    .sc-label{font-size:.8rem;color:rgba(30,27,75,0.6);margin-bottom:12px}
    .countdown-timer{display:flex;justify-content:center;gap:8px;margin-bottom:12px}
    .c-t{display:flex;flex-direction:column;align-items:center;background:var(--card);padding:8px 14px;border-radius:8px;min-width:48px}
    .c-t .num{font-size:1.1rem;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;color:var(--accent)}
    .c-t .lbl{font-size:.6rem;color:rgba(30,27,75,0.5);text-transform:uppercase}
    .sc-note{font-size:.8rem;color:var(--accent);font-weight:600}
    .guarantee-row{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px;font-size:.8rem;color:#16a34a}

    .exit-popup{position:fixed;inset:0;background:rgba(30,27,75,0.8);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;visibility:hidden;transition:all .3s}
    .exit-popup.show{opacity:1;visibility:visible}
    .popup-content{background:var(--card);border-radius:24px;padding:48px;max-width:480px;text-align:center;position:relative;transform:scale(0.9);transition:transform .3s}
    .exit-popup.show .popup-content{transform:scale(1)}
    .popup-close{position:absolute;top:16px;right:16px;width:40px;height:40px;background:rgba(102,126,234,0.1);border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--text)}
    .popup-icon{font-size:3rem;margin-bottom:16px}
    .popup-title{font-size:1.8rem;font-weight:800;margin-bottom:12px;background:linear-gradient(135deg, var(--accent), var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .popup-desc{color:rgba(30,27,75,0.7);margin-bottom:24px}
    .popup-code{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:16px}
    .popup-code span{background:linear-gradient(135deg, var(--accent), var(--secondary));color:#fff;padding:12px 24px;border-radius:10px;font-size:1.3rem;font-weight:700;letter-spacing:0.1em}
    .popup-code button{padding:12px 20px;background:var(--card);border:1px solid rgba(102,126,234,0.3);border-radius:10px;font-weight:600;cursor:pointer;color:var(--text)}
    .popup-note{color:rgba(30,27,75,0.6);font-size:.9rem;margin-bottom:8px}
    .popup-savings{color:var(--accent);font-weight:700;margin-bottom:20px}
    .popup-cta{display:inline-block;padding:16px 32px;background:linear-gradient(135deg, var(--accent), var(--secondary));color:#fff;border-radius:12px;font-weight:700;text-decoration:none;transition:all .3s}
    .popup-cta:hover{transform:translateY(-2px)}
    .popup-timer{color:rgba(30,27,75,0.5);font-size:.85rem;margin-top:16px}

    .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer span,.pricing-timer span,.final-cta-timer span{color:rgba(30,27,75,0.6);font-size:14px;font-weight:500}
    .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .summary-image{width:64px;height:64px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--secondary));display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:rgba(30,27,75,0.6);font-weight:500}
    .summary-timer strong{color:var(--accent);font-weight:700}

    @media(max-width:1024px){.hero-grid,.checkout-grid{grid-template-columns:1fr;gap:40px}.hero-right{order:-1}.hero-sub{margin:0 auto 32px}.price-block{margin:0 auto 28px}.stats-row{justify-content:center;gap:24px}.features-grid{grid-template-columns:1fr}.footer-inner{flex-direction:column;gap:16px;text-align:center}.checkout-grid{padding:0 16px}.order-summary{position:static;margin-top:32px}.checkout-left,.checkout-right{width:100%;padding:0}}
    @media(max-width:768px){.hero-grid{grid-template-columns:1fr;gap:24px}.hero{padding:100px 16px 40px}.hero-title{font-size:clamp(1.8rem,6vw,2.5rem)}.hero-sub{font-size:.9rem}.nav{padding:8px 16px;top:8px}.nav-brand span{display:none}.price-block{flex-wrap:wrap;padding:12px 16px;justify-content:center}.price-current{font-size:1.6rem}.cta-btn{padding:14px 24px;font-size:.9rem;width:100%;justify-content:center}.countdown-bar{max-width:100%}.timer-value{font-size:1.5rem}.stats-row{grid-template-columns:repeat(2,1fr);gap:12px}.stat-item{padding:16px 8px}.features-grid{grid-template-columns:1fr;gap:16px}.section{padding:60px 16px}.section-title{font-size:clamp(1.5rem,5vw,2rem)}.footer{padding:40px 16px 30px}.pricing-card{padding:32px 20px}.pricing-price .amount{font-size:2.2rem}.pricing-features li{font-size:.85rem}.cta-section{padding:60px 16px}.checkout-page{padding:20px 16px}.checkout-form{padding:24px 16px}.field-group input{padding:12px 14px;font-size:.9rem}.pay-btn{padding:16px;font-size:.95rem;width:100%}.order-summary-box{padding:20px 16px}.timer-sticky{padding:10px 16px;font-size:.8rem;width:calc(100% - 32px);left:16px;transform:none;bottom:12px}.exit-popup-box{padding:32px 20px;margin:16px}.exit-popup-box h2{font-size:1.4rem}.exit-code-wrap input{font-size:1rem;padding:12px}.exit-link{padding:14px 24px;font-size:.9rem}}
    @media(max-width:480px){.hero{padding:90px 12px 32px}.hero-title{font-size:1.6rem;letter-spacing:-.02em}.eyebrow{padding:4px 12px;font-size:.65rem}.price-new{font-size:2rem}.timer-box{padding:14px}.timer-value{font-size:1.3rem}.feature-card,.pricing-card{padding:20px}.feature-card h3{font-size:.95rem}.btn-primary,.cta-btn{font-size:.9rem;padding:12px 20px}.strip-bar{padding:8px 12px;font-size:.7rem}.nav{top:0;border-radius:0}.section{padding:48px 12px}.section-title{font-size:1.4rem}.cta-section{padding:40px 12px;border-radius:16px}.cta-section h2{font-size:1.5rem}.pricing-card{border-radius:16px}.stats-row{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
<div class="noise"></div>

<nav class="nav">
    <a href="/" class="nav-brand">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="6"/><path d="M6 14l4-8 4 8" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
                    <button type="button">Apply</button>
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
                        <div class="product-img">&#128241;</div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($productTitle); ?></h4>
                            <p class="pdesc">90-day content calendar, hashtag strategy, engagement scripts & more</p>
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
            <div class="eyebrow"><span class="dot"></span>Instagram Growth on Autopilot</div>
            <h1 class="hero-title">Grow to 10K Followers in 30 Days &mdash; The Proven System Used by Nigerian Creators</h1>
            <p class="hero-sub">Content calendar, hashtag strategy, engagement scripts, reel templates & growth tracking spreadsheet</p>
            <div class="price-block">
                <span class="price-current">&#8358;<?php echo $productPrice; ?></span>
                <span class="price-orig">&#8358;<?php echo $productOldPrice; ?></span>
                <span class="price-badge">SAVE &#8358;<?php echo $savings; ?></span>
            </div>
            <div class="cta-group">
                <a href="?step=checkout" class="cta-btn">
                    Get Started Now
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
                <div class="stats-item"><div class="val">10K</div><div class="lbl">Follower System</div></div>
                <div class="stats-item"><div class="val">90</div><div class="lbl">Day Content Calendar</div></div>
                <div class="stats-item"><div class="val">500+</div><div class="lbl">Hashtag Strategy</div></div>
            </div>
        </div>
        <div class="hero-right">
            <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="400" rx="32" fill="white" fill-opacity="0.1"/>
                <rect x="20" y="20" width="110" height="110" rx="16" fill="url(#grad1)"/>
                <rect x="145" y="20" width="110" height="110" rx="16" fill="url(#grad2)"/>
                <rect x="270" y="20" width="110" height="110" rx="16" fill="url(#grad3)"/>
                <rect x="20" y="145" width="110" height="110" rx="16" fill="url(#grad4)"/>
                <rect x="145" y="145" width="110" height="110" rx="16" fill="url(#grad5)"/>
                <rect x="270" y="145" width="110" height="110" rx="16" fill="url(#grad6)"/>
                <rect x="20" y="270" width="110" height="110" rx="16" fill="url(#grad7)"/>
                <rect x="145" y="270" width="110" height="110" rx="16" fill="url(#grad8)"/>
                <rect x="270" y="270" width="110" height="110" rx="16" fill="url(#grad9)"/>
                <circle cx="200" cy="200" r="40" fill="white" fill-opacity="0.9"/>
                <path d="M185 200L195 210L215 190" stroke="#EC4899" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <defs>
                    <linearGradient id="grad1" x1="20" y1="20" x2="130" y2="130" gradientUnits="userSpaceOnUse"><stop stop-color="#667eea"/><stop offset="1" stop-color="#764ba2"/></linearGradient>
                    <linearGradient id="grad2" x1="145" y1="20" x2="255" y2="130" gradientUnits="userSpaceOnUse"><stop stop-color="#f093fb"/><stop offset="1" stop-color="#f5576c"/></linearGradient>
                    <linearGradient id="grad3" x1="270" y1="20" x2="380" y2="130" gradientUnits="userSpaceOnUse"><stop stop-color="#4facfe"/><stop offset="1" stop-color="#00f2fe"/></linearGradient>
                    <linearGradient id="grad4" x1="20" y1="145" x2="130" y2="255" gradientUnits="userSpaceOnUse"><stop stop-color="#fa709a"/><stop offset="1" stop-color="#fee140"/></linearGradient>
                    <linearGradient id="grad5" x1="145" y1="145" x2="255" y2="255" gradientUnits="userSpaceOnUse"><stop stop-color="#a8edea"/><stop offset="1" stop-color="#fed6e3"/></linearGradient>
                    <linearGradient id="grad6" x1="270" y1="145" x2="380" y2="255" gradientUnits="userSpaceOnUse"><stop stop-color="#ff9a9e"/><stop offset="1" stop-color="#fecfef"/></linearGradient>
                    <linearGradient id="grad7" x1="20" y1="270" x2="130" y2="380" gradientUnits="userSpaceOnUse"><stop stop-color="#667eea"/><stop offset="1" stop-color="#764ba2"/></linearGradient>
                    <linearGradient id="grad8" x1="145" y1="270" x2="255" y2="380" gradientUnits="userSpaceOnUse"><stop stop-color="#f093fb"/><stop offset="1" stop-color="#f5576c"/></linearGradient>
                    <linearGradient id="grad9" x1="270" y1="270" x2="380" y2="380" gradientUnits="userSpaceOnUse"><stop stop-color="#4facfe"/><stop offset="1" stop-color="#00f2fe"/></linearGradient>
                </defs>
            </svg>
        </div>
    </div>
</section>

<section class="features-section">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">&#128197;</div>
            <h3>90-Day Content Calendar</h3>
            <p>Plan your content for the next 3 months with our ready-to-use content calendar. Never run out of ideas again.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">#</div>
            <h3>Hashtag Strategy Guide (500+)</h3>
            <p>Unlock the power of hashtags with our curated list of 500+ high-performing hashtags for Nigerian creators.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128172;</div>
            <h3>Engagement Comment Scripts</h3>
            <p> Proven comment scripts that actually get responses and build meaningful connections with your audience.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#127909;</div>
            <h3>Reel Template Ideas (50+)</h3>
            <p>50+ ready-to-edit reel templates that boost engagement and help your content go viral.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128200;</div>
            <h3>Growth Tracking Spreadsheet</h3>
            <p>Track your follower growth, engagement rate, and content performance with our custom Google Sheets.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128176;</div>
            <h3>Creator Monetization Tips</h3>
            <p>Learn how to turn your following into income with proven monetization strategies for Nigerian creators.</p>
        </div>
    </div>
</section>

<section class="pricing-section">
    <div class="pricing-card">
        <div class="pricing-label">One-Time Payment</div>
        <div class="pricing-price">
            <span class="currency">&#8358;</span>
            <span class="amount">12,000</span>
        </div>
        <div class="pricing-old">Was &#8358;20,000</div>
        <div class="pricing-savings">You save &#8358;8,000</div>
        <ul class="pricing-features">
            <li>90-Day Content Calendar</li>
            <li>500+ Hashtag Strategy Guide</li>
            <li>Engagement Comment Scripts</li>
            <li>50+ Reel Template Ideas</li>
            <li>Growth Tracking Spreadsheet</li>
            <li>Creator Monetization Tips</li>
            <li>Instant Digital Download</li>
        </ul>
        <a href="?step=checkout" class="pricing-btn">Get Instant Access</a>
        <p class="pricing-note">30-day money-back guarantee &bull; Instant delivery</p>
    </div>
</section>

<section class="cta-section">
    <div class="cta-content">
        <h2>Ready to Transform Your Instagram?</h2>
        <p>Join thousands of Nigerian creators who have grown their following using our proven system.</p>
        <div class="cta-group">
            <a href="?step=checkout" class="cta-btn">
                Get Started Now &mdash; &#8358;12,000
                <span class="cta-arrow">&#8594;</span>
            </a>
        </div>
        <div class="countdown-bar" style="justify-content:center;margin-top:24px">
            <div class="countdown-timer">
                <div class="c-t"><span class="num" id="h2">00</span><span class="lbl">Hrs</span></div>
                <div class="c-t"><span class="num" id="m2">00</span><span class="lbl">Min</span></div>
                <div class="c-t"><span class="num" id="s2">00</span><span class="lbl">Sec</span></div>
            </div>
            <span class="countdown-sub-label" style="color:rgba(255,255,255,0.8)">&#9201; Offer expires in</span>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="exit-popup" id="exitPopup">
    <div class="popup-content">
        <button class="popup-close" id="popupClose">&times;</button>
        <div class="popup-icon">&#128293;</div>
        <h2 class="popup-title">Wait &mdash; Get 15% Off!</h2>
        <p class="popup-desc">Don't leave empty-handed! Use this exclusive discount code to get the Instagram Growth System at a lower price.</p>
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

<footer class="footer">
    <p>&copy; 2026 Joala Digital. All rights reserved.</p>
</footer>

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
        if (popupShown || sessionStorage.getItem('igsPopupSeen')) return;
        if (e.clientY < 5) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
        }
    });
    setTimeout(function() {
        if (!popupShown && !sessionStorage.getItem('igsPopupSeen')) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
            sessionStorage.setItem('igsPopupSeen', '1');
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
        fetch('/validate-coupon?code=' + encodeURIComponent(code) + '&amount=12000')
            .then(function(r) { return r.json(); })
            .then(function(data) {
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