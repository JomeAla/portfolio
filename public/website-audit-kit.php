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
$productSlug = 'website-audit-kit';
$productId = 14;
$funnelId = 20;

$product = Product::where('slug', $productSlug)->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Website Audit Kit';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 12000;
$productOldRaw = $product ? (float)$product->price : 25000;

$pageKey = "audit_kit_viewed";
if (!isset($_SESSION[$pageKey])) { $_SESSION[$pageKey] = true; }

$timerOffset = isset($_SESSION['audit_kit_timer']) ? (int)$_SESSION['audit_kit_timer'] : rand(20000, 40000);
$_SESSION['audit_kit_timer'] = $timerOffset;

$email = $_SESSION['checkout_email'] ?? '';
$step = $_GET['step'] ?? 'landing';

$price = $step === 'checkout' ? $productPriceRaw : $productOldRaw;
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
        'order_number' => 'AK-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'funnel_id' => $funnelId,
    ]);
    $ref = 'AK_' . uniqid() . '_' . time();
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
            $couponMsg = "<span style='color:#0d9488'>✓ You save &#x20A6;".number_format($disc)."</span>";
            $_SESSION['audit_coupon'] = $couponCode;
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
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--bg:#F0FDFA;
--surface:#FFFFFF;
--card:#F0FDFA;
--primary:#0D9488;
--primary-dark:#0F766E;
--secondary:#5EEAD4;
--accent:#14B8A6;
--text:#134E4A;
--sub:#64748B;
--border:#CCFBF1;
--grid:#E0F2F1;
}
html{scroll-behavior:smooth}
body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

