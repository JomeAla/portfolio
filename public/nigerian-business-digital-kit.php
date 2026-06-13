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
$productSlug = 'nigerian-business-digital-kit';
$productId = 9;
$funnelId = 22;

$product = Product::where('slug', $productSlug)->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Nigerian Business Digital Kit';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 25000;
$productOldRaw = $product ? (float)$product->price : 35000;

$pageKey = "nigeria_biz_viewed";
if (!isset($_SESSION[$pageKey])) { $_SESSION[$pageKey] = true; }

$timerOffset = isset($_SESSION['nigeria_biz_timer']) ? (int)$_SESSION['nigeria_biz_timer'] : rand(20000, 40000);
$_SESSION['nigeria_biz_timer'] = $timerOffset;

$email = $_SESSION['checkout_email'] ?? '';
$step = $_GET['step'] ?? 'landing';

$price = $step === 'checkout' ? $productPriceRaw : $productOldRaw;
$couponMsg = '';
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
        'order_number' => 'NB-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'funnel_id' => $funnelId,
    ]);
    $ref = 'NB_' . uniqid() . '_' . time();
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
            if ($c->discount_type === 'percentage') {
                $disc = $price * ($c->discount_value / 100);
                $maxD = $c->max_discount ?? PHP_INT_MAX;
                $disc = min($disc, $maxD);
            } else {
                $disc = min((float)$c->discount_value, $price);
            }
            $finalPrice = $price - $disc;
            $couponMsg = "<span style='color:#16a34a'>✓ You save &#x20A6;".number_format($disc)."</span>";
            $_SESSION['nigeria_coupon'] = $couponCode;
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
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--bg:#F0FDF4;
--surface:#FFFFFF;
--card:#ECFDF5;
--primary:#15803D;
--primary-dark:#166534;
--secondary:#22C55E;
--accent:#14532D;
--text:#14532D;
--sub:#64748B;
--border:#BBF7D0;
--gold:#D97706;
}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

