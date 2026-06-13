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

$baseUrl = 'https://joala.com.ng';
$productSlug = 'freelancer-toolkit';
$productId = 8;
$funnelId = 18;

$product = Product::where('slug', $productSlug)->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Freelancer Toolkit';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 15000;
$productOldRaw = $product ? (float)$product->price : 25000;

$pageKey = "freelancer_toolkit_viewed";
if (!isset($_SESSION[$pageKey])) { $_SESSION[$pageKey] = true; }

$timerOffset = isset($_SESSION['freelancer_toolkit_timer']) ? (int)$_SESSION['freelancer_toolkit_timer'] : rand(20000, 40000);
$_SESSION['freelancer_toolkit_timer'] = $timerOffset;

$email = $_SESSION['checkout_email'] ?? '';
$step = $_GET['step'] ?? 'landing';
$showPopup = isset($_SESSION['freelancer_exit_shown']) ? false : true;

$price = $step === 'checkout' ? $productPriceRaw : $productOldRaw;
$discount = 0;
$couponMsg = '';
$couponSuccess = false;
$finalPrice = $price;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] ?? '' === 'init_payment') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $coupon = trim($_POST['coupon'] ?? '');
    $couponApplied = null;
    if ($coupon) {
        $c = Coupon::where('code', $coupon)->first();
        if ($c && $c->isValid()) {
            $couponSuccess = true;
            $couponApplied = $coupon;
            if ($c->discount_type === 'percentage') {
                $disc = $amount * ($c->discount_value / 100);
                $maxD = $c->max_discount ?? PHP_INT_MAX;
                $disc = min($disc, $maxD);
            } else {
                $disc = min((float)$c->discount_value, $amount);
            }
            $amount = max(0.01, $amount - $disc);
        }
    }
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $order = Order::create([
        'user_id' => $userId, 'product_id' => $productId, 'amount' => $amount,
        'original_amount' => $price, 'coupon_used' => $couponApplied,
        'payment_status' => 'pending',
        'order_number' => 'FT-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'funnel_id' => $funnelId,
    ]);
    $ref = 'FT_' . uniqid() . '_' . time();
    echo json_encode([
        'order_id' => $order->id, 'paystack_key' => 'pk_live_xxxx',
        'amount' => (int)($amount * 100), 'email' => $email, 'reference' => $ref,
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
            $couponMsg = "<span style='color:#16a34a'>✓ You save &#x20A6;".number_format($disc)."</span>";
            $_SESSION['freelancer_coupon'] = $couponCode;
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
<title><?= htmlspecialchars($productTitle) ?> — Joala Store</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--bg:#FFFBEB;
--surface:#FFFFFF;
--card:#FFF7ED;
--primary:#F97316;
--primary-dark:#C2410C;
--secondary:#FDBA74;
--accent:#9A3412;
--text:#1C1917;
--sub:#78716C;
--border:#FED7AA;
}
html{scroll-behavior:smooth}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

.strip-top{background:var(--primary);color:#fff;padding:10px;text-align:center;font-size:13px;font-weight:600}
.container{max-width:1200px;margin:0 auto;padding:0 24px}

.hero{position:relative;padding:80px 0 60px;overflow:hidden}
.hero::before{content:'';position:absolute;top:0;right:-10%;width:600px;height:600px;background:radial-gradient(circle,#F9731633,transparent 70%);border-radius:50%}
.hero::after{content:'';position:absolute;bottom:-20%;left:-5%;width:400px;height:400px;background:radial-gradient(circle,#FBBF2433,transparent 70%);border-radius:50%}
.hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative;z-index:1}
.eyebrow{display:inline-flex;align-items:center;gap:8px;background:var(--primary);color:#fff;font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:20px}
.hero-title{font-family:'Bricolage Grotesque',sans-serif;font-size:clamp(36px,5vw,64px);font-weight:800;line-height:1.05;letter-spacing:-.03em;margin-bottom:20px}
.hero-title span{color:var(--primary)}
.hero-sub{font-size:1.05rem;color:var(--sub);max-width:500px;margin-bottom:32px;line-height:1.8}
.price-row{display:flex;align-items:center;gap:16px;margin-bottom:24px}
.price-old{font-size:1.2rem;color:var(--sub);text-decoration:line-through}
.price-new{font-family:'Bricolage Grotesque',sans-serif;font-size:3rem;font-weight:800;color:var(--primary)}
.price-badge{background:var(--accent);color:#fff;padding:6px 16px;border-radius:10px;font-size:.8rem;font-weight:700}
.timer-box{background:var(--card);border:2px dashed var(--secondary);border-radius:16px;padding:20px;text-align:center;margin-bottom:24px;max-width:320px}
.timer-label{font-size:.75rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);margin-bottom:8px;font-weight:600}
.timer-value{font-family:'Bricolage Grotesque',sans-serif;font-size:2.2rem;font-weight:800;color:var(--primary)}
.features-list{list-style:none;display:flex;flex-direction:column;gap:10px}
.features-list li{display:flex;align-items:center;gap:10px;font-size:.95rem}
.features-list li::before{content:'✓';display:flex;align-items:center;justify-content:center;width:26px;height:26px;background:var(--primary);color:#fff;border-radius:50%;font-size:.75rem;font-weight:700;flex-shrink:0}

.hero-visual{position:relative}
.hero-img-wrap{position:relative;border-radius:28px;overflow:hidden;aspect-ratio:4/3;box-shadow:0 40px 80px rgba(249,115,22,.15);border:2px solid var(--border)}
.hero-img-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.hero-img-placeholder{width:100%;height:100%;background:linear-gradient(135deg,#F97316 0%,#FBBF24 100%);display:flex;align-items:center;justify-content:center;font-size:5rem}
.hero-badge-float{position:absolute;bottom:20px;right:20px;background:var(--surface);padding:12px 20px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.1);font-size:.85rem;font-weight:600;color:var(--text)}
.hero-badge-float span{display:block;font-size:1.3rem;font-weight:800;color:var(--primary);font-family:'Bricolage Grotesque',sans-serif}

.section{padding:80px 0}
.section-title{font-family:'Bricolage Grotesque',sans-serif;font-size:clamp(28px,4vw,48px);font-weight:800;text-align:center;margin-bottom:12px;letter-spacing:-.02em}
.section-sub{text-align:center;color:var(--sub);font-size:1rem;margin-bottom:56px}
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.feat-card{background:var(--surface);border-radius:24px;padding:32px;border:1px solid var(--border);transition:all .3s}
.feat-card:hover{transform:translateY(-6px);box-shadow:0 20px 50px rgba(249,115,22,.1);border-color:var(--primary)}
.feat-icon{width:56px;height:56px;background:var(--card);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:20px}
.feat-card h3{font-family:'Bricolage Grotesque',sans-serif;font-size:1.15rem;font-weight:700;margin-bottom:10px}
.feat-card p{color:var(--sub);font-size:.9rem;line-height:1.7}
.feat-card.popular{border:2px solid var(--primary);position:relative;background:linear-gradient(var(--surface),var(--surface)),linear-gradient(var(--primary),#FBBF24);background-origin:border-box;background-clip:padding-box,border-box}
.feat-card.popular::before{content:'BEST VALUE';position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--primary);color:#fff;padding:4px 20px;border-radius:50px;font-size:.65rem;font-weight:700;letter-spacing:.1em}

.bundle-section{background:var(--surface);border-radius:32px;padding:64px;margin:60px 0;border:2px solid var(--border);position:relative;overflow:hidden}
.bundle-section::before{content:'';position:absolute;top:0;right:0;width:300px;height:300px;background:radial-gradient(circle,#F9731620,transparent)}
.bundle-section h2{font-family:'Bricolage Grotesque',sans-serif;font-size:clamp(28px,4vw,48px);font-weight:800;text-align:center;margin-bottom:16px}
.bundle-section p{text-align:center;color:var(--sub);margin-bottom:40px;font-size:1rem}
.bundle-items{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.bundle-item{background:var(--bg);border-radius:16px;padding:20px;text-align:center;border:1px solid var(--border)}
.bundle-item-icon{font-size:2rem;margin-bottom:10px}
.bundle-item h4{font-size:.9rem;font-weight:700;margin-bottom:4px}
.bundle-item p{font-size:.75rem;color:var(--sub)}

.cta-section{background:linear-gradient(135deg,var(--primary) 0%,#FBBF24 100%);border-radius:32px;padding:64px;text-align:center;margin:60px 0;color:#fff;position:relative;overflow:hidden}
.cta-section::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.cta-section h2{font-family:'Bricolage Grotesque',sans-serif;font-size:clamp(28px,4vw,48px);font-weight:800;margin-bottom:12px;position:relative}
.cta-section p{font-size:1rem;margin-bottom:32px;opacity:.9;position:relative}
.btn-white{display:inline-block;background:#fff;color:var(--primary);padding:18px 48px;border-radius:16px;font-size:1.1rem;font-weight:700;text-decoration:none;transition:all .3s;position:relative}
.btn-white:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(0,0,0,.2)}

.timer-sticky{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--text);color:#fff;padding:12px 28px;border-radius:100px;display:flex;align-items:center;gap:12px;font-size:.9rem;font-weight:600;z-index:100;box-shadow:0 8px 30px rgba(0,0,0,.3);opacity:0;transition:opacity .5s;pointer-events:none}
.timer-sticky.show{opacity:1;pointer-events:auto}
.timer-sticky-dot{width:8px;height:8px;background:#dc2626;border-radius:50%;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

.exit-popup{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .4s}
.exit-popup.active{opacity:1;pointer-events:all}
.exit-popup-box{background:var(--surface);border-radius:24px;padding:48px;max-width:460px;width:90%;text-align:center;position:relative;border:2px solid var(--primary)}
.exit-popup-close{position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:var(--sub)}
.exit-popup-icon{font-size:3rem;margin-bottom:16px}
.exit-popup-box h2{font-family:'Bricolage Grotesque',sans-serif;font-size:1.8rem;font-weight:800;margin-bottom:8px}
.exit-popup-box p{color:var(--sub);margin-bottom:20px}
.exit-code-wrap{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.exit-code-wrap input{border:2px dashed var(--primary);background:var(--card);padding:14px 20px;border-radius:12px;font-size:1.1rem;font-weight:700;color:var(--primary);font-family:'Bricolage Grotesque',sans-serif;flex:1;text-align:center;cursor:pointer;width:100%}
.exit-timer{font-size:.85rem;color:var(--sub);margin-bottom:20px}
.exit-link{display:inline-block;background:var(--primary);color:#fff;padding:14px 36px;border-radius:12px;font-weight:700;text-decoration:none;font-size:1rem}

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
</style>
</head>
<body>

<div class="strip-top">🔥 Freelancer's Secret Weapon — Get It Now at 40% OFF</div>

<div class="container">
<nav style="padding:20px 0;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)">
<div style="font-family:'Bricolage Grotesque',sans-serif;font-size:22px;font-weight:800">Joala<span style="color:var(--primary)">.</span></div>
<a href="<?= $baseUrl ?>" style="color:var(--sub);text-decoration:none;font-size:.9rem;font-weight:500">← Back to store</a>
</nav>

<section class="hero">
<div class="hero-grid">
<div>
<div class="eyebrow">⚡ For Nigerian Freelancers</div>
<h1 class="hero-title"><?= htmlspecialchars($productTitle) ?><br><span>& Win More Clients</span></h1>
<p class="hero-sub">Everything you need to launch your freelance career — contracts, invoices, proposals, and a complete system to get paid on time, every time.</p>
<div class="price-row">
<span class="price-old">&#x20A6;<?= number_format($productOldRaw) ?></span>
<span class="price-new">&#x20A6;<?= number_format($productPriceRaw) ?></span>
<span class="price-badge">40% OFF</span>
</div>
<div class="timer-box">
<div class="timer-label">⏰ Offer expires in</div>
<div class="timer-value" id="timer">--:--:--</div>
</div>
<ul class="features-list">
<li>10+ Professional contract templates</li>
<li>Invoice & proposal generator</li>
<li>Client rate calculator</li>
<li>Project management kit</li>
</ul>
<a href="?step=checkout" class="btn-white" style="display:inline-block;background:var(--primary);color:#fff;padding:16px 40px;border-radius:16px;font-weight:700;font-size:1rem;text-decoration:none;margin-top:8px">Get Access Now →</a>
</div>
<div class="hero-visual">
<div class="hero-img-wrap">
<?php if ($productImage): ?>
<img src="/<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($productTitle) ?>">
<?php else: ?>
<div class="hero-img-placeholder">🎨</div>
<?php endif; ?>
</div>
<div class="hero-badge-float">
Instant Download
<span>&#x20A6;<?= number_format($productPriceRaw) ?></span>
</div>
</div>
</div>
</section>
</div>

<section class="section">
<div class="container">
<h2 class="section-title">What's Inside the Toolkit</h2>
<p class="section-sub">6 powerful resources to elevate your freelance business</p>
<div class="features-grid">
<div class="feat-card">
<div class="feat-icon">📄</div>
<h3>Contract Templates</h3>
<p>10+ legally-vetted contract templates for different project types. Protect yourself and your clients.</p>
</div>
<div class="feat-card">
<div class="feat-icon">💰</div>
<h3>Invoice Generator</h3>
<p>Ready-to-use invoice templates in multiple formats. Get paid faster with professional billing.</p>
</div>
<div class="feat-card">
<div class="feat-icon">📊</div>
<h3>Rate Calculator</h3>
<p>Find your ideal hourly rate based on your goals, expenses, and market rates in Nigeria.</p>
</div>
<div class="feat-card">
<div class="feat-icon">📋</div>
<h3>Project Kit</h3>
<p>Checklists, timelines, and tracking sheets to manage any freelance project smoothly.</p>
</div>
<div class="feat-card">
<div class="feat-icon">🎯</div>
<h3>Pitch Deck</h3>
<p>Winning pitch templates that convert leads into paying clients every time.</p>
</div>
<div class="feat-card popular">
<div class="feat-icon">🚀</div>
<h3>Quick-Start Guide</h3>
<p>30-page guide to building a sustainable freelance business from scratch in Nigeria.</p>
</div>
</div>
</div>
</section>

<div class="container">
<div class="bundle-section">
<h2>Everything You Need to Freelance Successfully</h2>
<p>6 resources, one complete system</p>
<div class="bundle-items">
<div class="bundle-item"><div class="bundle-item-icon">📄</div><h4>10+ Contracts</h4><p>Legal protection</p></div>
<div class="bundle-item"><div class="bundle-item-icon">💵</div><h4>Invoices</h4><p>Get paid faster</p></div>
<div class="bundle-item"><div class="bundle-item-icon">📈</div><h4>Rate Calculator</h4><p>Know your worth</p></div>
<div class="bundle-item"><div class="bundle-item-icon">📋</div><h4>PM Kit</h4><p>Stay organized</p></div>
<div class="bundle-item"><div class="bundle-item-icon">🎯</div><h4>Pitch Deck</h4><p>Win clients</p></div>
<div class="bundle-item"><div class="bundle-item-icon">📚</div><h4>Guide</h4><p>30 pages</p></div>
</div>
</div>

<div class="cta-section">
<h2>Ready to take your freelance career seriously?</h2>
<p>Get the complete toolkit and start winning clients today</p>
<div style="margin-bottom:20px">
<div style="font-size:.85rem;opacity:.8">Original Price: <span style="text-decoration:line-through">&#x20A6;<?= number_format($productOldRaw) ?></span></div>
<div style="font-size:2.5rem;font-weight:800;font-family:'Bricolage Grotesque',sans-serif">&#x20A6;<?= number_format($productPriceRaw) ?></div>
</div>
<a href="?step=checkout" class="btn-white">Get Instant Access →</a>
</div>
</div>

<footer style="padding:40px 0;text-align:center;color:var(--sub);font-size:.8rem;border-top:1px solid var(--border)">
<div class="container">
<p>© 2024 Joala Store. All rights reserved.</p>
</div>
</footer>

</body>
</html>
<?php if ($step === 'checkout'): ?>
<div style="display:none">
<div id="checkoutPage" style="background:var(--bg);min-height:100vh;padding:40px 0">
<div class="container" style="max-width:560px">
<div style="text-align:center;margin-bottom:40px">
<a href="freelancer-toolkit.php" style="display:inline-flex;align-items:center;gap:8px;color:var(--sub);text-decoration:none;font-size:.9rem">← Back</a>
<h1 style="font-family:'Bricolage Grotesque',sans-serif;font-size:2.2rem;font-weight:800;margin-top:16px">Checkout</h1>
<p style="color:var(--sub)">Complete your order for the <?= htmlspecialchars($productTitle) ?></p>
</div>
<div style="background:var(--surface);border-radius:24px;padding:40px;border:2px solid var(--primary);position:relative;overflow:hidden">
<?php if ($finalPrice < $price): ?>
<div style="position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--primary),#FBBF24)"></div>
<?php endif; ?>
<div style="text-align:center;margin-bottom:28px">
<div style="font-size:.8rem;color:var(--sub);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px">Total Amount</div>
<div style="font-family:'Bricolage Grotesque',sans-serif;font-size:2.8rem;font-weight:800;color:var(--primary)">&#x20A6;<?= number_format($finalPrice) ?></div>
<?php if ($finalPrice < $price): ?>
<div style="font-size:.9rem;color:#16a34a;margin-top:4px">You saved &#x20A6;<?= number_format($price - $finalPrice) ?>!</div>
<?php endif; ?>
</div>
<form method="POST" style="text-align:left">
<div style="margin-bottom:18px">
<label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:6px;color:var(--text)">Email Address *</label>
<input type="email" id="checkoutEmail" required value="<?= htmlspecialchars($email) ?>" style="width:100%;padding:14px 16px;border:1px solid var(--border);border-radius:12px;font-size:1rem;font-family:inherit;background:var(--bg)">
</div>
<div style="margin-bottom:18px">
<label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:6px;color:var(--text)">Coupon Code</label>
<div style="display:flex;gap:10px">
<input type="text" name="coupon_code" id="couponInput" placeholder="Enter code" style="flex:1;padding:14px 16px;border:1px solid var(--border);border-radius:12px;font-size:.9rem;font-family:inherit;background:var(--bg)">
<button type="submit" name="apply_coupon" style="background:var(--card);border:1px solid var(--border);padding:0 20px;border-radius:12px;font-weight:600;cursor:pointer;font-size:.85rem">Apply</button>
</div>
<?php if ($couponMsg): ?><div style="font-size:.8rem;margin-top:8px"><?= $couponMsg ?></div><?php endif; ?>
</div>
<button type="button" id="payBtn" style="width:100%;background:var(--primary);color:#fff;padding:18px;border-radius:16px;font-size:1.1rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:10px">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
Pay &#x20A6;<?= number_format($finalPrice) ?>
</button>
<p style="text-align:center;margin-top:12px;font-size:.75rem;color:var(--sub)">🔒 Secured by Paystack</p>
</form>
</div>
</div>
</div>
</div>
<script>document.getElementById('checkoutPage').style.display='block';</script>
<?php endif; ?>

<div class="timer-sticky" id="timerSticky">
<div class="timer-sticky-dot"></div>
<span>⏰ Ends: <span id="stickyTimer">--:--:--</span></span>
</div>

<div class="exit-popup" id="exitPopup">
<div class="exit-popup-box">
<button class="exit-popup-close" id="exitClose">×</button>
<div class="exit-popup-icon">💰</div>
<h2>Wait! Here's 15% off</h2>
<p>Use this code to save on your freelancer toolkit order</p>
<div class="exit-code-wrap">
<input type="text" value="LAUNCH15" id="exitCode" readonly onclick="this.select()">
</div>
<div class="exit-timer" id="exitTimer">Coupon expires in <strong>05:00</strong></div>
<a href="?step=checkout" class="exit-link">Claim My 15% Discount</a>
</div>
</div>

<script src="https://js.paystack.co/v2/inline.js"></script>
<script>
(function(){
var timerOffset = <?= $timerOffset ?>;
var endTime = Date.now() + timerOffset;
function updateTimers(){
var now = Date.now();
var remaining = Math.max(0, endTime - now);
var h=Math.floor(remaining/3600000), m=Math.floor((remaining%3600000)/60000), s=Math.floor((remaining%60000)/1000);
var fmt=String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
document.querySelectorAll('#timer,#stickyTimer').forEach(function(el){if(el)el.textContent=fmt});
if(remaining < 300000) document.getElementById('timerSticky').classList.add('show');
}
setInterval(updateTimers, 1000);
updateTimers();

var popupShown = false;
function showExitPopup(){
if(popupShown) return;
popupShown = true;
document.getElementById('exitPopup').classList.add('active');
var countDown = 300;
var timerEl = setInterval(function(){
countDown--;
var m=Math.floor(countDown/60),s=countDown%60;
var el=document.getElementById('exitTimer');
if(el) el.innerHTML='Coupon expires in <strong>'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+'</strong>';
if(countDown<=0) clearInterval(timerEl);
}, 1000);
}
document.addEventListener('mouseleave', function(e){ if(e.clientY < 10) showExitPopup(); });
setTimeout(showExitPopup, 25000);
document.getElementById('exitClose').addEventListener('click', function(){
document.getElementById('exitPopup').classList.remove('active');
popupShown = true;
});

<?php if ($step === 'checkout'): ?>
var currentAmount = <?= $finalPrice ?>;
var appliedCoupon = '<?= $_SESSION['freelancer_coupon'] ?? '' ?>';
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
var paystack = PaystackPop.setup({
key: data.paystack_key, email: data.email, amount: data.amount, reference: data.reference,
onClose: function(){payBtn.disabled=false;payBtn.innerHTML='Pay ₦'+currentAmount.toLocaleString();},
callback: function(response){window.location.href='<?= $baseUrl ?>/order/success?ref='+response.reference+'&order_id='+data.order_id;}
});
paystack.openIframe();
})
.catch(function(err){
payBtn.disabled=false;payBtn.innerHTML='Pay ₦'+currentAmount.toLocaleString();
alert('Payment setup failed. Please try again.');
});
});
<?php endif; ?>
})();
</script>
</body>
</html>