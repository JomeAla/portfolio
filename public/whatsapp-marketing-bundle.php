<?php
error_reporting(0);
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

session_start();

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;

$baseUrl = 'https://joala.com.ng';
$productSlug = 'whatsapp-marketing-bundle';
$productId = 10;
$funnelId = 15;

$product = Product::where('slug', $productSlug)->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'WhatsApp Marketing Bundle';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 8000;
$productOldRaw = $product ? (float)$product->price : 15000;

$pageKey = "whatsapp_bundle_page_viewed";
$viewed = isset($_SESSION[$pageKey]);
if (!$viewed) { $_SESSION[$pageKey] = true; }

$timerOffset = isset($_SESSION['whatsapp_bundle_timer']) ? (int)$_SESSION['whatsapp_bundle_timer'] : null;
if (!$timerOffset) {
    $_SESSION['whatsapp_bundle_timer'] = rand(20000, 40000);
    $timerOffset = $_SESSION['whatsapp_bundle_timer'];
}

$email = $_SESSION['checkout_email'] ?? '';
$step = $_GET['step'] ?? 'landing';
$showPopup = isset($_SESSION['whatsapp_exit_shown']) ? false : true;

$price = $step === 'checkout' ? $productPriceRaw : $productOldRaw;
$originalPrice = $productOldRaw;
$discount = 0;
$couponMsg = '';
$couponSuccess = false;
$couponCode = '';
$finalPrice = $price;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] ?? '' === 'init_payment') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0) * 100;
    $coupon = trim($_POST['coupon'] ?? '');

    if ($coupon) {
        $c = Coupon::where('code', $coupon)->first();
        if ($c && $c->isValid()) {
            $couponSuccess = true;
            $p = $amount / 100;
            if ($c->discount_type === 'percentage') {
                $disc = $p * ($c->discount_value / 100);
                $maxD = $c->max_discount ?? PHP_INT_MAX;
                $disc = min($disc, $maxD);
            } else {
                $disc = min((float)$c->discount_value, $p);
            }
            $amount = max(100, $amount - ($disc * 100));
            $_SESSION['whatsapp_bundle_coupon'] = $coupon;
        }
    }

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $finalAmount = $amount / 100;
    $order = Order::create([
        'order_number' => 'WA-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'product_id' => $product ? $product->id : $productId,
        'customer_name' => explode('@', $email)[0],
        'customer_email' => $email,
        'amount' => $finalAmount,
        'discount' => ($price - $finalAmount),
        'final_amount' => $finalAmount,
        'coupon_code' => $coupon ?: null,
        'payment_status' => 'pending',
        'checkout_started_at' => now(),
    ]);

    $ref = 'WA_' . uniqid() . '_' . time();

    echo json_encode([
        'order_id' => $order->id,
        'paystack_key' => Setting::get('paystack_public_key'),
        'amount' => $amount,
        'email' => $email,
        'reference' => $ref,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = trim($_POST['coupon_code'] ?? '');
    if ($couponCode) {
        $c = Coupon::where('code', $couponCode)->first();
        if ($c && $c->isValid()) {
            $couponSuccess = true;
            if ($c->discount_type === 'percentage') {
                $disc = $price * ($c->discount_value / 100);
                $maxD = $c->max_discount ?? PHP_INT_MAX;
                $disc = min($disc, $maxD);
            } else {
                $disc = min((float)$c->discount_value, $price);
            }
            $finalPrice = $price - $disc;
            $couponMsg = "<span style='color:#16a34a'>✓ Coupon applied! You save &#x20A6;".number_format($disc)."</span>";
            $_SESSION['whatsapp_bundle_coupon'] = $couponCode;
        } else {
            $couponMsg = "<span style='color:#dc2626'>✗ Invalid or expired coupon</span>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WhatsApp Marketing Bundle — Joala Store</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--bg:#f0fdf4;
--surface:#ffffff;
--primary:#16a34a;
--primary-dark:#15803d;
--accent:#dc2626;
--text:#1a2332;
--sub:#64748b;
--border:#d1fae5;
--card:#f0fdf4;
--muted:#64748b;
--accent:#16a34a;
}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}
.container{max-width:1200px;margin:0 auto;padding:0 24px}

.hero{padding:80px 0 60px;position:relative}
.hero::before{content:'';position:absolute;top:-60px;right:-60px;width:300px;height:300px;background:radial-gradient(circle,#16a34a33,transparent 70%);border-radius:50%}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:#dcfce7;color:var(--primary-dark);padding:8px 18px;border-radius:50px;font-size:13px;font-weight:600;margin-bottom:24px}
.hero h1{font-family:'Space Grotesk',sans-serif;font-size:clamp(36px,5vw,64px);font-weight:800;line-height:1.1;margin-bottom:20px;letter-spacing:-2px}
.hero h1 span{color:var(--primary)}
.hero-sub{font-size:18px;color:var(--sub);max-width:580px;margin-bottom:32px}
.hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center}
.hero-product{background:var(--surface);border-radius:24px;padding:40px;box-shadow:0 20px 60px rgba(22,163,74,0.1);border:1px solid var(--border)}
.hero-product-label{font-size:12px;text-transform:uppercase;letter-spacing:2px;color:var(--sub);margin-bottom:12px}
.hero-product h3{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;margin-bottom:8px}
.hero-product p{color:var(--sub);font-size:14px;margin-bottom:20px}
.hero-price-row{display:flex;align-items:baseline;gap:12px;margin-bottom:24px}
.price-old{font-size:20px;color:var(--sub);text-decoration:line-through}
.price-new{font-family:'Space Grotesk',sans-serif;font-size:40px;font-weight:800;color:var(--primary)}
.price-badge{background:var(--primary);color:#fff;padding:4px 12px;border-radius:8px;font-size:12px;font-weight:700}
.timer-box{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:16px 20px;text-align:center;margin-bottom:24px}
.timer-label{font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:var(--sub);margin-bottom:6px}
.timer-value{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;color:var(--primary)}
.timer-value span{font-size:14px;font-weight:400;color:var(--sub)}
.hero-features{list-style:none;display:flex;flex-direction:column;gap:12px}
.hero-features li{display:flex;align-items:center;gap:12px;font-size:15px}
.hero-features li::before{content:'✓';display:flex;align-items:center;justify-content:center;width:28px;height:28px;background:#dcfce7;color:var(--primary);border-radius:50%;font-size:14px;font-weight:700;flex-shrink:0}
.hero-image{background:linear-gradient(135deg,#16a34a 0%,#15803d 100%);border-radius:24px;padding:40px;text-align:center;color:#fff;aspect-ratio:4/3;display:flex;flex-direction:column;align-items:center;justify-content:center}
.hero-image-icon{font-size:64px;margin-bottom:16px}
.hero-image h3{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;margin-bottom:8px}
.hero-image p{opacity:0.8;font-size:15px}
.whatsapp-icon{font-size:48px;color:#25D366}

.section{padding:60px 0}
.section-title{font-family:'Space Grotesk',sans-serif;font-size:36px;font-weight:800;text-align:center;margin-bottom:12px}
.section-sub{text-align:center;color:var(--sub);font-size:16px;margin-bottom:48px}

.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.feature-card{background:var(--surface);border-radius:20px;padding:32px;border:1px solid var(--border);transition:transform 0.3s,box-shadow 0.3s}
.feature-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(22,163,74,0.08)}
.feature-icon{width:56px;height:56px;background:var(--card);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:20px}
.feature-card h3{font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:700;margin-bottom:10px}
.feature-card p{color:var(--sub);font-size:14px;line-height:1.7}
.feature-card.highlight{border-color:var(--primary);background:#f0fdf4}

.cta-section{background:var(--surface);border-radius:32px;padding:64px;text-align:center;margin:60px 0;border:1px solid var(--border)}
.cta-section h2{font-family:'Space Grotesk',sans-serif;font-size:42px;font-weight:800;margin-bottom:16px}
.cta-section p{font-size:18px;color:var(--sub);margin-bottom:32px}

.btn-primary{display:inline-block;background:var(--primary);color:#fff;padding:18px 48px;border-radius:16px;font-size:18px;font-weight:700;text-decoration:none;transition:all 0.3s;border:none;cursor:pointer;font-family:inherit}
.btn-primary:hover{background:var(--primary-dark);transform:translateY(-2px);box-shadow:0 12px 30px rgba(22,163,74,0.3)}

.pricing-card{background:var(--surface);border-radius:24px;padding:48px;max-width:500px;margin:0 auto;border:2px solid var(--primary);position:relative}
.pricing-popular{position:absolute;top:-14px;left:50%;transform:translateX(-50%);background:var(--primary);color:#fff;padding:6px 24px;border-radius:50px;font-size:13px;font-weight:700}
.pricing-header{text-align:center;margin-bottom:32px}
.pricing-header h3{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;margin-bottom:8px}
.pricing-header p{color:var(--sub);font-size:14px}
.pricing-price{text-align:center;margin-bottom:32px}
.pricing-price .old{font-size:20px;color:var(--sub);text-decoration:line-through;display:block;margin-bottom:4px}
.pricing-price .current{font-family:'Space Grotesk',sans-serif;font-size:56px;font-weight:800;color:var(--primary)}
.pricing-price .current span{font-size:20px;font-weight:400}
.pricing-features{list-style:none;display:flex;flex-direction:column;gap:14px;margin-bottom:32px}
.pricing-features li{display:flex;align-items:center;gap:12px;font-size:15px}
.pricing-features li::before{content:'✓';color:var(--primary);font-weight:700}

.timer-sticky{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--text);color:#fff;padding:12px 28px;border-radius:100px;display:flex;align-items:center;gap:12px;font-size:14px;font-weight:600;z-index:100;box-shadow:0 8px 30px rgba(0,0,0,0.3);opacity:0;transition:opacity 0.5s}
.timer-sticky.show{opacity:1}
.timer-sticky-dot{width:8px;height:8px;background:#dc2626;border-radius:50%;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.3}}

.exit-popup{position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity 0.4s}
.exit-popup.active{opacity:1;pointer-events:all}
.exit-popup-box{background:#fff;border-radius:24px;padding:48px;max-width:480px;width:90%;text-align:center;position:relative}
.exit-popup-close{position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:var(--sub)}
.exit-popup-icon{font-size:48px;margin-bottom:16px}
.exit-popup-box h2{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:800;margin-bottom:8px}
.exit-popup-box p{color:var(--sub);margin-bottom:20px}
.exit-popup-code{display:flex;align-items:center;gap:12px;margin-bottom:20px}
.exit-popup-code input{border:2px dashed var(--primary);background:#f0fdf4;padding:14px 20px;border-radius:12px;font-size:18px;font-weight:700;color:var(--primary);font-family:'Space Grotesk',sans-serif;flex:1;text-align:center;cursor:pointer;width:100%}
.exit-popup-timer{font-size:14px;color:var(--sub);margin-bottom:20px}
.exit-popup-link{display:inline-block;background:var(--primary);color:#fff;padding:14px 36px;border-radius:12px;font-weight:700;text-decoration:none;font-size:16px}

@media(max-width:768px){
        .hero-grid{grid-template-columns:1fr;gap:24px}
        .hero{padding:80px 16px 40px}
        .hero h1{font-size:clamp(1.8rem,6vw,2.5rem)}
        .hero-sub{font-size:.9rem}
        .hero-price-row{flex-wrap:wrap}
        .price-new{font-size:2rem}
        .timer-box{padding:12px}
        .timer-value{font-size:1.5rem}
        .features-grid{grid-template-columns:1fr;gap:16px}
        .feature-card{padding:20px}
        .feature-card h3{font-size:.95rem}
        .cta-section{padding:40px 20px}
        .cta-section h2{font-size:1.5rem}
        .section{padding:48px 16px}
        .section-title{font-size:clamp(1.5rem,5vw,2rem)}
        .pricing-card{padding:32px 20px}
        .btn-primary{padding:14px 28px;font-size:1rem;width:100%;text-align:center;display:block}
        .nav-brand span{display:none}
        .exit-popup-box{padding:28px 16px;margin:12px}
        .exit-popup-box h2{font-size:1.3rem}
        .exit-popup-code input{font-size:1rem;padding:10px}
        .exit-popup-link{padding:12px 20px;font-size:.9rem;width:100%;display:block;text-align:center}
        .timer-sticky{width:calc(100% - 32px);left:16px;transform:none;padding:10px 16px;font-size:.8rem}
        #checkoutPage .container{max-width:100%;padding:0 16px}
        #checkoutPage .pricing-card{padding:24px 16px}
        #checkoutPage input{width:100%}
        #checkoutPage button{width:100%}
    }
    @media(max-width:480px){
        .hero{padding:70px 12px 32px}
        .hero h1{font-size:1.6rem}
        .hero-badge{padding:6px 12px;font-size:.7rem}
        .price-old{font-size:1rem}
        .timer-box{max-width:100%}
        .timer-value{font-size:1.3rem}
        .section{padding:40px 12px}
        .section-title{font-size:1.4rem}
        .cta-section{padding:32px 12px;border-radius:16px}
        .cta-section h2{font-size:1.3rem}
        .cta-section p{font-size:.9rem}
        .feature-card{padding:16px}
        .pricing-card{border-radius:16px}
        nav{flex-direction:column;gap:8px;text-align:center}
        nav a{display:none}
        .timer-sticky{font-size:.75rem;padding:8px 12px}
    }
.hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
.hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--muted);font-size:14px;font-weight:500}
.hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
.summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--muted);font-weight:500}
.summary-timer strong{color:var(--accent);font-weight:700}
.summary-image{width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,rgba(22,163,74,.15),rgba(22,163,74,.1));border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;overflow:hidden}
.summary-image img{width:100%;height:100%;object-fit:cover}
</style>
</head>
<body>

<div class="container">
<nav style="padding:20px 0;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)">
<div style="font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:800">Joala<span style="color:var(--primary)">.</span></div>
<a href="<?= $baseUrl ?>" style="color:var(--sub);text-decoration:none;font-size:14px;font-weight:500">← Back to store</a>
</nav>

<section class="hero">
<div class="hero-grid">
<div>
<div class="hero-badge">
<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.06 2.272 6.965L.818 23.182l4.11-1.168A11.944 11.944 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.137 0-4.146-.669-5.802-1.806l-.416-.272 1.456-1.456.257.168C8.76 19.8 10.315 20 12 20c5.523 0 10-4.477 10-10S17.523 0 12 0z"/></svg>
Bundle Deal — 46% OFF
</div>
<h1>WhatsApp Marketing<br><span>Bundle</span></h1>
<p class="hero-sub">Everything you need to generate leads, close sales, and automate your WhatsApp messages — all in one powerful toolkit.</p>
<div class="hero-product">
<div class="hero-product-label">Complete Package</div>
<h3>WhatsApp Marketing Bundle</h3>
<p>Everything you need to launch, automate, and scale your WhatsApp marketing</p>
<div class="hero-price-row">
<span class="price-old">&#x20A6;<?= number_format($productOldRaw) ?></span>
<span class="price-new">&#x20A6;<?= number_format($productPriceRaw) ?></span>
<span class="price-badge"><?= round((1 - $productPriceRaw / $productOldRaw) * 100) ?>% OFF</span>
</div>
<div class="timer-box">
<div class="timer-label">⏰ Offer expires in</div>
<div class="timer-value" id="timer">--:--:--</div>
</div>
<ul class="hero-features">
<li>Automated message templates</li>
<li>Lead capture forms setup guide</li>
<li>Sales funnel templates</li>
<li>Broadcast sequence guides</li>
</ul>
</div>
</div>
<div>
<div class="hero-image" style="background:linear-gradient(135deg,var(--primary) 0%,var(--primary-dark) 100%)">
<?php if ($productImage): ?>
<img src="/<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($productTitle) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:24px">
<?php else: ?>
<div class="whatsapp-icon">💬</div>
<?php endif; ?>
</div>
</div>
</div>
</section>
</div>

<section class="section" style="background:#fff;margin-top:40px">
<div class="container">
<h2 class="section-title">What You Get</h2>
<p class="section-sub">Everything for your WhatsApp marketing needs</p>
<div class="features-grid">
<div class="feature-card">
<div class="feature-icon">📱</div>
<h3>Message Templates</h3>
<p>50+ proven WhatsApp message templates for sales, follow-ups, and customer support.</p>
</div>
<div class="feature-card">
<div class="feature-icon">🤖</div>
<h3>Automation Guide</h3>
<p>Step-by-step guide to automating responses and broadcast messages without code.</p>
</div>
<div class="feature-card">
<div class="feature-icon">🎯</div>
<h3>Lead Funnel</h3>
<p>Pre-built landing page templates designed to capture leads via WhatsApp.</p>
</div>
<div class="feature-card">
<div class="feature-icon">📊</div>
<h3>Analytics</h3>
<p>Track message open rates, response rates, and conversion metrics.</p>
</div>
<div class="feature-card">
<div class="feature-icon">🔗</div>
<h3>Integration Setup</h3>
<p>Connect WhatsApp Business API to your existing CRM and tools.</p>
</div>
<div class="feature-card highlight">
<div class="feature-icon">🚀</div>
<h3>Growth Hacks</h3>
<p>Exclusive strategies to grow your WhatsApp contact list and increase engagement.</p>
</div>
</div>
</div>
</section>

<div class="container">
<div class="cta-section">
<h2>Ready to WhatsApp your way to more sales?</h2>
<p>Get the complete WhatsApp Marketing Bundle today and start converting conversations into customers.</p>
<div class="timer-box" style="max-width:300px;margin:0 auto 32px">
<div class="timer-label">⏰ Limited time offer</div>
<div class="timer-value" id="timer2">--:--:--</div>
</div>
<a href="?step=checkout" class="btn-primary">Get Access Now — &#x20A6;<?= number_format($productPriceRaw) ?></a>
</div>
</div>

<footer style="padding:40px 0;text-align:center;color:var(--sub);font-size:13px">
<div class="container">
<p>© 2024 Joala Store. All rights reserved.</p>
</div>
</footer>

</body>
</html>
<?php if ($step === 'checkout'): ?>
<div style="display:none">
<div id="checkoutPage" style="background:var(--bg);min-height:100vh;padding:40px 0">
<div class="container" style="max-width:640px">
<div style="text-align:center;margin-bottom:40px">
<a href="whatsapp-marketing-bundle.php" style="display:inline-flex;align-items:center;gap:8px;color:var(--sub);text-decoration:none;font-size:14px">← Back to product</a>
<h1 style="font-family:'Space Grotesk',sans-serif;font-size:36px;font-weight:800;margin-top:16px">Checkout</h1>
<p style="color:var(--sub)">Complete your order for the WhatsApp Marketing Bundle</p>
</div>
<div class="pricing-card">
<div class="pricing-popular">Complete Bundle</div>
<div class="pricing-header">
<h3>WhatsApp Marketing Bundle</h3>
<p>Instant digital delivery after payment</p>
</div>
<div class="pricing-price">
<span class="old">&#x20A6;<?= number_format($productOldRaw) ?></span>
<span class="current" id="checkoutPrice">&#x20A6;<?= number_format($productPriceRaw) ?></span>
</div>
<form method="POST" id="checkoutForm" style="text-align:left">
<div style="margin-bottom:20px">
<label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:var(--text)">Email Address *</label>
<input type="email" name="email" id="checkoutEmail" required value="<?= htmlspecialchars($email) ?>" style="width:100%;padding:14px 16px;border:1px solid var(--border);border-radius:12px;font-size:16px;font-family:inherit;background:var(--surface)">
</div>
<div style="margin-bottom:20px">
<label style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:var(--text)">Coupon Code</label>
<div style="display:flex;gap:10px">
<input type="text" name="coupon_code" id="couponInput" placeholder="Enter code" style="flex:1;padding:14px 16px;border:1px solid var(--border);border-radius:12px;font-size:15px;font-family:inherit;background:var(--surface)">
<button type="submit" name="apply_coupon" style="background:var(--card);border:1px solid var(--border);padding:0 20px;border-radius:12px;font-weight:600;cursor:pointer;font-size:14px">Apply</button>
</div>
<?php if ($couponMsg): ?>
<div style="font-size:13px;margin-top:8px"><?= $couponMsg ?></div>
<?php endif; ?>
</div>
<div id="paystackContainer"></div>
<div id="paymentSection">
<button type="button" id="payBtn" style="width:100%;background:var(--primary);color:#fff;padding:18px;border-radius:16px;font-size:18px;font-weight:700;border:none;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:10px">
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
Pay &#x20A6;<span id="payBtnAmount"><?= number_format($finalPrice) ?></span>
</button>
<p style="text-align:center;margin-top:12px;font-size:12px;color:var(--sub)">🔒 Secured by Paystack</p>
</div>
</form>
</div>
</div>
</div>
</div>
<script>document.getElementById('checkoutPage').style.display='block';</script>
<?php endif; ?>

<div class="timer-sticky" id="timerSticky">
<div class="timer-sticky-dot"></div>
<span>⏰ Offer ends: <span id="stickyTimer">--:--:--</span></span>
</div>

<div class="exit-popup" id="exitPopup">
<div class="exit-popup-box">
<button class="exit-popup-close" id="exitClose">×</button>
<div class="exit-popup-icon">💬</div>
<h2>Wait! Don't leave empty-handed</h2>
<p>Use this special code to save 15% on the WhatsApp Marketing Bundle</p>
<div class="exit-popup-code">
<input type="text" value="LAUNCH15" id="exitCode" readonly onclick="this.select()">
</div>
<div class="exit-popup-timer" id="exitTimer">Coupon expires in <strong>05:00</strong></div>
<a href="?step=checkout" class="exit-popup-link">Claim My 15% Discount</a>
</div>
</div>

<script src="https://js.paystack.co/v2/inline.js"></script>
<script>
(function(){
var timerOffset = <?= $timerOffset ?>;
var endTime = Date.now() + 3600000;

function updateTimers(){
var now = Date.now();
var remaining = Math.max(0, endTime - now);
var h = Math.floor(remaining / 3600000);
var m = Math.floor((remaining % 3600000) / 60000);
var s = Math.floor((remaining % 60000) / 1000);
var fmt = String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
var els = document.querySelectorAll('#timer,#timer2,#stickyTimer');
els.forEach(function(el){if(el) el.textContent = fmt});
if(remaining < 300000){
document.getElementById('timerSticky').classList.add('show');
}
}
setInterval(updateTimers, 1000);
updateTimers();

var popupShown = false;
var popupTimer = null;

function showExitPopup(){
if(popupShown || <?= $showPopup ? 'false' : 'true' ?>) return;
popupShown = true;
var popup = document.getElementById('exitPopup');
popup.classList.add('active');
var countDown = 300;
var timerEl = document.getElementById('exitTimer').querySelector('strong') || document.getElementById('exitTimer');
function tick(){
countDown--;
var m=Math.floor(countDown/60),s=countDown%60;
var el=document.getElementById('exitTimer');
if(el) el.innerHTML='Coupon expires in <strong>'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+'</strong>';
if(countDown<=0) clearInterval(timerEl);
}
timerEl = setInterval(tick, 1000);
tick();
}

document.addEventListener('mouseleave', function(e){
if(e.clientY < 10) showExitPopup();
});

popupTimer = setTimeout(showExitPopup, 25000);

document.getElementById('exitClose').addEventListener('click',function(){
document.getElementById('exitPopup').classList.remove('active');
clearTimeout(popupTimer);
popupShown = true;
});

window.copyPopupCode = function(){
var inp = document.getElementById('exitCode');
inp.select();
document.execCommand('copy');
alert('Coupon copied!');
};

<?php if ($step === 'checkout'): ?>
var currentAmount = <?= $finalPrice ?>;
var appliedCoupon = '<?= $_SESSION['whatsapp_bundle_coupon'] ?? '' ?>';
var emailInput = document.getElementById('checkoutEmail');
var payBtn = document.getElementById('payBtn');

payBtn.addEventListener('click', function(){
var email = emailInput.value.trim();
if(!email || !email.includes('@')){alert('Please enter a valid email');return;}

payBtn.disabled = true;
payBtn.innerHTML = '<span style="font-size:14px">Processing...</span>';

var formData = new FormData();
formData.append('action','init_payment');
formData.append('email', email);
formData.append('amount', currentAmount);
if(appliedCoupon) formData.append('coupon', appliedCoupon);

fetch(window.location.pathname, {method:'POST',body:formData})
.then(function(r){return r.json()})
.then(function(data){
new PaystackPop().newTransaction({
key: data.paystack_key,
email: data.email,
amount: data.amount,
reference: data.reference,
onClose: function(){
payBtn.disabled = false;
payBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Pay ₦'+currentAmount.toLocaleString();
},
onSuccess: function(response){
window.location.href = '<?= $baseUrl ?>/order/success?ref='+response.reference+'&order_id='+data.order_id;
}
});
})
.catch(function(err){
console.error('Payment init error:', err);
payBtn.disabled = false;
payBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Pay ₦'+currentAmount.toLocaleString();
alert('Payment setup failed. Please try again.');
});
});
<?php endif; ?>
})();
</script>
</body>
</html>