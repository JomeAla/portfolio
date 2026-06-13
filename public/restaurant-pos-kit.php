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
        'source' => 'restaurant_pos_sales_page',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(12);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 12)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 12, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'restaurant_pos_sales_page',
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
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'Restaurant POS Kit'], $step->body ?? ''),
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

    $product = Product::where('slug', 'restaurant-pos-kit')->where('is_active', true)->first();
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
        'source' => 'restaurant_pos_sales_page',
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
            DB::table('funnel_leads')->where('funnel_id', 12)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'restaurant-pos-kit')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Restaurant POS Kit';
$productPrice = '35,000';
$productOldPrice = '50,000';
$productPriceRaw = 35000;
$productOldRaw = 50000;
$savings = '15,000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Run Your Restaurant Smarter | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

    :root{
        --bg:#FAFAFA;
        --card:#FFFFFF;
        --text:#18181B;
        --text-light:#52525B;
        --accent:#DC2626;
        --accent-dark:#B91C1C;
        --secondary:#F97316;
        --border:#E5E7EB;
        --green:#16A34A;
    }

    html{scroll-behavior:smooth}
    body{font-family:'Nunito Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

    .container{max-width:1100px;margin:0 auto;padding:0 24px}
    .section{padding:100px 0}
    .section-eyebrow{display:flex;align-items:center;gap:10px;font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--accent);margin-bottom:16px}
    .section-eyebrow .dot{width:8px;height:8px;border-radius:50%;background:var(--accent)}
    h2{font-size:2.5rem;font-weight:800;line-height:1.15;margin-bottom:16px;letter-spacing:-.02em}
    .sub{font-size:1.1rem;color:var(--text-light);max-width:600px;margin:0 auto 48px}

    .nav{position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(12px);border:1px solid var(--border);border-radius:50px;padding:12px 28px;display:flex;align-items:center;gap:12px;box-shadow:0 8px 32px rgba(0,0,0,.06)}
    .nav-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.1rem;text-decoration:none;color:var(--text)}
    .nav-brand svg{width:28px;height:28px}

    .hero{padding:140px 0 100px;position:relative;overflow:hidden}
    .hero-grid{display:grid;grid-template-columns:1.1fr 1fr;gap:60px;align-items:center}
    .eyebrow{font-size:.85rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--secondary);margin-bottom:16px}
    .hero-title{font-size:3rem;font-weight:800;line-height:1.1;margin-bottom:20px;letter-spacing:-.03em}
    .hero-sub{font-size:1.15rem;color:var(--text-light);margin-bottom:32px;line-height:1.7}
    .price-block{display:flex;align-items:center;gap:16px;margin-bottom:24px}
    .price-current{font-size:2.5rem;font-weight:800;color:var(--accent)}
    .price-orig{font-size:1.3rem;text-decoration:line-through;color:var(--text-light)}
    .price-badge{background:var(--green);color:#fff;font-size:.8rem;font-weight:700;padding:6px 14px;border-radius:20px}
    .cta-group{display:flex;flex-direction:column;gap:16px;margin-bottom:32px}
    .cta-btn{display:inline-flex;align-items:center;gap:10px;background:var(--accent);color:#fff;font-size:1.1rem;font-weight:700;padding:18px 32px;border-radius:14px;text-decoration:none;transition:all .3s;box-shadow:0 8px 24px rgba(220,38,38,.25)}
    .cta-btn:hover{background:var(--accent-dark);transform:translateY(-2px)}
    .cta-arrow{font-size:1.3rem}
    .countdown-bar{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid var(--border);padding:14px 20px;border-radius:12px;width:fit-content}
    .countdown-timer{display:flex;gap:8px}
    .c-t{text-align:center;min-width:44px}
    .c-t .num{font-size:1.2rem;font-weight:800;display:block;line-height:1}
    .c-t .lbl{font-size:.65rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.05em}
    .countdown-sub-label{font-size:.8rem;color:var(--text-light)}
    .stats-row{display:flex;gap:32px}
    .stats-item{text-align:center}
    .stats-item .val{font-size:1.4rem;font-weight:800;color:var(--text)}
    .stats-item .lbl{font-size:.8rem;color:var(--text-light)}

    .hero-right{position:relative}
    .hero-img-wrap{background:#fff;border-radius:24px;padding:32px;box-shadow:0 20px 60px rgba(0,0,0,.1);border:1px solid var(--border)}
    .hero-img-placeholder{aspect-ratio:4/3;background:linear-gradient(135deg,#FEF3C7 0%,#FDE68A 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:5rem}

    .features-section{background:#fff;padding:100px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
    .features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:48px}
    .feature-card{background:var(--bg);border:1px solid var(--border);border-radius:20px;padding:28px;transition:all .3s}
    .feature-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.08)}
    .feature-icon{width:56px;height:56px;background:var(--accent);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;font-size:1.6rem}
    .feature-card h3{font-size:1.15rem;font-weight:700;margin-bottom:10px}
    .feature-card p{font-size:.9rem;color:var(--text-light);line-height:1.6}

    .pricing-section{background:var(--bg);padding:100px 0;position:relative}
    .pricing-inner{text-align:center;max-width:600px;margin:0 auto}
    .pricing-card{background:#fff;border-radius:28px;padding:48px;margin-top:40px;box-shadow:0 20px 60px rgba(0,0,0,.08);border:1px solid var(--border)}
    .pricing-price{margin:24px 0 32px}
    .pricing-price .strike{font-size:1.4rem;text-decoration:line-through;color:var(--text-light);margin-right:12px}
    .pricing-price .amount{font-size:3rem;font-weight:800;color:var(--accent)}
    .pricing-features{text-align:left;margin-bottom:32px}
    .pricing-features ul{list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .pricing-features li{font-size:.95rem;display:flex;align-items:center;gap:10px}
    .pricing-features .fi{color:var(--green);font-weight:700}
    .btn-large{width:100%;background:var(--accent);color:#fff;font-size:1.1rem;font-weight:700;padding:20px;border-radius:14px;border:none;cursor:pointer;transition:all .3s;box-shadow:0 8px 24px rgba(220,38,38,.25)}
    .btn-large:hover{background:var(--accent-dark);transform:translateY(-2px)}
    .pricing-countdown{margin-top:24px;padding-top:24px;border-top:1px solid var(--border)}
    .timer-row{display:flex;justify-content:center;gap:12px;margin-bottom:12px}
    .urgency{font-size:.85rem;color:var(--secondary);font-weight:600;margin-bottom:8px}
    .guarantee{font-size:.8rem;color:var(--text-light)}

    .cta-section{background:#fff;padding:100px 0;border-top:1px solid var(--border)}
    .cta-inner{text-align:center}
    .cta-btn-center{display:inline-flex;align-items:center;gap:10px;background:var(--accent);color:#fff;font-size:1.1rem;font-weight:700;padding:18px 36px;border-radius:14px;text-decoration:none;transition:all .3s;box-shadow:0 8px 24px rgba(220,38,38,.25)}
    .cta-btn-center:hover{background:var(--accent-dark);transform:translateY(-2px)}
    .cta-countdown{margin-top:24px}
    .cta-countdown .countdown-timer{display:flex;justify-content:center;gap:12px}

    .footer{padding:40px 24px;border-top:1px solid var(--border)}
    .footer-inner{display:flex;justify-content:space-between;align-items:center}
    .footer-brand{font-size:.9rem;color:var(--text-light)}

    .checkout-wrap{background:#fff;min-height:100vh;padding:120px 0 80px}
    .checkout-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:60px;max-width:1000px;margin:0 auto;padding:0 24px}
    .checkout-left h1{font-size:2rem;font-weight:800;margin-bottom:8px}
    .checkout-left .sub{color:var(--text-light);margin-bottom:32px}
    .form-card{background:var(--bg);border:1px solid var(--border);border-radius:20px;padding:32px}
    .error-msg{background:#FEF2F2;border:1px solid #FECACA;color:#DC2626;padding:14px 18px;border-radius:10px;font-size:.9rem;margin-bottom:20px;display:none}
    .error-msg.show{display:block}
    .field-group{margin-bottom:20px}
    .field-group label{display:block;font-weight:600;font-size:.9rem;margin-bottom:8px}
    .field-group input{width:100%;padding:14px 16px;border:1px solid var(--border);border-radius:10px;font-size:1rem;font-family:inherit;transition:all .2s}
    .field-group input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(220,38,38,.1)}
    .coupon-row{display:flex;gap:12px;margin-bottom:16px}
    .coupon-row input{flex:1}
    .coupon-row button{background:var(--text);color:#fff;border:none;padding:14px 24px;border-radius:10px;font-weight:600;cursor:pointer;transition:all .2s}
    .coupon-row button:hover{background:var(--text-light)}
    .coupon-msg{font-size:.85rem;margin-bottom:20px;display:none}
    .coupon-msg.success{color:var(--green)}
    .coupon-msg.error{color:var(--accent)}
    .pay-btn{width:100%;background:var(--accent);color:#fff;font-size:1.1rem;font-weight:700;padding:18px;border-radius:12px;border:none;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:10px;position:relative;overflow:hidden}
    .pay-btn:hover{background:var(--accent-dark)}
    .pay-btn .spinner{display:none}
    .pay-btn.loading{pointer-events:none}
    .pay-btn.loading .btn-text{display:none}
    .pay-btn.loading .spinner{display:inline-flex;align-items:center;gap:10px}
    .spin{width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .8s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .security-note{display:flex;align-items:center;justify-content:center;gap:8px;font-size:.8rem;color:var(--text-light);margin-top:16px}
    .trust-row{display:flex;justify-content:center;gap:32px;margin-top:32px}
    .trust-item{display:flex;align-items:center;gap:8px;font-size:.85rem;color:var(--text-light)}
    .trust-item svg{color:var(--green)}

    .checkout-right{position:sticky;top:120px;height:fit-content}
    .order-summary{background:var(--bg);border:1px solid var(--border);border-radius:20px;overflow:hidden}
    .summary-header{background:var(--text);color:#fff;padding:24px;border-bottom:1px solid var(--border)}
    .summary-header h3{font-size:1.1rem;font-weight:700;margin-bottom:4px}
    .summary-header p{font-size:.9rem;opacity:.9}
    .summary-body{padding:24px}
    .product-row{display:flex;gap:16px;padding-bottom:20px;border-bottom:1px solid var(--border);margin-bottom:20px}
    .product-img{width:80px;height:60px;background:linear-gradient(135deg,#FEF3C7,#FDE68A);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0}
    .product-info h4{font-size:1rem;font-weight:700;margin-bottom:4px}
    .product-info .pdesc{font-size:.8rem;color:var(--text-light);margin-bottom:8px}
    .product-tag{font-size:.7rem;background:var(--text);color:#fff;padding:4px 10px;border-radius:20px}
    .price-breakdown{margin-bottom:20px}
    .price-line{display:flex;justify-content:space-between;margin-bottom:12px;font-size:.95rem}
    .price-line .lbl{color:var(--text-light)}
    .price-line.discount{color:var(--green)}
    .price-line.total{font-weight:800;font-size:1.2rem;padding-top:12px;border-top:1px solid var(--border)}
    .summary-countdown{background:#FFF7ED;border:1px solid #FED7AA;border-radius:12px;padding:16px;text-align:center}
    .sc-label{font-size:.85rem;color:var(--secondary);font-weight:600;margin-bottom:12px}
    .summary-countdown .countdown-timer{justify-content:center;gap:12px;margin-bottom:8px}
    .summary-countdown .c-t{min-width:40px}
    .summary-countdown .c-t .num{font-size:1.1rem}
    .sc-note{font-size:.8rem;color:var(--accent);font-weight:600}
    .guarantee-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:20px;padding:14px;background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;font-size:.82rem;color:var(--text-light)}
    .guarantee-row svg{width:16px;height:16px;color:var(--green);flex-shrink:0}

    .popup-overlay{position:fixed;inset:0;z-index:9990;background:rgba(17,16,16,.55);backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;padding:24px}
    .popup-overlay.show{display:flex}
    .popup-box{background:#fff;border-radius:28px;max-width:480px;width:100%;padding:48px;position:relative;text-align:center;box-shadow:0 40px 80px rgba(17,16,16,.25);transform:scale(.9);opacity:0;transition:all .5s cubic-bezier(.32,.72,0,1)}
    .popup-overlay.show .popup-box{transform:scale(1);opacity:1}
    .popup-close{position:absolute;top:16px;right:16px;width:36px;height:36px;border-radius:50%;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;color:var(--text-light);transition:all .3s}
    .popup-close:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
    .popup-icon{font-size:3.5rem;margin-bottom:20px}
    .popup-title{font-size:1.8rem;font-weight:800;margin-bottom:12px;letter-spacing:-.02em;line-height:1.2}
    .popup-desc{font-size:.95rem;color:var(--text-light);margin-bottom:20px;line-height:1.7}
    .popup-code{display:flex;align-items:center;justify-content:center;gap:12px;background:var(--bg);border:2px dashed var(--accent);border-radius:12px;padding:16px 24px;margin-bottom:8px}
    .popup-code span{font-size:1.4rem;font-weight:800;color:var(--accent);letter-spacing:.1em}
    .popup-code button{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:.78rem;font-weight:700;cursor:pointer;transition:all .3s}
    .popup-code button:hover{background:var(--accent-dark)}
    .popup-note{font-size:.78rem;color:var(--text-light);margin-bottom:12px}
    .popup-savings{font-size:.82rem;font-weight:700;color:var(--green);margin-bottom:24px}
    .popup-cta{display:block;width:100%;background:var(--accent);color:#fff;font-size:1rem;font-weight:700;padding:18px;border-radius:14px;border:none;cursor:pointer;font-family:inherit;transition:all .5s cubic-bezier(.32,.72,0,1);text-decoration:none}
    .popup-cta:hover{background:var(--accent-dark);transform:translateY(-2px);box-shadow:0 12px 32px rgba(220,38,38,.3)}
    .popup-timer{margin-top:20px;font-size:.78rem;color:var(--text-light)}
    .popup-timer span{font-weight:700;color:var(--accent)}

    .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
    .hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--text-light);font-size:14px;font-weight:500}
    .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
    .summary-image{width:80px;height:60px;border-radius:10px;background:linear-gradient(135deg,#FEF3C7,#FDE68A);display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
    .summary-image img{width:100%;height:100%;object-fit:cover}
    .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--text-light);font-weight:500}
    .summary-timer strong{color:var(--accent);font-weight:700}

    @media(max-width:1024px){.hero-grid,.checkout-grid{grid-template-columns:1fr;gap:40px}.hero-right{order:-1}.hero-sub{margin:0 auto 32px}.price-block{margin:0 auto 28px}.stats-row{justify-content:center;gap:24px}.features-grid{grid-template-columns:1fr}.footer-inner{flex-direction:column;gap:16px;text-align:center}.checkout-grid{padding:0 16px}.order-summary{position:static;margin-top:32px}.checkout-left,.checkout-right{width:100%;padding:0}}
    @media(max-width:768px){.hero-grid{grid-template-columns:1fr;gap:24px}.hero{padding:100px 16px 40px}.hero-title{font-size:clamp(1.8rem,6vw,2.5rem)}.hero-sub{font-size:.9rem}.nav{padding:8px 16px;top:8px}.nav-brand span{display:none}.price-block{flex-wrap:wrap;padding:12px 16px;justify-content:center}.price-current{font-size:1.6rem}.cta-btn{padding:14px 24px;font-size:.9rem;width:100%;justify-content:center}.countdown-bar{max-width:100%}.timer-value{font-size:1.5rem}.stats-row{grid-template-columns:repeat(2,1fr);gap:12px}.stat-item{padding:16px 8px}.features-grid{grid-template-columns:1fr;gap:16px}.section{padding:60px 16px}.section-title{font-size:clamp(1.5rem,5vw,2rem)}.footer{padding:40px 16px 30px}.pricing-card{padding:32px 20px}.pricing-price .amount{font-size:2.2rem}.pricing-features li{font-size:.85rem}.cta-section{padding:60px 16px}.checkout-page{padding:20px 16px}.checkout-form{padding:24px 16px}.field-group input{padding:12px 14px;font-size:.9rem}.pay-btn{padding:16px;font-size:.95rem;width:100%}.order-summary-box{padding:20px 16px}.timer-sticky{padding:10px 16px;font-size:.8rem;width:calc(100% - 32px);left:16px;transform:none;bottom:12px}.exit-popup-box{padding:32px 20px;margin:16px}.exit-popup-box h2{font-size:1.4rem}.exit-code-wrap input{font-size:1rem;padding:12px}.exit-link{padding:14px 24px;font-size:.9rem}}
    @media(max-width:480px){.hero{padding:90px 12px 32px}.hero-title{font-size:1.6rem;letter-spacing:-.02em}.eyebrow{padding:4px 12px;font-size:.65rem}.price-new{font-size:2rem}.timer-box{padding:14px}.timer-value{font-size:1.3rem}.feature-card,.pricing-card{padding:20px}.feature-card h3{font-size:.95rem}.btn-primary,.cta-btn{font-size:.9rem;padding:12px 20px}.strip-bar{padding:8px 12px;font-size:.7rem}.nav{top:0;border-radius:0}.section{padding:48px 12px}.section-title{font-size:1.4rem}.cta-section{padding:40px 12px;border-radius:16px}.cta-section h2{font-size:1.5rem}.pricing-card{border-radius:16px}.stats-row{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>

<nav class="nav">
    <a href="/" class="nav-brand">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><rect width="28" height="28" rx="8" fill="#DC2626"/><path d="M8 19l6-11 6 11" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
                        <div class="product-img">&#128203;</div>
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($productTitle); ?></h4>
                            <p class="pdesc">Complete POS system for restaurants</p>
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
    <div class="container">
        <div class="hero-grid">
            <div class="hero-text">
                <div class="eyebrow">For Restaurants & Food Businesses</div>
                <h1 class="hero-title">Run Your Restaurant Smarter &mdash; From Orders to Kitchen to Customers</h1>
                <p class="hero-sub">Complete POS system with order management, kitchen display, customer loyalty &amp; reporting dashboard</p>
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
                    <div class="stats-item"><div class="val">&#128203;</div><div class="lbl">Order Management</div></div>
                    <div class="stats-item"><div class="val">&#127869;</div><div class="lbl">Kitchen Display</div></div>
                    <div class="stats-item"><div class="val">&#128100;</div><div class="lbl">Customer Loyalty</div></div>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-img-wrap">
                    <div class="hero-img-placeholder">&#127869;&#8205;&#128203;</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="features-section">
    <div class="container">
        <div class="section-eyebrow"><span class="dot"></span>What's Included</div>
        <h2>Everything You Need to Run<br>Your Restaurant Efficiently</h2>
        <p class="sub">Powerful features designed specifically for Nigerian restaurants and food businesses.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">&#128203;</div>
                <h3>Digital Menu &amp; Ordering</h3>
                <p>Create beautiful digital menus, manage items, and process orders from multiple channels in one place.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#127869;</div>
                <h3>Kitchen Display System</h3>
                <p>Real-time kitchen tickets, order prioritization, and multi-station support for smooth kitchen operations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#128202;</div>
                <h3>Order Tracking</h3>
                <p>Track orders from placement to delivery. Keep customers informed with real-time status updates.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#10004;</div>
                <h3>Customer Loyalty Program</h3>
                <p>Build repeat customers with points, rewards, and personalized offers based on purchase history.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#128200;</div>
                <h3>Sales Reporting Dashboard</h3>
                <p>Powerful analytics: daily sales, popular items, peak hours, staff performance, and profit margins.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">&#127984;</div>
                <h3>Multi-Branch Support</h3>
                <p>Manage multiple locations from one dashboard. Centralized reporting and inventory across all branches.</p>
            </div>
        </div>
    </div>
</section>

<section class="pricing-section">
    <div class="container">
        <div class="pricing-inner">
            <div class="section-eyebrow"><span class="dot"></span>One-Time Price</div>
            <h2>Get Started for Less</h2>
            <p>One-time payment. Lifetime access. No monthly fees. No hidden costs.</p>
            <div class="pricing-card">
                <h3 class="pricing-title"><?php echo htmlspecialchars($productTitle); ?></h3>
                <div class="pricing-price">
                    <span class="strike">&#8358;<?php echo $productOldPrice; ?></span>
                    <span class="amount">&#8358;<?php echo $productPrice; ?></span>
                </div>
                <div class="pricing-features">
                    <ul>
                        <li><span class="fi">&#10004;</span> Digital Menu &amp; Ordering</li>
                        <li><span class="fi">&#10004;</span> Kitchen Display System</li>
                        <li><span class="fi">&#10004;</span> Order Tracking</li>
                        <li><span class="fi">&#10004;</span> Customer Loyalty Program</li>
                        <li><span class="fi">&#10004;</span> Sales Reporting Dashboard</li>
                        <li><span class="fi">&#10004;</span> Multi-Branch Support</li>
                        <li><span class="fi">&#10004;</span> Free Updates</li>
                        <li><span class="fi">&#10004;</span> Instant Digital Delivery</li>
                    </ul>
                </div>
                <form method="POST" action="?step=checkout" style="display:contents">
                    <input type="hidden" name="action" value="capture_lead">
                    <button type="submit" class="btn-large">Get Started Now <span style="font-size:1.2rem">&#8594;</span></button>
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
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-inner">
            <h2>Ready to Transform<br>Your Restaurant?</h2>
            <p class="sub">Join thousands of restaurants already running smarter with our POS system.</p>
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
            <p style="margin-top:16px;font-size:.85rem;color:var(--text-light)">&#9201; Offer expires in</p>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">&copy; <?php echo date('Y'); ?> Joala Digital. All rights reserved.</div>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Exit Intent Popup -->
<div class="popup-overlay" id="exitPopup">
    <div class="popup-box">
        <button class="popup-close" id="popupClose">&times;</button>
        <div class="popup-icon">&#127973;</div>
        <h2 class="popup-title">Wait &mdash; Get 15% Off!</h2>
        <p class="popup-desc">Don't leave empty-handed! Use this exclusive discount code to get the Restaurant POS Kit at a lower price.</p>
        <div class="popup-code">
            <span id="popupCodeText">LAUNCH15</span>
            <button onclick="copyPopupCode()">Copy</button>
        </div>
        <p class="popup-note">Copy the code and apply it at checkout to save &#8358;5,250</p>
        <p class="popup-savings">&#10004; You save &#8358;5,250 on your order</p>
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
        if (popupShown || sessionStorage.getItem('rppPopupSeen')) return;
        if (e.clientY < 5) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
        }
    });
    setTimeout(function() {
        if (!popupShown && !sessionStorage.getItem('rppPopupSeen')) {
            document.getElementById('exitPopup').classList.add('show');
            popupShown = true;
            sessionStorage.setItem('rppPopupSeen', '1');
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
        fetch('/validate-coupon?code=' + encodeURIComponent(code) + '&amount=35000')
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

        var pathParts = window.location.pathname.split('/');
        var basePath = '/' + pathParts[1] + '/' + pathParts[2] + '.php';
        var fetchUrl = basePath + '?step=checkout';

        fetch(fetchUrl, { method: 'POST', body: fd })
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
                    onClose: function() {
                        btn.classList.remove('loading');
                    }
                });
                handler.openIframe();
            })
            .catch(function(err) {
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