.top-banner{background:linear-gradient(90deg,#15803D,#166534);color:#fff;padding:12px;text-align:center;font-size:.8rem;font-weight:600;display:flex;align-items:center;justify-content:center;gap:12px}
.top-banner span{background:#fff;color:#15803D;padding:2px 10px;border-radius:4px;font-size:.7rem}
.container{max-width:1200px;margin:0 auto;padding:0 24px}

.hero{padding:80px 0 60px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-100px;right:-100px;width:500px;height:500px;background:radial-gradient(circle,#15803D20,transparent 70%);border-radius:50%}
.hero-grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative;z-index:1}
.flag-stripe{width:48px;height:48px;background:linear-gradient(135deg,#15803D 33%,#fff 33%,#fff 66%,#15803D 66%);border-radius:8px;display:inline-block;margin-bottom:16px}
.eyebrow{display:inline-flex;align-items:center;gap:6px;background:var(--card);color:var(--primary);font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:20px;border:1px solid var(--border)}
.hero-title{font-family:'Sora',sans-serif;font-size:clamp(32px,5vw,58px);font-weight:800;line-height:1.1;margin-bottom:20px;letter-spacing:-.02em}
.hero-title span{color:var(--primary)}
.hero-sub{font-size:1rem;color:var(--sub);max-width:480px;margin-bottom:28px;line-height:1.8}
.price-row{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.price-old{font-size:1.1rem;color:var(--sub);text-decoration:line-through}
.price-new{font-family:'Sora',sans-serif;font-size:3rem;font-weight:800;color:var(--primary)}
.price-badge{background:var(--accent);color:#fff;padding:6px 16px;border-radius:10px;font-size:.8rem;font-weight:700}
.timer-box{background:var(--card);border:2px solid var(--border);border-radius:16px;padding:20px;text-align:center;margin-bottom:24px;max-width:300px}
.timer-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);margin-bottom:8px;font-weight:600}
.timer-value{font-family:'Sora',sans-serif;font-size:2rem;font-weight:800;color:var(--primary)}
.feature-tags{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:24px}
.feature-tag{display:inline-flex;align-items:center;gap:6px;background:var(--surface);border:1px solid var(--border);padding:8px 14px;border-radius:100px;font-size:.8rem;font-weight:600}
.feature-tag::before{content:'✓';color:var(--primary);font-size:.7rem}

.hero-visual{position:relative}
.img-frame{position:relative;border-radius:28px;overflow:hidden;aspect-ratio:4/3;border:2px solid var(--border);box-shadow:0 40px 80px rgba(21,128,61,.12)}
.img-frame img{width:100%;height:100%;object-fit:cover;display:block}
.img-placeholder{width:100%;height:100%;background:linear-gradient(135deg,#15803D 0%,#22C55E 100%);display:flex;align-items:center;justify-content:center;font-size:4rem}
.badge-float{position:absolute;bottom:16px;left:16px;background:var(--surface);padding:12px 20px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.1);display:flex;align-items:center;gap:10px}
.badge-float-icon{width:36px;height:36px;background:var(--card);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
.badge-float-text{font-size:.8rem;font-weight:600}.badge-float-text span{display:block;font-size:1.2rem;font-weight:800;color:var(--primary);font-family:'Sora',sans-serif}

.section{padding:80px 0}
.section-title{font-family:'Sora',sans-serif;font-size:clamp(28px,4vw,48px);font-weight:800;text-align:center;margin-bottom:12px;letter-spacing:-.02em}
.section-sub{text-align:center;color:var(--sub);font-size:1rem;margin-bottom:48px}

.biz-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.biz-card{background:var(--surface);border-radius:20px;padding:28px;border:1px solid var(--border);transition:all .3s}
.biz-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(21,128,61,.1);border-color:var(--primary)}
.biz-icon{width:52px;height:52px;background:var(--card);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:18px}
.biz-card h3{font-family:'Sora',sans-serif;font-size:1rem;font-weight:700;margin-bottom:8px}
.biz-card p{color:var(--sub);font-size:.85rem;line-height:1.7}
.biz-card.highlight{border:2px solid var(--primary);background:linear-gradient(var(--surface),var(--surface)),linear-gradient(var(--primary),var(--secondary));background-origin:border-box;background-clip:padding-box,border-box}

.pay-section{background:var(--surface);border-radius:32px;padding:64px;margin:60px 0;border:1px solid var(--border);text-align:center}
.pay-section h2{font-family:'Sora',sans-serif;font-size:clamp(24px,3.5vw,40px);font-weight:700;margin-bottom:24px}
.pay-logos{display:flex;align-items:center;justify-content:center;gap:20px;margin-bottom:32px;flex-wrap:wrap}
.pay-logo{background:var(--card);padding:10px 20px;border-radius:12px;font-size:.8rem;font-weight:700;color:var(--sub);font-family:'Sora',sans-serif}

.cta-section{background:linear-gradient(135deg,#15803D 0%,#166534 100%);border-radius:32px;padding:64px;text-align:center;margin:60px 0;color:#fff;position:relative;overflow:hidden}
.cta-section::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.cta-section h2{font-family:'Sora',sans-serif;font-size:clamp(28px,4vw,48px);font-weight:800;margin-bottom:12px;position:relative}
.cta-section p{font-size:1rem;margin-bottom:32px;opacity:.9;position:relative}
.btn-white{display:inline-block;background:#fff;color:var(--primary);padding:18px 48px;border-radius:16px;font-size:1rem;font-weight:700;text-decoration:none;transition:all .3s;position:relative}
.btn-white:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(0,0,0,.2)}

.timer-sticky{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--accent);color:#fff;padding:12px 28px;border-radius:100px;display:flex;align-items:center;gap:12px;font-size:.9rem;font-weight:600;z-index:100;box-shadow:0 8px 30px rgba(0,0,0,.3);opacity:0;transition:opacity .5s;pointer-events:none}
.timer-sticky.show{opacity:1;pointer-events:auto}
.pulse-dot{width:8px;height:8px;background:#dc2626;border-radius:50%;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

.exit-popup{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .4s}
.exit-popup.active{opacity:1;pointer-events:all}
.exit-box{background:var(--surface);border-radius:24px;padding:48px;max-width:460px;width:90%;text-align:center;position:relative;border:2px solid var(--primary)}
.exit-close{position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:var(--sub)}
.exit-icon{font-size:3rem;margin-bottom:16px}
.exit-box h2{font-family:'Sora',sans-serif;font-size:1.8rem;font-weight:800;margin-bottom:8px}
.exit-box p{color:var(--sub);margin-bottom:20px}
.exit-code{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.exit-code input{border:2px dashed var(--primary);background:var(--card);padding:14px 20px;border-radius:12px;font-size:1.1rem;font-weight:700;color:var(--primary);font-family:'Sora',sans-serif;flex:1;text-align:center;cursor:pointer;width:100%}
.exit-timer{font-size:.85rem;color:var(--sub);margin-bottom:20px}
.exit-link{display:inline-block;background:var(--primary);color:#fff;padding:14px 36px;border-radius:12px;font-weight:700;text-decoration:none;font-size:1rem}

@media(max-width:768px){
.hero-grid,.hero-inner,.hero-layout{grid-template-columns:1fr;gap:24px}
.hero,.hero{padding:80px 16px 40px}
.hero-title,.hero h1{font-size:clamp(1.8rem,6vw,2.5rem)}
.hero-sub,.hero p{font-size:.9rem;max-width:100%}
.nav-brand span{display:none}
.nav{padding:8px 16px;top:8px}
.price-row{flex-wrap:wrap;gap:10px}
.price-new{font-size:2rem}
.timer-box{padding:12px;max-width:100%}
.timer-value{font-size:1.5rem}
.features-grid,.biz-grid,.mod-grid,.audit-grid,.feat-grid{grid-template-columns:1fr;gap:16px}
.feat-card,.biz-card,.mod-card,.audit-item,.feature-card{padding:20px}
.feat-card h3,.biz-card h3,.mod-card h3,.audit-item h3{font-size:.95rem}
.section{padding:60px 16px}
.section-title{font-size:clamp(1.5rem,5vw,2rem)}
.cta-section,.bundle-section,.progress-section{padding:40px 20px}
.cta-section h2,.bundle-section h2{font-size:clamp(1.4rem,4vw,2rem)}
.btn-white,.cta-btn{padding:14px 28px;font-size:.95rem;width:100%;display:block;text-align:center}
.footer{padding:40px 16px 30px}
.checkout-page,.checkout-wrap{padding:20px 16px}
.checkout-card,.pricing-card,.order-box,.bundle-items,.progress-grid{grid-template-columns:1fr}
.checkout-card,.pricing-card,.order-box{padding:24px 16px}
.checkout-card input,.checkout-card textarea,.pricing-card input{width:100%;font-size:.9rem;padding:12px}
.pay-btn,.checkout-btn,.exit-link{padding:14px 20px;font-size:.95rem;width:100%;display:block;text-align:center}
.timer-sticky{width:calc(100% - 32px);left:16px;transform:none;padding:10px 16px;font-size:.8rem}
.exit-popup-box,.exit-box{padding:28px 16px;margin:12px}
.exit-popup-box h2,.exit-box h2{font-size:1.3rem}
.exit-code input,.exit-code-wrap input{font-size:1rem;padding:10px;width:100%}
.exit-link{padding:12px 20px;font-size:.9rem}
.bundle-items,.progress-grid{grid-template-columns:repeat(2,1fr)}
.stats-row{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:480px){
.hero,.hero{padding:70px 12px 32px}
.hero-title,.hero h1{font-size:1.6rem;letter-spacing:-.02em}
.eyebrow,.eyebrow-tag{padding:4px 12px;font-size:.65rem}
.price-old{font-size:1rem}
.timer-box{padding:10px}
.timer-value{font-size:1.3rem}
.feat-card,.biz-card,.mod-card,.audit-item,.feature-card,.pricing-card,.module-card{padding:16px}
.section{padding:48px 12px}
.section-title{font-size:1.4rem}
.section-sub{font-size:.85rem}
.cta-section,.bundle-section,.progress-section{padding:32px 12px;border-radius:16px}
.cta-section h2{font-size:1.3rem}
.cta-section p{font-size:.9rem}
.btn-white,.cta-btn,.cta-btn-white{font-size:.9rem;padding:12px 16px}
.strip-bar,.top-bar,.strip-top{font-size:.7rem;padding:8px 12px}
nav{flex-direction:column;gap:8px;text-align:center}
nav a{display:none}
.timer-sticky{font-size:.75rem;padding:8px 12px}
.bundle-item,.progress-item{padding:16px 12px}
.bundle-item h4,.progress-label{font-size:.8rem}
.stats-row{grid-template-columns:1fr 1fr}
.stat-item,.stat-box{padding:16px 8px}
.exit-popup{justify-content:flex-end;align-items:flex-end;padding:16px}
.exit-popup-box,.exit-box{border-radius:16px;margin:0;width:100%}
.exit-popup-close{top:8px;right:8px;font-size:20px}
.checkout-card,.pricing-card,.order-box{border-radius:16px}
}
</style>
</head>
<body>

<div class="top-banner">
🇳🇬 Built for Nigerian Entrepreneurs — Get Your Digital Toolkit Today
<span>40% OFF</span>
</div>

<div class="container">
<nav style="padding:20px 0;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)">
<div style="font-family:'Sora',sans-serif;font-size:22px;font-weight:800">Joala<span style="color:var(--primary)">.</span></div>
<a href="<?= $baseUrl ?>" style="color:var(--sub);text-decoration:none;font-size:.9rem;font-weight:500">← Store</a>
</nav>

<section class="hero">
<div class="hero-grid">
<div>
<div class="flag-stripe"></div>
<div class="eyebrow">🇳🇬 For Nigerian Businesses</div>
<h1 class="hero-title">Go Digital &<br><span>Grow Your Business</span></h1>
<p class="hero-sub">A complete digital toolkit designed specifically for Nigerian businesses. Get your website, automate your WhatsApp, and dominate your local market online.</p>
<div class="price-row">
<span class="price-old">&#x20A6;<?= number_format($productOldRaw) ?></span>
<span class="price-new">&#x20A6;<?= number_format($productPriceRaw) ?></span>
<span class="price-badge">40% OFF</span>
</div>
<div class="timer-box">
<div class="timer-label">⏰ Offer expires in</div>
<div class="timer-value" id="timer">--:--:--</div>
</div>
<div class="feature-tags">
<span class="feature-tag">✓ Website Template</span>
<span class="feature-tag">✓ WhatsApp Setup</span>
<span class="feature-tag">✓ Local SEO Guide</span>
<span class="feature-tag">✓ Social Media Kit</span>
<span class="feature-tag">✓ Invoice Templates</span>
</div>
<a href="?step=checkout" style="display:inline-block;background:var(--primary);color:#fff;padding:16px 40px;border-radius:16px;font-weight:700;font-size:1rem;text-decoration:none;margin-top:8px;font-family:'Sora',sans-serif">Get Started →</a>
</div>
<div class="hero-visual">
<div class="img-frame">
<?php if ($productImage): ?>
<img src="/<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($productTitle) ?>">
<?php else: ?>
<div class="img-placeholder">🇳🇬</div>
<?php endif; ?>
<div class="badge-float">
<div class="badge-float-icon">🎯</div>
<div class="badge-float-text">
Instant Access
<span>&#x20A6;<?= number_format($productPriceRaw) ?></span>
</div>
</div>
</div>
</div>
</div>
</section>
</div>

<section class="section">
<div class="container">
<h2 class="section-title">Everything Your Nigerian Business Needs</h2>
<p class="section-sub">6 powerful tools to establish and grow your online presence</p>
<div class="biz-grid">
<div class="biz-card">
<div class="biz-icon">🌐</div>
<h3>Business Website</h3>
<p>Professional multi-page website template ready for Nigerian businesses. Mobile-responsive and SEO-optimized.</p>
</div>
<div class="biz-card">
<div class="biz-icon">💬</div>
<h3>WhatsApp Automation</h3>
<p>Complete setup guide for WhatsApp Business. Automate responses, broadcast messages, and close more sales.</p>
</div>
<div class="biz-card">
<div class="biz-icon">📍</div>
<h3>Google My Business</h3>
<p>Step-by-step guide to ranking on Google Maps. Get found by customers searching for your services.</p>
</div>
<div class="biz-card">
<div class="biz-icon">📱</div>
<h3>Social Media Kit</h3>
<p>Brand assets, posting templates, and a 30-day content calendar for Instagram, Facebook, and X.</p>
</div>
<div class="biz-card">
<div class="biz-icon">💰</div>
<h3>Invoice Templates</h3>
<p>Nigerian naira formatted invoice and receipt templates. Professional billing that gets you paid faster.</p>
</div>
<div class="biz-card highlight">
<div class="biz-icon">📋</div>
<h3>Contract Templates</h3>
<p>Service agreement and LOC templates adapted for Nigerian business practices and legal requirements.</p>
</div>
</div>
</div>
</section>

<div class="container">
<div class="pay-section">
<h2>💳 Accepted Payment Methods</h2>
<div class="pay-logos">
<div class="pay-logo">Paystack</div>
<div class="pay-logo">Bank Transfer</div>
<div class="pay-logo">Nigerian Cards</div>
</div>
<p style="color:var(--sub);font-size:.9rem;max-width:400px;margin:0 auto">All Nigerian payment methods supported. Instant delivery after payment confirmation.</p>
</div>

<div class="cta-section">
<h2>Ready to take your business online?</h2>
<p>Everything you need to establish a powerful digital presence</p>
<div style="margin-bottom:20px">
<div style="font-size:.85rem;opacity:.8;text-decoration:line-through">&#x20A6;<?= number_format($productOldRaw) ?></div>
<div style="font-size:2.5rem;font-weight:800;font-family:'Sora',sans-serif">&#x20A6;<?= number_format($productPriceRaw) ?></div>
</div>
<a href="?step=checkout" class="btn-white">Get Your Toolkit →</a>
</div>
</div>

<footer style="padding:40px 0;text-align:center;color:var(--sub);font-size:.8rem;border-top:1px solid var(--border)">
<div class="container">
<p>© 2024 Joala Store — Built for Nigerian Entrepreneurs 🇳🇬</p>
</div>
</footer>

</body>
</html>
<?php if ($step === 'checkout'): ?>
<div style="display:none">
<div id="checkoutPage" style="background:var(--bg);min-height:100vh;padding:40px 0">
<div class="container" style="max-width:560px">
<div style="text-align:center;margin-bottom:40px">
<a href="nigerian-business-digital-kit.php" style="display:inline-flex;align-items:center;gap:8px;color:var(--sub);text-decoration:none;font-size:.9rem">← Back</a>
<h1 style="font-family:'Sora',sans-serif;font-size:2.2rem;font-weight:800;margin-top:16px">Checkout</h1>
<p style="color:var(--sub)">Complete your order for the <?= htmlspecialchars($productTitle) ?></p>
</div>
<div style="background:var(--surface);border-radius:24px;padding:40px;border:2px solid var(--primary);position:relative">
<div style="text-align:center;margin-bottom:28px">
<div style="font-family:'Sora',sans-serif;font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);margin-bottom:6px">Total</div>
<div style="font-family:'Sora',sans-serif;font-size:2.8rem;font-weight:800;color:var(--primary)">&#x20A6;<?= number_format($finalPrice) ?></div>
<?php if ($finalPrice < $price): ?>
<div style="font-size:.9rem;color:#16a34a;margin-top:4px">You saved &#x20A6;<?= number_format($price - $finalPrice) ?>!</div>
<?php endif; ?>
</div>
<form method="POST">
<div style="margin-bottom:18px">
<label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:6px">Email *</label>
<input type="email" id="checkoutEmail" required value="<?= htmlspecialchars($email) ?>" style="width:100%;padding:14px 16px;border:1px solid var(--border);border-radius:12px;font-size:1rem;font-family:inherit;background:var(--card)">
</div>
<div style="margin-bottom:18px">
<label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:6px">Coupon</label>
<div style="display:flex;gap:10px">
<input type="text" name="coupon_code" id="couponInput" placeholder="Code" style="flex:1;padding:14px 16px;border:1px solid var(--border);border-radius:12px;font-size:.9rem;font-family:inherit;background:var(--card)">
<button type="submit" name="apply_coupon" style="background:var(--card);border:1px solid var(--border);padding:0 20px;border-radius:12px;font-weight:600;cursor:pointer;font-size:.85rem">Apply</button>
</div>
<?php if ($couponMsg): ?><div style="font-size:.8rem;margin-top:8px"><?= $couponMsg ?></div><?php endif; ?>
</div>
<button type="button" id="payBtn" style="width:100%;background:var(--primary);color:#fff;padding:18px;border-radius:16px;font-size:1.1rem;font-weight:700;border:none;cursor:pointer;font-family:'Sora',sans-serif">
Pay &#x20A6;<?= number_format($finalPrice) ?>
</button>
<p style="text-align:center;margin-top:12px;font-size:.75rem;color:var(--sub)">🔒 Paystack</p>
</form>
</div>
</div>
</div>
</div>
<script>document.getElementById('checkoutPage').style.display='block';</script>
<?php endif; ?>

<div class="timer-sticky" id="timerSticky">
<div class="pulse-dot"></div>
<span>⏰ Ends: <span id="stickyTimer">--:--:--</span></span>
</div>

<div class="exit-popup" id="exitPopup">
<div class="exit-box">
<button class="exit-close" id="exitClose">×</button>
<div class="exit-icon">🇳🇬</div>
<h2>Wait! Get 15% off</h2>
<p>Use this code to save on your Nigerian Business Kit</p>
<div class="exit-code">
<input type="text" value="LAUNCH15" id="exitCode" readonly onclick="this.select()">
</div>
<div class="exit-timer" id="exitTimer">Expires in <strong>05:00</strong></div>
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
if(el) el.innerHTML='Expires in <strong>'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+'</strong>';
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
var appliedCoupon = '<?= $_SESSION['nigeria_coupon'] ?? '' ?>';
var emailInput = document.getElementById('checkoutEmail');
var payBtn = document.getElementById('payBtn');
payBtn.addEventListener('click', function(){
var email = emailInput.value.trim();
if(!email || !email.includes('@')){alert('Enter valid email');return;}
payBtn.disabled = true;
payBtn.innerHTML = 'Processing...';
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
alert('Payment failed. Try again.');
});
});
<?php endif; ?>
})();
</script>
</body>
</html>