.top-bar{background:var(--primary);color:#fff;padding:10px;text-align:center;font-size:.8rem;font-weight:600;font-family:'JetBrains Mono',monospace}
.container{max-width:1200px;margin:0 auto;padding:0 24px}

.grid-bg{position:fixed;inset:0;background-image:linear-gradient(var(--grid) 1px,transparent 1px),linear-gradient(90deg,var(--grid) 1px,transparent 1px);background-size:40px 40px;opacity:.5;z-index:-1;pointer-events:none}

.hero{padding:80px 0 60px;position:relative}
.hero-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center}
.eyebrow{display:inline-flex;align-items:center;gap:6px;background:var(--primary);color:#fff;font-size:.7rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:20px;font-family:'JetBrains Mono',monospace}
.hero-title{font-family:'JetBrains Mono',monospace;font-size:clamp(32px,4.5vw,56px);font-weight:700;line-height:1.1;margin-bottom:20px;letter-spacing:-.02em}
.hero-title span{color:var(--primary)}
.hero-sub{font-size:1rem;color:var(--sub);max-width:480px;margin-bottom:28px;line-height:1.8}

.code-block{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:20px;font-family:'JetBrains Mono',monospace;font-size:.8rem;color:var(--primary-dark)}
.code-block::before{content:'score';display:block;color:var(--sub);font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}
.code-block span{display:block;font-size:3rem;font-weight:700;color:var(--primary)}

.price-row{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.price-old{font-size:1.1rem;color:var(--sub);text-decoration:line-through;font-family:'JetBrains Mono',monospace}
.price-new{font-family:'JetBrains Mono',monospace;font-size:2.8rem;font-weight:700;color:var(--primary)}
.price-badge{background:var(--primary);color:#fff;padding:4px 14px;border-radius:8px;font-size:.75rem;font-weight:700}

.timer-box{background:var(--card);border:2px solid var(--border);border-radius:12px;padding:18px;text-align:center;margin-bottom:24px;max-width:300px}
.timer-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);margin-bottom:6px;font-family:'JetBrains Mono',monospace}
.timer-value{font-family:'JetBrains Mono',monospace;font-size:2rem;font-weight:700;color:var(--primary)}

.check-list{list-style:none;display:flex;flex-direction:column;gap:10px}
.check-list li{display:flex;align-items:center;gap:10px;font-size:.95rem;font-family:'JetBrains Mono',monospace;font-size:.85rem}
.check-list li::before{content:'✓';display:flex;align-items:center;justify-content:center;width:22px;height:22px;background:var(--primary);color:#fff;border-radius:6px;font-size:.7rem;flex-shrink:0}

.hero-visual{position:relative}
.img-card{position:relative;border-radius:24px;overflow:hidden;aspect-ratio:4/3;border:1px solid var(--border);box-shadow:0 30px 60px rgba(13,148,136,.12)}
.img-card img{width:100%;height:100%;object-fit:cover;display:block}
.img-placeholder{width:100%;height:100%;background:linear-gradient(135deg,#0D9488 0%,#5EEAD4 100%);display:flex;align-items:center;justify-content:center;font-size:4rem}
.score-badge{position:absolute;top:16px;right:16px;background:var(--surface);padding:12px 20px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.1);font-family:'JetBrains Mono',monospace;text-align:center}
.score-badge .num{font-size:2rem;font-weight:700;color:var(--primary);display:block}
.score-badge .label{font-size:.65rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub)}

.section{padding:80px 0}
.section-title{font-family:'JetBrains Mono',monospace;font-size:clamp(26px,3.5vw,44px);font-weight:700;text-align:center;margin-bottom:12px;letter-spacing:-.02em}
.section-sub{text-align:center;color:var(--sub);font-size:1rem;margin-bottom:48px}

.audit-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.audit-item{background:var(--surface);border-radius:16px;padding:28px;border:1px solid var(--border);transition:all .3s}
.audit-item:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(13,148,136,.1);border-color:var(--primary)}
.audit-item-icon{width:48px;height:48px;background:var(--card);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:16px}
.audit-item h3{font-family:'JetBrains Mono',monospace;font-size:.95rem;font-weight:700;margin-bottom:8px}
.audit-item p{color:var(--sub);font-size:.85rem;line-height:1.6}
.audit-item.bordered{border:2px solid var(--primary);background:linear-gradient(var(--surface),var(--surface)),linear-gradient(var(--primary),var(--secondary));background-origin:border-box;background-clip:padding-box,border-box}

.progress-section{background:var(--surface);border-radius:32px;padding:64px;margin:60px 0;border:1px solid var(--border)}
.progress-section h2{font-family:'JetBrains Mono',monospace;font-size:clamp(24px,3.5vw,40px);font-weight:700;text-align:center;margin-bottom:40px}
.progress-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.progress-item{text-align:center;padding:20px;border-radius:16px;background:var(--card)}
.progress-num{font-family:'JetBrains Mono',monospace;font-size:2.5rem;font-weight:700;color:var(--primary);display:block;margin-bottom:4px}
.progress-label{font-size:.8rem;color:var(--sub)}

.cta-section{background:linear-gradient(135deg,var(--primary) 0%,#14B8A6 100%);border-radius:32px;padding:64px;text-align:center;margin:60px 0;color:#fff;position:relative;overflow:hidden}
.cta-section::before{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.05) 50%,transparent 100%)}
.cta-section h2{font-family:'JetBrains Mono',monospace;font-size:clamp(26px,3.5vw,44px);font-weight:700;margin-bottom:12px;position:relative}
.cta-section p{font-size:1rem;margin-bottom:32px;opacity:.9;position:relative}
.btn-ghost{display:inline-block;background:#fff;color:var(--primary);padding:18px 48px;border-radius:16px;font-size:1rem;font-weight:700;text-decoration:none;transition:all .3s;position:relative;font-family:'JetBrains Mono',monospace}
.btn-ghost:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(0,0,0,.2)}

.timer-sticky{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--text);color:#fff;padding:12px 28px;border-radius:100px;display:flex;align-items:center;gap:12px;font-size:.9rem;font-weight:600;font-family:'JetBrains Mono',monospace;z-index:100;box-shadow:0 8px 30px rgba(0,0,0,.3);opacity:0;transition:opacity .5s;pointer-events:none}
.timer-sticky.show{opacity:1;pointer-events:auto}
.pulse-dot{width:8px;height:8px;background:#dc2626;border-radius:50%;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

.exit-popup{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .4s}
.exit-popup.active{opacity:1;pointer-events:all}
.exit-box{background:var(--surface);border-radius:24px;padding:48px;max-width:460px;width:90%;text-align:center;position:relative;border:2px solid var(--primary)}
.exit-close{position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:var(--sub)}
.exit-icon{font-size:3rem;margin-bottom:16px}
.exit-box h2{font-family:'JetBrains Mono',monospace;font-size:1.8rem;font-weight:700;margin-bottom:8px}
.exit-box p{color:var(--sub);margin-bottom:20px}
.exit-code{display:flex;align-items:center;gap:10px;margin-bottom:20px}
.exit-code input{border:2px dashed var(--primary);background:var(--card);padding:14px 20px;border-radius:12px;font-size:1.1rem;font-weight:700;color:var(--primary);font-family:'JetBrains Mono',monospace;flex:1;text-align:center;cursor:pointer;width:100%}
.exit-timer{font-size:.85rem;color:var(--sub);margin-bottom:20px}
.exit-link{display:inline-block;background:var(--primary);color:#fff;padding:14px 36px;border-radius:12px;font-weight:700;text-decoration:none;font-size:1rem;font-family:'JetBrains Mono',monospace}

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

<div class="grid-bg"></div>
<div class="top-bar">// ANALYZE · IMPROVE · GROW</div>

<div class="container">
<nav style="padding:20px 0;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)">
<div style="font-family:'JetBrains Mono',monospace;font-size:20px;font-weight:700;color:var(--primary)">JSSL//</div>
<a href="<?= $baseUrl ?>" style="color:var(--sub);text-decoration:none;font-size:.85rem;font-weight:500">← Store</a>
</nav>

<section class="hero">
<div class="hero-inner">
<div>
<div class="eyebrow">🔍 Website Audit Kit</div>
<h1 class="hero-title">Find Every<br>Problem On<br><span>Your Site</span></h1>
<p class="hero-sub">A complete audit toolkit with 50+ checkpoints, scoring system, and step-by-step fix guides. Know exactly what's wrong and how to fix it.</p>
<div class="code-block">
<span><?= rand(65, 92) ?>/100</span>
</div>
<div class="price-row">
<span class="price-old">&#x20A6;<?= number_format($productOldRaw) ?></span>
<span class="price-new">&#x20A6;<?= number_format($productPriceRaw) ?></span>
<span class="price-badge">52% OFF</span>
</div>
<div class="timer-box">
<div class="timer-label">// time remaining</div>
<div class="timer-value" id="timer">--:--:--</div>
</div>
<ul class="check-list">
<li>100-Point Audit Scoring</li>
<li>50+ Technical Checklist</li>
<li>Speed Test Report Template</li>
<li>Action Plan Workbook</li>
</ul>
<a href="?step=checkout" style="display:inline-block;background:var(--primary);color:#fff;padding:16px 40px;border-radius:16px;font-weight:700;font-size:1rem;text-decoration:none;margin-top:8px;font-family:'JetBrains Mono',monospace">Start Audit →</a>
</div>
<div class="hero-visual">
<div class="img-card">
<?php if ($productImage): ?>
<img src="/<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($productTitle) ?>">
<?php else: ?>
<div class="img-placeholder">🔍</div>
<?php endif; ?>
<div class="score-badge">
<span class="num"><?= rand(65, 92) ?></span>
<span class="label">/ 100</span>
</div>
</div>
</div>
</div>
</section>
</div>

<section class="section">
<div class="container">
<h2 class="section-title">// Audit Modules</h2>
<p class="section-sub">6 comprehensive audit tools in one kit</p>
<div class="audit-grid">
<div class="audit-item">
<div class="audit-item-icon">📊</div>
<h3>Health Score System</h3>
<p>Get a comprehensive 100-point score for your website with detailed breakdowns by category.</p>
</div>
<div class="audit-item">
<div class="audit-item-icon">🔍</div>
<h3>SEO Analysis</h3>
<p>50-point technical SEO checklist covering meta tags, headings, links, and more.</p>
</div>
<div class="audit-item">
<div class="audit-item-icon">⚡</div>
<h3>Speed Audit</h3>
<p>PageSpeed insights guide with before/after benchmarks and optimization tips.</p>
</div>
<div class="audit-item">
<div class="audit-item-icon">📱</div>
<h3>Mobile Check</h3>
<p>Responsive design audit covering touch targets, viewport, and mobile UX.</p>
</div>
<div class="audit-item">
<div class="audit-item-icon">🎯</div>
<h3>Competitor Benchmark</h3>
<p>Side-by-side comparison with 3 competitors across 20+ metrics.</p>
</div>
<div class="audit-item bordered">
<div class="audit-item-icon">📋</div>
<h3>Fix Action Plan</h3>
<p>Step-by-step workbook with prioritization matrix and implementation timeline.</p>
</div>
</div>
</div>
</section>

<div class="container">
<div class="progress-section">
<h2>// What You Get</h2>
<div class="progress-grid">
<div class="progress-item">
<div class="progress-num">50+</div>
<div class="progress-label">Checkpoints</div>
</div>
<div class="progress-item">
<div class="progress-num">100</div>
<div class="progress-label">Health Score</div>
</div>
<div class="progress-item">
<div class="progress-num">20+</div>
<div class="progress-label">Metrics</div>
</div>
<div class="progress-item">
<div class="progress-num">∞</div>
<div class="progress-label">Uses</div>
</div>
</div>
</div>

<div class="cta-section">
<h2>// Ready to audit your website?</h2>
<p>Find every issue and get a clear roadmap to improvement</p>
<div style="margin-bottom:20px">
<div style="font-size:.85rem;opacity:.8;text-decoration:line-through">&#x20A6;<?= number_format($productOldRaw) ?></div>
<div style="font-size:2.5rem;font-weight:700;font-family:'JetBrains Mono',monospace">&#x20A6;<?= number_format($productPriceRaw) ?></div>
</div>
<a href="?step=checkout" class="btn-ghost">Get Audit Kit →</a>
</div>
</div>

<footer style="padding:40px 0;text-align:center;color:var(--sub);font-size:.8rem;border-top:1px solid var(--border)">
<div class="container">
<p style="font-family:'JetBrains Mono',monospace">© 2024 Joala Store — // ANALYZE · IMPROVE · GROW</p>
</div>
</footer>

</body>
</html>
<?php if ($step === 'checkout'): ?>
<div style="display:none">
<div id="checkoutPage" style="background:var(--bg);min-height:100vh;padding:40px 0">
<div class="container" style="max-width:560px">
<div style="text-align:center;margin-bottom:40px">
<a href="website-audit-kit.php" style="display:inline-flex;align-items:center;gap:8px;color:var(--sub);text-decoration:none;font-size:.9rem">← Back</a>
<h1 style="font-family:'JetBrains Mono',monospace;font-size:2.2rem;font-weight:700;margin-top:16px">// Checkout</h1>
<p style="color:var(--sub)">Complete your order for the <?= htmlspecialchars($productTitle) ?></p>
</div>
<div style="background:var(--surface);border-radius:24px;padding:40px;border:2px solid var(--primary);position:relative">
<div style="text-align:center;margin-bottom:28px">
<div style="font-family:'JetBrains Mono',monospace;font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);margin-bottom:6px">Total</div>
<div style="font-family:'JetBrains Mono',monospace;font-size:2.8rem;font-weight:700;color:var(--primary)">&#x20A6;<?= number_format($finalPrice) ?></div>
<?php if ($finalPrice < $price): ?>
<div style="font-size:.9rem;color:#0d9488;margin-top:4px">Saved &#x20A6;<?= number_format($price - $finalPrice) ?>!</div>
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
<button type="button" id="payBtn" style="width:100%;background:var(--primary);color:#fff;padding:18px;border-radius:16px;font-size:1.1rem;font-weight:700;border:none;cursor:pointer;font-family:'JetBrains Mono',monospace">
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
<span>// Ends: <span id="stickyTimer">--:--:--</span></span>
</div>

<div class="exit-popup" id="exitPopup">
<div class="exit-box">
<button class="exit-close" id="exitClose">×</button>
<div class="exit-icon">🔍</div>
<h2>// Wait! 15% off</h2>
<p>Get the audit kit at a discount</p>
<div class="exit-code">
<input type="text" value="LAUNCH15" id="exitCode" readonly onclick="this.select()">
</div>
<div class="exit-timer" id="exitTimer">Expires in <strong>05:00</strong></div>
<a href="?step=checkout" class="exit-link">Claim Discount</a>
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
var appliedCoupon = '<?= $_SESSION['audit_coupon'] ?? '' ?>';
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