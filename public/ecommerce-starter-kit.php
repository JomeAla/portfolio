<?php
error_reporting(0);
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

session_start();
$canShowPopup = !isset($_SESSION['ecom_exit_shown']);



use Illuminate\Support\Facades\DB;
use App\Models\Funnel;
use App\Models\FunnelLead;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Setting;
use App\Models\EmailQueue;

$step = $_GET['step'] ?? 'landing';
$urlCoupon = $_GET['coupon'] ?? '';
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
        'source' => 'ecommerce_sales_page',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(3);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 3)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 3, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'ecommerce_sales_page',
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
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'E-commerce Starter Kit'], $step->body ?? ''),
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
    error_log("INIT_PAYMENT: POST received, action=" . ($_POST['action'] ?? 'NONE'));

    $emailInput = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $nameInput = trim($_POST['name'] ?? '');
    $phoneInput = trim($_POST['phone'] ?? '');
    $couponCode = trim($_POST['coupon_code'] ?? '');

    if (!$emailInput || !$nameInput || !$phoneInput) {
        echo json_encode(['error' => 'Please fill in all required fields.']);
        exit;
    }

    $product = Product::where('slug', 'ecommerce-starter-kit')->first();
    if (!$product) {
        $product = Product::where('slug', 'e-commerce-starter-kit')->first();
    }
    error_log("INIT_PAYMENT: final product: " . ($product ? $product->id . '/' . $product->slug : 'NULL'));
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
        'source' => 'ecommerce_sales_page',
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

    $paystackPublicKey = Setting::get('paystack_public_key') ?? 'pk_live_xxxxxxxxxxxx';

    echo json_encode([
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'paystack_public_key' => $paystackPublicKey,
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
            DB::table('funnel_leads')->where('funnel_id', 3)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'ecommerce-starter-kit')->first();
if (!$product) { $product = Product::where('slug', 'e-commerce-starter-kit')->first(); }
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'E-commerce Starter Kit';
$productPrice = $product ? number_format((float)($product->sale_price ?? $product->price), 0) : '55,000';
$productOldPrice = $product ? number_format((float)$product->price, 0) : '85,000';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 55000;
$productOldRaw = $product ? (float)$product->price : 85000;
$savings = number_format($productOldRaw - $productPriceRaw, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Launch Your Online Store in 48 Hours | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://js.paystack.co/v2/inline.js"></script>
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --bg:#0D0D0D;
        --text:#F5F0E8;
        --accent:#FF4D00;
        --accent-hover:#ff6a2a;
        --card:#1A1A1A;
        --card-hover:#242424;
        --gold:#F59E0B;
        --gold-dim:#d97706;
        --muted:#9ca3af;
        --success:#22c55e;
    }

    html{scroll-behavior:smooth}
    body{font-family:'DM Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

    .heading{font-family:'Space Grotesk',sans-serif}

    .container{max-width:1200px;margin:0 auto;padding:0 20px}

    section{padding:80px 0}

    .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:16px 32px;font-size:16px;font-weight:600;border-radius:8px;text-decoration:none;cursor:pointer;border:none;transition:all .3s}
    .btn-primary{background:var(--accent);color:#fff}
    .btn-primary:hover{background:var(--accent-hover);transform:translateY(-2px);box-shadow:0 10px 30px rgba(255,77,0,0.3)}
    .btn-gold{background:var(--gold);color:#000}
    .btn-gold:hover{background:var(--gold-dim);transform:translateY(-2px);box-shadow:0 10px 30px rgba(245,158,11,0.3)}

    .hero{background:linear-gradient(180deg,#0D0D0D 0%,#151515 100%);min-height:100vh;display:flex;align-items:center;padding:100px 0}
    .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
    .hero-content{max-width:600px}
    .hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,77,0,0.15);color:var(--accent);padding:8px 16px;border-radius:50px;font-size:14px;font-weight:600;margin-bottom:24px}
    .hero-badge svg{width:16px;height:16px}
    .hero h1{font-size:clamp(36px,5vw,64px);font-weight:800;line-height:1.1;margin-bottom:24px}
    .hero p{font-size:18px;color:var(--muted);margin-bottom:32px;max-width:500px}
    .hero-price{display:flex;align-items:center;gap:20px;margin-bottom:32px;flex-wrap:wrap}
    .hero-price-current{font-size:48px;font-weight:700;font-family:'Space Grotesk',sans-serif}
    .hero-price-old{font-size:24px;color:var(--muted);text-decoration:line-through}
    .hero-save{background:var(--accent);color:#fff;padding:8px 16px;border-radius:6px;font-weight:700;font-size:14px}
    .hero-timer{display:flex;align-items:center;gap:12px;margin-bottom:32px;padding:16px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer svg{width:24px;height:24px;color:var(--accent);flex-shrink:0}
    .hero-timer span{color:var(--muted);font-size:14px;font-weight:500}
    .hero-timer strong{color:var(--accent);font-weight:800;font-size:18px;letter-spacing:0.05em}
    .hero-stats{display:flex;gap:40px;margin-top:48px;flex-wrap:wrap}
    .hero-stat{text-align:center}
    .hero-stat-num{font-size:32px;font-weight:700;font-family:'Space Grotesk',sans-serif;color:var(--gold)}
    .hero-stat-label{font-size:14px;color:var(--muted)}
    .hero-visual{position:relative}
    .hero-image{width:100%;border-radius:16px;box-shadow:0 25px 80px rgba(0,0,0,0.5);border:1px solid #333}
    .hero-glow{position:absolute;top:-50px;right:-50px;width:200px;height:200px;background:radial-gradient(circle,rgba(255,77,0,0.3) 0%,transparent 70%);filter:blur(40px)}

    .features{background:#0a0a0a}
    .features h2{font-size:clamp(32px,4vw,48px);font-weight:700;margin-bottom:16px;text-align:center}
    .features-subtitle{text-align:center;color:var(--muted);margin-bottom:60px;font-size:18px}
    .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px}
    .feature-card{background:var(--card);border:1px solid #2a2a2a;border-radius:16px;padding:32px;transition:all .3s}
    .feature-card:hover{background:var(--card-hover);border-color:var(--accent);transform:translateY(-4px)}
    .feature-icon{width:56px;height:56px;background:linear-gradient(135deg,var(--accent),#ff7a3d);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:20px}
    .feature-icon svg{width:28px;height:28px;color:#fff}
    .feature-card h3{font-size:20px;font-weight:700;margin-bottom:12px;font-family:'Space Grotesk',sans-serif}
    .feature-card p{color:var(--muted);font-size:15px}

    .how-it-works{background:var(--bg)}
    .how-it-works h2{font-size:clamp(32px,4vw,48px);font-weight:700;margin-bottom:16px;text-align:center}
    .how-it-works .subtitle{text-align:center;color:var(--muted);margin-bottom:60px;font-size:18px}
    .steps{display:grid;grid-template-columns:repeat(3,1fr);gap:40px;position:relative}
    .steps::before{content:'';position:absolute;top:60px;left:15%;right:15%;height:2px;background:linear-gradient(90deg,var(--accent),var(--gold));opacity:0.3}
    .step{text-align:center;position:relative;z-index:1}
    .step-num{width:80px;height:80px;background:linear-gradient(135deg,var(--accent),#ff7a3d);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;font-family:'Space Grotesk',sans-serif;color:#fff;margin:0 auto 24px}
    .step h3{font-size:24px;font-weight:700;margin-bottom:12px;font-family:'Space Grotesk',sans-serif}
    .step p{color:var(--muted);max-width:300px;margin:0 auto}

    .pricing{background:linear-gradient(180deg,#0a0a0a 0%,#0D0D0D 100%);padding:100px 0}
    .pricing-card{max-width:600px;margin:0 auto;background:var(--card);border:1px solid #2a2a2a;border-radius:24px;padding:48px;text-align:center;position:relative;overflow:hidden}
    .pricing-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--accent),var(--gold))}
    .pricing-badge{position:absolute;top:24px;right:-30px;background:var(--accent);color:#fff;padding:8px 40px;font-weight:700;font-size:12px;transform:rotate(45deg)}
    .pricing h2{font-size:clamp(28px,4vw,40px);font-weight:700;margin-bottom:8px;font-family:'Space Grotesk',sans-serif}
    .pricing-price{font-size:72px;font-weight:800;font-family:'Space Grotesk',sans-serif;margin:24px 0}
    .pricing-price span{font-size:24px;font-weight:400;color:var(--muted)}
    .pricing-old{color:var(--muted);text-decoration:line-through;font-size:24px}
    .pricing-features{list-style:none;margin:32px 0;text-align:left;max-width:400px;margin-left:auto;margin-right:auto}
    .pricing-features li{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #2a2a2a}
    .pricing-features li:last-child{border-bottom:none}
    .pricing-features svg{width:20px;height:20px;color:var(--success);flex-shrink:0}
    .pricing .btn{width:100%;margin-top:24px}
    .pricing-timer{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:24px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content;margin-left:auto;margin-right:auto}
    .pricing-timer svg{width:22px;height:22px;color:var(--accent);flex-shrink:0}
    .pricing-timer span{color:var(--muted);font-size:14px;font-weight:500}
    .pricing-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .pricing-guarantee{display:flex;align-items:center;justify-content:center;gap:12px;margin-top:24px;color:var(--muted);font-size:14px}
    .pricing-guarantee svg{width:20px;height:20px;color:var(--gold)}

    .testimonial{background:#0a0a0a}
    .testimonial-content{max-width:800px;margin:0 auto;text-align:center}
    .testimonial-quote{font-size:clamp(24px,3vw,36px);font-weight:600;line-height:1.4;margin-bottom:32px;font-family:'Space Grotesk',sans-serif;color:var(--text)}
    .testimonial-author{display:flex;align-items:center;justify-content:center;gap:16px}
    .testimonial-avatar{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--gold));display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700}
    .testimonial-name{font-weight:700;font-size:18px}
    .testimonial-role{color:var(--muted);font-size:14px}

    .final-cta{background:linear-gradient(135deg,#1a1814 0%,#2a2520 100%);border-top:1px solid #3a3530}
    .final-cta .container{text-align:center}
    .final-cta h2{font-size:clamp(32px,4vw,48px);font-weight:700;margin-bottom:16px;font-family:'Space Grotesk',sans-serif}
    .final-cta p{color:var(--muted);margin-bottom:32px;font-size:18px}
    .final-cta .btn{margin-bottom:24px}
    .final-cta-timer{display:flex;align-items:center;justify-content:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content;margin:0 auto;color:var(--muted)}
    .final-cta-timer svg{width:22px;height:22px;color:var(--accent);flex-shrink:0}
    .final-cta-timer span{font-size:14px;font-weight:500}
    .final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}

    .checkout{background:var(--bg);padding:60px 0;display:<?php echo $step === 'checkout' ? 'block' : 'none'; ?>}
    .checkout-grid{display:grid;grid-template-columns:1fr 400px;gap:40px;align-items:start}
    .checkout-form{background:var(--card);border:1px solid #2a2a2a;border-radius:16px;padding:40px}
    .checkout-form h2{font-size:28px;font-weight:700;margin-bottom:32px;font-family:'Space Grotesk',sans-serif}
    .form-group{margin-bottom:20px}
    .form-group label{display:block;font-weight:600;margin-bottom:8px}
    .form-group input{width:100%;padding:14px 16px;border:1px solid #333;border-radius:8px;background:#0D0D0D;color:var(--text);font-size:16px;transition:border-color .3s}
    .form-group input:focus{outline:none;border-color:var(--accent)}
    .coupon-row{display:flex;gap:12px;margin-bottom:24px}
    .coupon-row input{flex:1}
    .coupon-row .btn{padding:14px 24px;white-space:nowrap}
    .checkout-summary{background:var(--card);border:1px solid #2a2a2a;border-radius:16px;padding:32px;position:sticky;top:20px}
    .checkout-summary h3{font-size:20px;font-weight:700;margin-bottom:24px;font-family:'Space Grotesk',sans-serif}
    .summary-product{display:flex;gap:16px;padding-bottom:24px;border-bottom:1px solid #2a2a2a;margin-bottom:24px}
    .summary-image{width:80px;height:80px;background:linear-gradient(135deg,var(--accent),#ff7a3d);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-details h4{font-weight:700;margin-bottom:4px}
    .summary-details p{color:var(--muted);font-size:14px}
    .summary-row{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #2a2a2a}
    .summary-row:last-of-type{border-bottom:none}
    .summary-row.total{font-size:20px;font-weight:700;padding-top:20px;border-top:2px solid #2a2a2a}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--muted);font-weight:500}
    .summary-timer strong{color:var(--accent);font-weight:700}

    .exit-popup{position:fixed;inset:0;background:rgba(0,0,0,0.8);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;visibility:hidden;transition:all .3s}
    .exit-popup.active{opacity:1;visibility:visible}
    .exit-popup-content{background:var(--card);border:1px solid #2a2a2a;border-radius:24px;padding:48px;max-width:500px;text-align:center;position:relative;transform:scale(0.9);transition:transform .3s}
    .exit-popup.active .exit-popup-content{transform:scale(1)}
    .exit-popup-close{position:absolute;top:16px;right:16px;width:40px;height:40px;background:#2a2a2a;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center}
    .exit-popup-close svg{width:20px;height:20px;color:var(--muted)}
    .exit-popup h3{font-size:28px;font-weight:700;margin-bottom:16px;font-family:'Space Grotesk',sans-serif}
    .exit-popup p{color:var(--muted);margin-bottom:24px}
    .exit-popup .btn{margin-top:16px}

    footer{background:#050505;border-top:1px solid #1a1a1a;padding:40px 0}
    footer .container{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px}
    footer p{color:var(--muted);font-size:14px}
    footer a{color:var(--muted);text-decoration:none;font-size:14px;transition:color .3s}
    footer a:hover{color:var(--accent)}
    .footer-links{display:flex;gap:24px}

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
<?php if ($step !== 'checkout'): ?>
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Limited Time Offer
                    </div>
                    <h1 class="heading">Launch Your Online Store in 48 Hours — No Coding Required</h1>
                    <p>Complete Laravel e-commerce platform with payment integration, inventory management & admin dashboard</p>
                    <div class="hero-price">
                        <span class="hero-price-current">N<?php echo $productPrice; ?></span>
                        <span class="hero-price-old">N<?php echo $productOldPrice; ?></span>
                        <span class="hero-save">SAVE N<?php echo $savings; ?></span>
                    </div>
                    <a href="?step=checkout" class="btn btn-primary">Get Started Now</a>
                    <div class="hero-timer">
                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Offer expires in <strong id="hero-timer-display">59:59</strong></span>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-num">500+</div>
                            <div class="hero-stat-label">E-commerce Sites Built</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">Paystack</div>
                            <div class="hero-stat-label">WooCommerce Integration</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-num">Lifetime</div>
                            <div class="hero-stat-label">Free Updates</div>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-glow"></div>
                    <?php if ($productImage): ?>
                    <img class="hero-image" src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($productTitle); ?>" style="border-radius:16px;box-shadow:0 25px 80px rgba(0,0,0,0.5);border:1px solid #333">
                <?php else: ?>
                    <svg class="hero-image" viewBox="0 0 600 450" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="600" height="450" fill="#1A1A1A" rx="12"/>
                        <rect x="40" y="30" width="520" height="40" fill="#0D0D0D" rx="8"/>
                        <circle cx="70" cy="50" r="8" fill="#FF4D00"/>
                        <circle cx="96" cy="50" r="8" fill="#333"/>
                        <circle cx="122" cy="50" r="8" fill="#333"/>
                        <rect x="40" y="90" width="180" height="320" fill="#0D0D0D" rx="8"/>
                        <rect x="60" y="110" width="140" height="100" fill="#1A1A1A" rx="8"/>
                        <path d="M80 140 L110 120 L140 150 L170 110 L200 140" stroke="#FF4D00" stroke-width="3" fill="none"/>
                        <rect x="60" y="230" width="140" height="20" fill="#1A1A1A" rx="4"/>
                        <rect x="60" y="260" width="100" height="16" fill="#333" rx="4"/>
                        <rect x="60" y="285" width="120" height="16" fill="#333" rx="4"/>
                        <rect x="240" y="90" width="320" height="40" fill="#1A1A1A" rx="8"/>
                        <rect x="240" y="140" width="150" height="100" fill="#0D0D0D" rx="8"/>
                        <rect x="410" y="140" width="150" height="100" fill="#0D0D0D" rx="8"/>
                        <rect x="240" y="260" width="320" height="30" fill="#1A1A1A" rx="6"/>
                        <rect x="240" y="305" width="100" height="30" fill="#FF4D00" rx="6"/>
                        <rect x="360" y="305" width="100" height="30" fill="#1A1A1A" rx="6"/>
                        <rect x="480" y="305" width="80" height="30" fill="#1A1A1A" rx="6"/>
                        <text x="300" y="410" fill="#666" font-size="14" text-anchor="middle">Admin Dashboard Preview</text>
                    </svg>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2 class="heading">Everything You Need to Sell Online</h2>
            <p class="features-subtitle">Powerful features built for modern e-commerce</p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <h3>Admin Dashboard</h3>
                    <p>Complete control panel with sales analytics, order tracking, and real-time statistics</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3>Product Management</h3>
                    <p>Add, edit, categorize products with images, variants, and detailed descriptions</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <h3>Order Tracking</h3>
                    <p>Full order lifecycle management from checkout to delivery confirmation</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h3>Payment Gateway Integration</h3>
                    <p>Built-in Paystack, Stripe, Flutterwave support with instant settlement</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h3>Inventory System</h3>
                    <p>Stock tracking, low-stock alerts, and automated inventory management</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <h3>SEO & Analytics</h3>
                    <p>Built-in SEO tools, Google Analytics integration, and conversion tracking</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3>Mobile Responsive</h3>
                    <p>Beautiful storefront that looks perfect on any device, desktop or mobile</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <h3>Free Updates</h3>
                    <p>Lifetime access to all future updates, new features, and security patches</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <h3>Physical & Digital Products</h3>
                    <p>Sell physical goods and digital downloads from one store with separate delivery methods</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <h3>Shipping Across Africa & Worldwide</h3>
                    <p>Multi-zone shipping with rate calculation, tracking, and delivery management across Africa and globally</p>
                </div>
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <div class="container">
            <h2 class="heading">How It Works</h2>
            <p class="subtitle">Three simple steps to your online store</p>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <h3>Install Laravel</h3>
                    <p>Download and install the complete Laravel e-commerce package on your server</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h3>Add Products</h3>
                    <p>Use the admin panel to add your products, set prices, and upload images</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h3>Go Live</h3>
                    <p>Connect your domain and start accepting orders immediately</p>
                </div>
            </div>
        </div>
    </section>

    <section style="background: linear-gradient(135deg,#0D0D0D 0%,#1a1814 100%);padding:80px 0">
        <div class="container" style="text-align:center">
            <div style="display:inline-block;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.3);border-radius:24px;padding:48px;max-width:700px">
                <div style="display:inline-block;background:rgba(245,158,11,0.2);color:var(--gold);padding:6px 16px;border-radius:50px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:16px">BONUS</div>
                <h2 class="heading" style="font-size:clamp(28px,3vw,36px);font-weight:700;margin-bottom:16px;color:var(--gold)">Free Deployment & 2 Free Edits</h2>
                <p style="color:var(--muted);font-size:18px;margin-bottom:24px">Purchase the E-commerce Starter Kit and we will deploy your store on your domain <strong style="color:var(--text)">for free</strong> and make <strong style="color:var(--text)">2 additional customizations</strong> of your choice at no extra cost.</p>
                <div style="display:flex;justify-content:center;gap:32px;flex-wrap:wrap;margin-bottom:24px">
                    <div><div style="font-size:32px;font-weight:700;font-family:'Space Grotesk',sans-serif;color:var(--gold)">FREE</div><div style="color:var(--muted);font-size:14px">Deployment</div></div>
                    <div><div style="font-size:32px;font-weight:700;font-family:'Space Grotesk',sans-serif;color:var(--gold)">2</div><div style="color:var(--muted);font-size:14px">Free Edits</div></div>
                    <div><div style="font-size:32px;font-weight:700;font-family:'Space Grotesk',sans-serif;color:var(--gold)">₦150k</div><div style="color:var(--muted);font-size:14px">Valued At</div></div>
                </div>
                <p style="color:var(--muted);font-size:14px;font-style:italic">Valued at ₦150,000 — yours free when you order today</p>
            </div>
        </div>
    </section>

    <section class="pricing">
        <div class="container">
            <div class="pricing-card">
                <div class="pricing-badge">BEST VALUE</div>
                <h2 class="heading">E-commerce Starter Kit</h2>
                <p class="pricing-old">Was N<?php echo $productOldPrice; ?></p>
                <div class="pricing-price">N<?php echo $productPrice; ?><span> one-time</span></div>
                <ul class="pricing-features">
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Complete Laravel E-commerce Platform
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Paystack, Stripe & Flutterwave Integration
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Admin Dashboard with Analytics
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Inventory & Order Management
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Mobile Responsive Design
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Lifetime Free Updates
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Priority Support
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Physical & Digital Product Support
                    </li>
                    <li>
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Africa & Worldwide Shipping
                    </li>
                </ul>
                <a href="?step=checkout" class="btn btn-gold">Get Started Now</a>
                <div class="pricing-timer">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Offer expires in <strong id="pricing-timer-display">59:59</strong></span>
                </div>
                <div class="pricing-guarantee">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    30-Day Money-Back Guarantee
                </div>
            </div>
        </div>
    </section>

    <section class="testimonial">
        <div class="container">
            <div class="testimonial-content">
                <p class="testimonial-quote">"We launched our online store in just 2 days. The admin panel makes managing orders effortless. Best investment we've made."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">AK</div>
                    <div>
                        <div class="testimonial-name">Adebola Kuti</div>
                        <div class="testimonial-role">Fashion Store Owner, Lagos</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="container">
            <h2 class="heading">Ready to Start Selling Online?</h2>
            <p>Join 500+ entrepreneurs who trust our e-commerce solution</p>
            <a href="?step=checkout" class="btn btn-primary">Get Your Store Now — N<?php echo $productPrice; ?></a>
            <div class="final-cta-timer">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Offer expires in <strong id="final-timer-display">59:59</strong></span>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($step === 'checkout'): ?>
    <section class="checkout" style="display: block;">
        <div class="container">
            <div class="checkout-grid">
                <div class="checkout-form">
                    <h2 class="heading">Complete Your Order</h2>
                    <form id="checkout-form">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required placeholder="e.g., 08012345678">
                        </div>
                        <div class="coupon-row">
                            <input type="text" id="coupon-code" placeholder="Enter coupon code">
                            <button type="button" class="btn btn-primary" id="apply-coupon">Apply</button>
                        </div>
                        <div id="coupon-message" style="margin-bottom: 20px; font-size: 14px;"></div>
                        <button type="submit" class="btn btn-gold" id="pay-btn" style="width: 100%;">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Pay N<?php echo $productPrice; ?>
                        </button>
                    </form>
                </div>
                <div class="checkout-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-product">
<div class="summary-image"><?php if ($productImage): ?><img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($productTitle); ?>"><?php else: ?><svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg><?php endif; ?></div>
                        <div class="summary-details">
                            <h4><?php echo htmlspecialchars($productTitle); ?></h4>
                            <p>Instant Download</p>
                        </div>
                    </div>
                    <div class="summary-row">
                        <span>Original Price</span>
                        <span style="text-decoration: line-through; color: var(--muted);">N<?php echo $productOldPrice; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Discount</span>
                        <span id="summary-discount" style="color: var(--success);">-N0</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="summary-total">N<?php echo $productPrice; ?></span>
                    </div>
                    <div class="summary-timer">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="checkout-timer-display">59:59</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>


<div class="exit-popup" id="exit-popup">
    <div class="exit-popup-content">
        <button class="exit-popup-close" id="exit-popup-close">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 class="heading">Wait! Don't Miss Out</h3>
        <p>Get <strong>15% OFF</strong> when you order now with code:</p>
        <div style="background: var(--bg); padding: 16px; border-radius: 8px; margin: 20px 0; font-size: 24px; font-weight: 700; color: var(--accent); font-family: 'Space Grotesk', sans-serif;">LAUNCH15</div>
        <div id="exit-timer-count" style="color: var(--muted); font-size: 14px; margin-bottom: 16px;">Coupon expires in <strong style="color: var(--accent);">05:00</strong></div>
        <button class="btn btn-primary" id="use-coupon-btn">Apply This Coupon</button>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; 2026 Joala Digital. All rights reserved.</p>
        <div class="footer-links">
            <a href="/terms">Terms</a>
            <a href="/privacy">Privacy</a>
            <a href="/contact">Contact</a>
        </div>
    </div>
</footer>

<script>
(function() {
    'use strict';

    let timerInterval;
    let couponApplied = false;
    let discountAmount = 0;
    const basePrice = <?php echo $productPriceRaw; ?>;
    const originalPrice = <?php echo $productOldRaw; ?>;

    function updateTimer(displayId) {
        const endTime = new Date(Date.now() + 3600000);
        
        function tick() {
            const now = new Date();
            const diff = endTime - now;
            
            if (diff <= 0) {
                const newEnd = new Date(Date.now() + 3600000);
                endTime.setTime(newEnd.getTime());
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            const h = hours.toString().padStart(2, '0');
            const m = minutes.toString().padStart(2, '0');
            const s = seconds.toString().padStart(2, '0');
            
            const display = document.getElementById(displayId);
            if (display) display.textContent = `${h}:${m}:${s}`;
        }
        
        tick();
        setInterval(tick, 1000);
    }

    const heroTimer = document.getElementById('hero-timer-display');
    if (heroTimer) updateTimer('hero-timer-display');
    const pricingTimer = document.getElementById('pricing-timer-display');
    if (pricingTimer) updateTimer('pricing-timer-display');
    const finalTimer = document.getElementById('final-timer-display');
    if (finalTimer) updateTimer('final-timer-display');
    const checkoutTimer = document.getElementById('checkout-timer-display');
    if (checkoutTimer) updateTimer('checkout-timer-display');

    var applyCouponBtn = document.getElementById('apply-coupon');
    var couponInput = document.getElementById('coupon-code');
    var couponMessage = document.getElementById('coupon-message');
    var summaryDiscount = document.getElementById('summary-discount');
    var summaryTotal = document.getElementById('summary-total');
    var urlCoupon = <?php echo json_encode($urlCoupon); ?>;
    var useCouponBtn = document.getElementById('use-coupon-btn');
    var exitPopup = document.getElementById('exit-popup');
    var exitPopupClose = document.getElementById('exit-popup-close');
    var checkoutForm = document.getElementById('checkout-form');
    var payBtn = document.getElementById('pay-btn');

    if (urlCoupon && couponInput) {
        couponInput.value = urlCoupon;
        setTimeout(function() {
            if (applyCouponBtn) applyCouponBtn.click();
        }, 300);
    }

    if (applyCouponBtn && couponInput) {
        applyCouponBtn.addEventListener('click', function() {
            const code = couponInput.value.trim();
            if (!code) {
                couponMessage.innerHTML = '<span style="color: #ef4444;">Please enter a coupon code</span>';
                return;
            }
            couponMessage.innerHTML = '<span style="color: var(--muted);">Validating...</span>';
            fetch('/validate-coupon?code=' + encodeURIComponent(code) + '&amount=' + basePrice)
            .then(r => r.json())
            .then(data => {
                if (data.valid) {
                    couponApplied = true;
                    discountAmount = data.discount || 0;
                    couponMessage.innerHTML = '<span style="color: var(--success);">Coupon applied! You save N' + discountAmount.toLocaleString() + '</span>';
                    summaryDiscount.textContent = '-N' + discountAmount.toLocaleString();
                    const newTotal = basePrice - discountAmount;
                    summaryTotal.textContent = 'N' + newTotal.toLocaleString();
                    if (payBtn) payBtn.innerHTML = '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Pay N' + newTotal.toLocaleString();
                } else {
                    couponMessage.innerHTML = '<span style="color: #ef4444;">' + (data.message || 'Invalid coupon') + '</span>';
                }
            })
            .catch(err => {
                couponMessage.innerHTML = '<span style="color: #ef4444;">Error validating coupon</span>';
            });
        });
    }

    if (useCouponBtn) {
        useCouponBtn.addEventListener('click', function() {
            exitPopup.classList.remove('active');
            window.location.href = '?step=checkout&coupon=LAUNCH15';
        });
    }

    var exitPopupAlreadyShown = <?php echo $canShowPopup ? 'false' : 'true'; ?>;
    console.log('Popup check - canShowPopup:', '<?php echo $canShowPopup ? 'true' : 'false'; ?>', 'var:', exitPopupAlreadyShown);

    function showExitPopup() {
        if (exitPopupAlreadyShown) return;
        exitPopupAlreadyShown = true;
        exitPopup.classList.add('active');
        var exitTimer = document.getElementById('exit-timer-count');
        var countDown = 300;
        function tick() {
            countDown--;
            var m = Math.floor(countDown / 60), s = countDown % 60;
            if (exitTimer) exitTimer.innerHTML = 'Coupon expires in <strong style="color: var(--accent);">' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0') + '</strong>';
            if (countDown <= 0) clearInterval(timerInterval);
        }
        timerInterval = setInterval(tick, 1000);
        tick();
    }

    document.addEventListener('mouseleave', function(e) {
        if (e.clientY < 10) showExitPopup();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'F9') {
            showExitPopup();
        }
    });

    if (exitPopupClose) {
        exitPopupClose.addEventListener('click', function() {
            exitPopup.classList.remove('active');
        });
        
        exitPopup.addEventListener('click', function(e) {
            if (e.target === exitPopup) {
                exitPopup.classList.remove('active');
            }
        });
    }

    if (checkoutForm && payBtn) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const couponCode = couponInput ? couponInput.value.trim() : '';
            
            if (!name || !email || !phone) {
                alert('Please fill in all required fields');
                return;
            }
            
            payBtn.disabled = true;
            payBtn.innerHTML = 'Processing...';
            
            const formData = new FormData();
            formData.append('action', 'init_payment');
            formData.append('name', name);
            formData.append('email', email);
            formData.append('phone', phone);
            formData.append('coupon_code', couponCode);
            formData.append('product_slug', 'ecommerce-starter-kit');
            
            console.log('Init payment:', { name, email, phone, couponCode });
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                console.log('Payment init response:', data);
                
                if (data.error) {
                    alert(data.error);
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Pay N' + (basePrice - discountAmount).toLocaleString();
                    return;
                }
                
                const popup = new PaystackPop();
                popup.newTransaction({
                    key: data.paystack_public_key,
                    email: data.email,
                    amount: data.amount,
                    reference: data.reference,
                    onCancel: function() {
                        console.log('Paystack modal closed');
                        payBtn.disabled = false;
                        payBtn.innerHTML = '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Pay N' + (basePrice - discountAmount).toLocaleString();
                    },
                    onSuccess: function(response) {
                        console.log('Payment successful:', response);
                        window.location.href = '/order/success?reference=' + response.reference + '&trxref=' + response.reference;
                    }
                });
            })
            .catch(err => {
                console.error('Payment init error:', err);
                alert('Error initializing payment');
                payBtn.disabled = false;
                payBtn.innerHTML = '<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Pay N' + (basePrice - discountAmount).toLocaleString();
            });
        });
    }

    console.log('E-commerce Starter Kit page loaded');
})();
</script>
</body>
</html>