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
$productSlug = 'school-management-system';
$productId = 13;
$funnelId = 16;

$product = Product::where('slug', $productSlug)->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'School Management System';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 45000;
$productOldRaw = $product ? (float)$product->price : 65000;

$pageKey = "school_mgmt_viewed";
if (!isset($_SESSION[$pageKey])) { $_SESSION[$pageKey] = true; }

$timerOffset = isset($_SESSION['school_mgmt_timer']) ? (int)$_SESSION['school_mgmt_timer'] : rand(20000, 40000);
$_SESSION['school_mgmt_timer'] = $timerOffset;

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
        'order_number' => 'SM-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'funnel_id' => $funnelId,
    ]);
    $ref = 'SM_' . uniqid() . '_' . time();
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
            $_SESSION['school_coupon'] = $couponCode;
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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--bg:#EFF6FF;
--surface:#FFFFFF;
--card:#EEF4FF;
--primary:#1D4ED8;
--primary-dark:#1E40AF;
--secondary:#3B82F6;
--accent:#F59E0B;
--text:#1E3A5F;
--sub:#64748B;
--border:#BFDBFE;
--success:#059669;
}
html{scroll-behavior:smooth}
body{font-family:'Nunito',sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}

.top-bar{background:var(--primary);color:#fff;padding:10px;text-align:center;font-size:.8rem;font-weight:600;letter-spacing:.05em}
.top-bar span{background:var(--accent);padding:2px 12px;border-radius:20px;font-size:.7rem;margin-left:8px}
.container{max-width:1200px;margin:0 auto;padding:0 24px}

.hero{padding:80px 0 60px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-80px;left:30%;width:600px;height:600px;background:radial-gradient(circle,#1D4ED820,transparent 70%);border-radius:50%}
.hero::after{content:'';position:absolute;bottom:-40px;right:-5%;width:350px;height:350px;background:radial-gradient(circle,#F59E0B15,transparent 70%);border-radius:50%}
.hero-inner{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;position:relative;z-index:1}
.eyebrow{display:inline-flex;align-items:center;gap:6px;background:var(--card);color:var(--primary);font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:20px;border:1px solid var(--border)}
.eyebrow-icon{font-size:.9rem}
.hero-title{font-family:'Playfair Display',serif;font-size:clamp(32px,4.5vw,56px);font-weight:700;line-height:1.15;margin-bottom:20px;letter-spacing:-.01em}
.hero-title span{color:var(--primary)}
.hero-sub{font-size:1rem;color:var(--sub);max-width:480px;margin-bottom:28px;line-height:1.8}
.price-row{display:flex;align-items:center;gap:14px;margin-bottom:24px}
.price-old{font-size:1.1rem;color:var(--sub);text-decoration:line-through}
.price-new{font-family:'Playfair Display',serif;font-size:3rem;font-weight:700;color:var(--primary)}
.price-badge{background:var(--accent);color:#fff;padding:6px 16px;border-radius:10px;font-size:.8rem;font-weight:700}
.timer-box{background:var(--card);border:2px solid var(--border);border-radius:16px;padding:20px;text-align:center;margin-bottom:24px;max-width:300px}
.timer-label{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);margin-bottom:8px;font-weight:700}
.timer-value{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--primary)}
.check-list{list-style:none;display:flex;flex-direction:column;gap:10px}
.check-list li{display:flex;align-items:center;gap:10px;font-size:.95rem}
.check-list li::before{content:'✓';display:flex;align-items:center;justify-content:center;width:24px;height:24px;background:var(--primary);color:#fff;border-radius:50%;font-size:.75rem;font-weight:700;flex-shrink:0}
.cta-btn{display:inline-flex;align-items:center;gap:10px;background:var(--primary);color:#fff;padding:16px 40px;border-radius:16px;font-weight:700;font-size:1rem;text-decoration:none;transition:all .3s;box-shadow:0 8px 24px rgba(29,78,216,.25)}
.cta-btn:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(29,78,216,.35)}
.cta-btn svg{transition:transform .3s}
.cta-btn:hover svg{transform:translateX(4px)}

.hero-visual{position:relative}
.img-wrap{position:relative;border-radius:28px;overflow:hidden;aspect-ratio:4/3;border:2px solid var(--border);box-shadow:0 40px 80px rgba(29,78,216,.12)}
.img-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.img-placeholder{width:100%;height:100%;background:linear-gradient(135deg,#1D4ED8 0%,#3B82F6 60%,#60A5FA 100%);display:flex;align-items:center;justify-content:center;font-size:4rem}
.stat-float{position:absolute;bottom:20px;right:20px;background:var(--surface);padding:16px 24px;border-radius:20px;box-shadow:0 12px 40px rgba(0,0,0,.1);text-align:center}
.stat-float .num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--primary);display:block}
.stat-float .label{font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);font-weight:600}

.section{padding:80px 0}
.section-title{font-family:'Playfair Display',serif;font-size:clamp(28px,4vw,48px);font-weight:700;text-align:center;margin-bottom:12px}
.section-sub{text-align:center;color:var(--sub);font-size:1rem;margin-bottom:48px}

.mod-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.mod-card{background:var(--surface);border-radius:20px;padding:32px;border:1px solid var(--border);transition:all .3s}
.mod-card:hover{transform:translateY(-5px);box-shadow:0 20px 48px rgba(29,78,216,.1);border-color:var(--secondary)}
.mod-icon{width:52px;height:52px;background:var(--card);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin-bottom:18px}
.mod-card h3{font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:700;margin-bottom:8px}
.mod-card p{color:var(--sub);font-size:.85rem;line-height:1.7}
.mod-card.featured{border:2px solid var(--primary);background:linear-gradient(var(--surface),var(--surface)),linear-gradient(var(--primary),var(--secondary));background-origin:border-box;background-clip:padding-box,border-box}
.mod-card.featured .mod-icon{background:var(--primary);color:#fff}

.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:48px}
.stat-box{background:var(--surface);border-radius:16px;padding:28px;text-align:center;border:1px solid var(--border)}
.stat-box .icon{font-size:2rem;margin-bottom:8px}
.stat-box .num{font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:700;color:var(--primary);display:block}
.stat-box .label{font-size:.8rem;color:var(--sub);font-weight:600}

.features-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:48px}
.feat-row{display:flex;align-items:flex-start;gap:16px;background:var(--surface);padding:24px;border-radius:16px;border:1px solid var(--border)}
.feat-row-icon{width:44px;height:44px;background:var(--card);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0}
.feat-row h4{font-family:'Playfair Display',serif;font-size:.95rem;font-weight:700;margin-bottom:4px}
.feat-row p{color:var(--sub);font-size:.8rem;line-height:1.6}

.cta-section{background:linear-gradient(135deg,var(--primary) 0%,#1E40AF 100%);border-radius:32px;padding:64px;text-align:center;margin:60px 0;color:#fff;position:relative;overflow:hidden}
.cta-section::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.cta-section h2{font-family:'Playfair Display',serif;font-size:clamp(28px,4vw,48px);font-weight:700;margin-bottom:12px;position:relative}
.cta-section p{font-size:1rem;margin-bottom:32px;opacity:.9;position:relative}
.btn-white{display:inline-block;background:#fff;color:var(--primary);padding:18px 48px;border-radius:16px;font-size:1rem;font-weight:700;text-decoration:none;transition:all .3s;position:relative}
.btn-white:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(0,0,0,.2)}

.timer-sticky{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--primary);color:#fff;padding:12px 28px;border-radius:100px;display:flex;align-items:center;gap:12px;font-size:.9rem;font-weight:600;z-index:100;box-shadow:0 8px 30px rgba(29,78,216,.3);opacity:0;transition:opacity .5s;pointer-events:none}
.timer-sticky.show{opacity:1;pointer-events:auto}
.pulse-dot{width:8px;height:8px;background:#F59E0B;border-radius:50%;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

.exit-popup{position:fixed;inset:0;background:rgba(0,0,0,.6);display:flex;align-items:center;justify-content:center;z-index:1000;opacity:0;pointer-events:none;transition:opacity .4s}
.exit-popup.active{opacity:1;pointer-events:all}
.exit-box{background:var(--surface);border-radius:24px;padding:48px;max-width:460px;width:90%;text-align:center;position:relative;border:2px solid var(--primary)}
.exit-close{position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:var(--sub)}
.exit-icon{font-size:3rem;margin-bottom:16px}
.exit-box h2{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;margin-bottom:8px}
.exit-box p{color:var(--sub);margin-bottom:20px}
.exit-code input{border:2px dashed var(--primary);background:var(--card);padding:14px 20px;border-radius:12px;font-size:1.1rem;font-weight:700;color:var(--primary);font-family:'Playfair Display',serif;width:100%;text-align:center;cursor:pointer;margin-bottom:20px}
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

<div class="top-bar">
🎓 Complete School Management System for Nigerian Schools
<span>30% OFF — Limited Time</span>
</div>

<div class="container">
<nav style="padding:20px 0;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)">
<div style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700">Joala<span style="color:var(--primary)">.</span> Education</div>
<a href="<?= $baseUrl ?>" style="color:var(--sub);text-decoration:none;font-size:.9rem;font-weight:500">← Store</a>
</nav>

<section class="hero">
<div class="hero-inner">
<div>
<div class="eyebrow"><span class="eyebrow-icon">🎓</span> School Management System</div>
<h1 class="hero-title">Run Your School<br><span>Smarter, Faster</span></h1>
<p class="hero-sub">A complete digital management system for Nigerian schools. Manage students, staff, results, fees, and communications — all from one powerful platform.</p>
<div class="price-row">
<span class="price-old">&#x20A6;<?= number_format($productOldRaw) ?></span>
<span class="price-new">&#x20A6;<?= number_format($productPriceRaw) ?></span>
<span class="price-badge">30% OFF</span>
</div>
<div class="timer-box">
<div class="timer-label">⏰ Offer expires in</div>
<div class="timer-value" id="timer">--:--:--</div>
</div>
<ul class="check-list">
<li>Student Information Management</li>
<li>Result & Grading System</li>
<li>Fee Collection & Tracking</li>
<li>Staff & Payroll Module</li>
</ul>
<a href="?step=checkout" class="cta-btn">
Get School Management System
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
</a>
</div>
<div class="hero-visual">
<div class="img-wrap">
<?php if ($productImage): ?>
<img src="/<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($productTitle) ?>">
<?php else: ?>
<div class="img-placeholder">🎓</div>
<?php endif; ?>
<div class="stat-float">
<div class="num">100%</div>
<div class="label">Digital</div>
</div>
</div>
</div>
</div>
</section>
</div>

<section class="section">
<div class="container">
<h2 class="section-title">Everything Your School Needs</h2>
<p class="section-sub">6 powerful modules to manage your entire school operation</p>

<div class="stats-row">
<div class="stat-box">
<div class="icon">👨‍🎓</div>
<div class="num">∞</div>
<div class="label">Students</div>
</div>
<div class="stat-box">
<div class="icon">📊</div>
<div class="num">100+</div>
<div class="label">Reports</div>
</div>
<div class="stat-box">
<div class="icon">💰</div>
<div class="num">100%</div>
<div class="label">Fees Tracked</div>
</div>
<div class="stat-box">
<div class="icon">📱</div>
<div class="num">24/7</div>
<div class="label">Access</div>
</div>
</div>

<div class="mod-grid">
<div class="mod-card">
<div class="mod-icon">👨‍🎓</div>
<h3>Student Records</h3>
<p>Complete student database with enrollment, attendance tracking, medical info, and guardian details.</p>
</div>
<div class="mod-card">
<div class="mod-icon">📝</div>
<h3>Result & Grading</h3>
<p>Automated grading system with WAEC-aligned grading scales, result sheets, and report card generation.</p>
</div>
<div class="mod-card">
<div class="mod-icon">💵</div>
<h3>Fee Management</h3>
<p>Flexible fee collection with installment plans, reminders, and collection reports for Nigerian schools.</p>
</div>
<div class="mod-card">
<div class="mod-icon">👩‍🏫</div>
<h3>Staff Directory</h3>
<p>Teacher and staff management with roles, subjects, class assignments, and performance tracking.</p>
</div>
<div class="mod-card">
<div class="mod-icon">📅</div>
<h3>Class Scheduling</h3>
<p>Timetable builder with subject allocation, room assignment, and substitution management.</p>
</div>
<div class="mod-card featured">
<div class="mod-icon">📊</div>
<h3>Analytics Dashboard</h3>
<p>Real-time school analytics — enrollment trends, fee collection rates, performance metrics.</p>
</div>
</div>
</div>
</section>

<div class="container">
<div class="features-grid">
<div class="feat-row">
<div class="feat-row-icon">🎯</div>
<div>
<h4>WAEC-Aligned Grading</h4>
<p>Pre-configured grading systems that match WAEC and NECO standards</p>
</div>
</div>
<div class="feat-row">
<div class="feat-row-icon">📱</div>
<div>
<h4>Parent Portal</h4>
<p>Parents can view results, fees, and announcements online</p>
</div>
</div>
<div class="feat-row">
<div class="feat-row-icon">🖨️</div>
<div>
<h4>Report Card Generator</h4>
<p>Auto-generate formatted report cards with comments and rankings</p>
</div>
</div>
<div class="feat-row">
<div class="feat-row-icon">🔔</div>
<div>
<h4>SMS Notifications</h4>
<p>Send fee reminders, class updates, and announcements via SMS</p>
</div>
</div>
</div>

<div class="cta-section">
<h2>Transform Your School Administration</h2>
<p>Replace spreadsheets and paper files with a modern digital system</p>
<div style="margin-bottom:20px">
<div style="font-size:.85rem;opacity:.8;text-decoration:line-through">&#x20A6;<?= number_format($productOldRaw) ?></div>
<div style="font-size:2.5rem;font-weight:700;font-family:'Playfair Display',serif">&#x20A6;<?= number_format($productPriceRaw) ?></div>
</div>
<a href="?step=checkout" class="btn-white">Get Started Now →</a>
</div>
</div>

<footer style="padding:40px 0;text-align:center;color:var(--sub);font-size:.8rem;border-top:1px solid var(--border)">
<div class="container">
<p>© 2024 Joala Store — Education Technology Solutions 🎓</p>
</div>
</footer>

</body>
</html>
<?php if ($step === 'checkout'): ?>
<div style="display:none">
<div id="checkoutPage" style="background:var(--bg);min-height:100vh;padding:40px 0">
<div class="container" style="max-width:560px">
<div style="text-align:center;margin-bottom:40px">
<a href="school-management-system.php" style="display:inline-flex;align-items:center;gap:8px;color:var(--sub);text-decoration:none;font-size:.9rem">← Back</a>
<h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:700;margin-top:16px">Checkout</h1>
<p style="color:var(--sub)">Complete your order for <?= htmlspecialchars($productTitle) ?></p>
</div>
<div style="background:var(--surface);border-radius:24px;padding:40px;border:2px solid var(--primary);position:relative">
<div style="text-align:center;margin-bottom:28px">
<div style="font-family:'Playfair Display',serif;font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--sub);margin-bottom:6px">Total</div>
<div style="font-family:'Playfair Display',serif;font-size:2.8rem;font-weight:700;color:var(--primary)">&#x20A6;<?= number_format($finalPrice) ?></div>
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
<button type="button" id="payBtn" style="width:100%;background:var(--primary);color:#fff;padding:18px;border-radius:16px;font-size:1.1rem;font-weight:700;border:none;cursor:pointer;font-family:'Nunito',sans-serif;display:flex;align-items:center;justify-content:center;gap:10px">
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
<div class="pulse-dot"></div>
<span>⏰ Ends: <span id="stickyTimer">--:--:--</span></span>
</div>

<div class="exit-popup" id="exitPopup">
<div class="exit-box">
<button class="exit-close" id="exitClose">×</button>
<div class="exit-icon">🎓</div>
<h2>Wait! 15% off for schools</h2>
<p>Use this code to save on your school management system</p>
<div style="margin-bottom:20px">
<input type="text" value="LAUNCH15" id="exitCode" readonly onclick="this.select()" style="width:100%">
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
var appliedCoupon = '<?= $_SESSION['school_coupon'] ?? '' ?>';
var emailInput = document.getElementById('checkoutEmail');
var payBtn = document.getElementById('payBtn');
payBtn.addEventListener('click', function(){
var email = emailInput.value.trim();
if(!email || !email.includes('@')){alert('Enter valid email');return;}
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
alert('Payment failed. Try again.');
});
});
<?php endif; ?>
})();
</script>
</body>
</html>