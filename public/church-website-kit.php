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
$productSlug = 'church-organization-website-kit';
$productId = 12;
$funnelId = 19;

$product = Product::where('slug', $productSlug)->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Church Organization Website Kit';
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 18000;
$productOldRaw = $product ? (float)$product->price : 25000;

$pageKey = "church_kit_viewed";
if (!isset($_SESSION[$pageKey])) { $_SESSION[$pageKey] = true; }

$timerOffset = isset($_SESSION['church_kit_timer']) ? (int)$_SESSION['church_kit_timer'] : rand(20000, 40000);
$_SESSION['church_kit_timer'] = $timerOffset;

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
        'order_number' => 'CK-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'funnel_id' => $funnelId,
    ]);
    $ref = 'CK_' . uniqid() . '_' . time();
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
            $_SESSION['church_coupon'] = $couponCode;
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
    <title>Church Organization Website Kit - Complete Digital Solution</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://js.paystack.co/v2/inline.js"></script>
    <style>
        :root {
            --primary: #7C3AED;
            --primary-light: #A78BFA;
            --primary-dark: #5B21B6;
            --accent: #CA8A04;
            --bg-soft: #FAF5FF;
            --bg-section: #F5F3FF;
            --card-bg: #ffffff;
            --text-deep: #1E1B4B;
            --text-muted: #6B7280;
            --border: #DDD6FE;
            --border-light: #EDE9FE;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-soft);
            color: var(--text-deep);
            line-height: 1.6;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 600;
            line-height: 1.2;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-light);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(124, 58, 237, 0.05);
        }
        
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        /* Hero Section */
        .hero {
            padding: 80px 0;
            background: linear-gradient(180deg, var(--bg-soft) 0%, var(--bg-section) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .hero::after {
            content: '✝';
            position: absolute;
            top: 20%;
            left: 5%;
            font-size: 120px;
            opacity: 0.03;
            color: var(--primary);
        }
        
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            color: var(--primary);
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.08);
        }
        
        .hero-badge::before {
            content: '✝';
            font-size: 14px;
        }
        
        .hero h1 {
            font-size: 52px;
            color: var(--text-deep);
            margin-bottom: 20px;
            letter-spacing: -0.02em;
        }
        
        .hero-subtitle {
            font-size: 18px;
            color: var(--text-muted);
            margin-bottom: 32px;
            max-width: 500px;
        }
        
        .hero-price {
            display: flex;
            align-items: baseline;
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .price-sale {
            font-size: 42px;
            font-weight: 700;
            color: var(--primary);
            font-family: 'Inter', sans-serif;
        }
        
        .price-original {
            font-size: 24px;
            color: var(--text-muted);
            text-decoration: line-through;
        }
        
        .hero-cta {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(124, 58, 237, 0.4);
        }
        
        .timer-display {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #FEF3C7;
            border: 1px solid #FCD34D;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            color: #92400E;
        }
        
        .timer-display span {
            font-family: 'Inter', monospace;
            font-size: 18px;
            font-weight: 700;
        }
        
        .hero-image {
            position: relative;
            z-index: 1;
        }
        
        .hero-image img {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(124, 58, 237, 0.15), 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 3px solid var(--card-bg);
        }
        
        .hero-image-placeholder {
            width: 100%;
            aspect-ratio: 4/3;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 100px;
            color: white;
            box-shadow: 0 25px 60px rgba(124, 58, 237, 0.2);
            border: 3px solid var(--card-bg);
        }
        
        /* Features Section */
        .features {
            padding: 80px 0;
            background: var(--card-bg);
            position: relative;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-header h2 {
            font-size: 42px;
            color: var(--text-deep);
            margin-bottom: 16px;
        }
        
        .section-header p {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .feature-card {
            background: var(--bg-soft);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 32px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(124, 58, 237, 0.1);
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
            color: white;
        }
        
        .feature-card h3 {
            font-size: 22px;
            color: var(--text-deep);
            margin-bottom: 12px;
        }
        
        .feature-card p {
            color: var(--text-muted);
            font-size: 15px;
        }
        
        /* Testimonials */
        .testimonials {
            padding: 80px 0;
            background: linear-gradient(180deg, var(--bg-section) 0%, var(--bg-soft) 100%);
            position: relative;
        }
        
        .testimonials::before {
            content: '"';
            position: absolute;
            top: 80px;
            left: 10%;
            font-size: 200px;
            font-family: 'Cormorant Garamond', serif;
            color: var(--primary);
            opacity: 0.05;
            line-height: 1;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .testimonial-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.08);
            position: relative;
        }
        
        .testimonial-card::before {
            content: '✝';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            color: var(--border);
        }
        
        .testimonial-text {
            font-size: 17px;
            color: var(--text-deep);
            font-style: italic;
            margin-bottom: 24px;
            line-height: 1.8;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }
        
        .author-info h4 {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-deep);
        }
        
        .author-info span {
            font-size: 14px;
            color: var(--text-muted);
        }
        
        /* Pricing Section */
        .pricing {
            padding: 80px 0;
            background: var(--card-bg);
            position: relative;
        }
        
        .pricing-card {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(180deg, var(--bg-soft) 0%, var(--card-bg) 100%);
            border: 2px solid var(--primary);
            border-radius: 24px;
            padding: 48px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--accent), var(--primary));
        }
        
        .pricing-badge {
            position: absolute;
            top: 20px;
            right: -30px;
            background: var(--accent);
            color: white;
            padding: 8px 40px;
            transform: rotate(45deg);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .pricing-card h3 {
            font-size: 28px;
            color: var(--text-deep);
            margin-bottom: 8px;
        }
        
        .pricing-subtitle {
            color: var(--text-muted);
            margin-bottom: 32px;
        }
        
        .pricing-amount {
            margin-bottom: 32px;
        }
        
        .pricing-amount .current {
            font-size: 56px;
            font-weight: 700;
            color: var(--primary);
            font-family: 'Inter', sans-serif;
        }
        
        .pricing-amount .original {
            font-size: 24px;
            color: var(--text-muted);
            text-decoration: line-through;
            margin-left: 12px;
        }
        
        .pricing-features {
            list-style: none;
            margin-bottom: 32px;
            text-align: left;
        }
        
        .pricing-features li {
            padding: 12px 0;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-deep);
        }
        
        .pricing-features li::before {
            content: '✓';
            color: var(--primary);
            font-weight: bold;
        }
        
        .pricing-form {
            margin-top: 24px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--card-bg);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }
        
        .btn-pay {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 18px 32px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3);
        }
        
        .btn-pay:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(124, 58, 237, 0.4);
        }
        
        .btn-pay:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .coupon-field {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }
        
        .coupon-field input {
            flex: 1;
        }
        
        .btn-apply {
            padding: 16px 24px;
            background: var(--bg-section);
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-apply:hover {
            background: var(--primary);
            color: white;
        }
        
        .coupon-message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .coupon-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
        
        .coupon-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '✝';
            position: absolute;
            bottom: -50px;
            right: 10%;
            font-size: 200px;
            color: white;
            opacity: 0.05;
        }
        
        .cta-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .cta-content h2 {
            font-size: 42px;
            color: white;
            margin-bottom: 16px;
        }
        
        .cta-content p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 32px;
        }
        
        .btn-cta {
            display: inline-block;
            background: white;
            color: var(--primary);
            padding: 18px 48px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
        }
        
        /* Footer */
        .footer {
            background: var(--text-deep);
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        
        .footer p {
            opacity: 0.7;
            font-size: 14px;
        }
        
        /* Exit Popup */
        .exit-popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(30, 27, 75, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .exit-popup.active {
            opacity: 1;
            visibility: visible;
        }
        
        .popup-content {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 48px;
            max-width: 500px;
            text-align: center;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .exit-popup.active .popup-content {
            transform: scale(1);
        }
        
        .popup-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 40px;
            height: 40px;
            border: none;
            background: var(--bg-section);
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }
        
        .popup-close:hover {
            background: var(--primary);
            color: white;
        }
        
        .popup-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent), #D97706);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
        }
        
        .popup-content h3 {
            font-size: 28px;
            color: var(--text-deep);
            margin-bottom: 16px;
        }
        
        .popup-content p {
            color: var(--text-muted);
            margin-bottom: 24px;
        }
        
        .popup-code {
            display: inline-block;
            background: #FEF3C7;
            border: 2px dashed var(--accent);
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 24px;
            font-weight: 700;
            color: #92400E;
            margin-bottom: 16px;
        }
        
        .popup-timer {
            color: var(--text-muted);
            font-size: 14px;
        }
        
        /* Timer Sticky */
        .timer-sticky {
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: white;
            padding: 16px 32px;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 -5px 20px rgba(124, 58, 237, 0.3);
            transition: bottom 0.3s ease;
            z-index: 99;
        }
        
        .timer-sticky.show {
            bottom: 0;
        }
        
        .timer-sticky span {
            font-family: monospace;
            font-size: 18px;
            font-weight: 700;
        }
        
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
    <header class="header">
        <div class="container header-inner">
            <div class="logo">
                <div class="logo-icon">✝</div>
                <span>ChurchKit</span>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">Complete Digital Solution for Churches</div>
                    <h1>Church Organization Website Kit</h1>
                    <p class="hero-subtitle">Transform your church's digital presence with a comprehensive website kit featuring sermon archives, event calendars, donation portals, and more.</p>
                    <div class="hero-price">
                        <span class="price-sale">₦<?= number_format($salePrice) ?></span>
                        <span class="price-original">₦<?= number_format($originalPrice) ?></span>
                    </div>
                    <div class="hero-cta">
                        <button class="btn-primary" onclick="document.getElementById('pricing').scrollIntoView({behavior: 'smooth'})">Get Started</button>
                        <div class="timer-display">
                            ⏱ Limited: <span id="timer">00:00:00</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <?php if ($productImage): ?>
                        <img src="<?= htmlspecialchars($productImage) ?>" alt="Church Organization Website Kit">
                    <?php else: ?>
                        <div class="hero-image-placeholder">⛪</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2>Everything Your Church Needs</h2>
                <p>A complete suite of tools designed specifically for church management and congregation engagement.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🎙</div>
                    <h3>Sermon Archive System</h3>
                    <p>Organize and display sermons by series, speaker, and date. Allow congregation members to easily access and browse through your message library.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Event Calendar</h3>
                    <p>Keep your community informed with upcoming services, events, and programs. Easy-to-manage calendar with automatic reminders.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💝</div>
                    <h3>Donation Portal</h3>
                    <p>Secure tithing and offering collection with multiple payment options. Track donations and generate reports effortlessly.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🙏</div>
                    <h3>Prayer Request System</h3>
                    <p>Allow congregation members to submit prayer requests. Manage and respond to prayers with ease and discretion.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Staff & Ministry Directory</h3>
                    <p>Showcase your team with beautiful staff bios and ministry department pages. Keep everyone connected and informed.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile-Responsive Design</h3>
                    <p>Perfect viewing experience on all devices. Reach your congregation wherever they are with a fully responsive website.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2>Trusted by Churches</h2>
                <p>See what church leaders are saying about our website kit.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"This kit transformed how we connect with our congregation. Our sermon archive alone has increased engagement by 40%!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">P</div>
                        <div class="author-info">
                            <h4>Pastor Emmanuel</h4>
                            <span>Grace Chapel, Lagos</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"The donation portal made tithing so much easier for our members. We've seen a significant increase in online giving."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">S</div>
                        <div class="author-info">
                            <h4>Sarah Mitchell</h4>
                            <span>Finance Director, Living Faith</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"Beautiful design and so easy to manage. Our volunteer team can update content without any technical knowledge."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">J</div>
                        <div class="author-info">
                            <h4>James Okonkwo</h4>
                            <span>Tech Ministry Lead, The Vineyard</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-header">
                <h2>Special Offer</h2>
                <p>Get complete access to all features at an unbeatable price.</p>
            </div>
            <div class="pricing-card">
                <div class="pricing-badge">Best Value</div>
                <h3>Church Organization Website Kit</h3>
                <p class="pricing-subtitle">One-time payment, lifetime access</p>
                <div class="pricing-amount">
                    <span class="current">₦<?= number_format($salePrice) ?></span>
                    <span class="original">₦<?= number_format($originalPrice) ?></span>
                </div>
                <ul class="pricing-features">
                    <li>Sermon Archive System with unlimited uploads</li>
                    <li>Event Calendar with RSVP functionality</li>
                    <li>Secure Donation Portal with Paystack</li>
                    <li>Prayer Request Management System</li>
                    <li>Staff & Ministry Directory</li>
                    <li>Mobile-Responsive Design</li>
                    <li>Free updates and support</li>
                </ul>
                <div class="pricing-form">
                    <div class="form-group">
                        <input type="email" id="emailInput" class="form-input" placeholder="Enter your email address" required>
                    </div>
                    <div class="coupon-field">
                        <input type="text" id="couponInput" class="form-input" placeholder="Have a coupon?">
                        <button type="button" class="btn-apply" id="applyCoupon">Apply</button>
                    </div>
                    <div id="couponMessage"></div>
                    <button class="btn-pay" id="payBtn">Pay ₦<?= number_format($salePrice) ?></button>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Elevate Your Church?</h2>
                <p>Join hundreds of churches already using our website kit to connect with their congregation.</p>
                <a href="#pricing" class="btn-cta">Get Started Now</a>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> ChurchKit. All rights reserved.</p>
        </div>
    </footer>

    <div class="exit-popup" id="exitPopup">
        <div class="popup-content">
            <button class="popup-close" id="exitClose">×</button>
            <div class="popup-icon">🎁</div>
            <h3>Wait! Don't Go Yet</h3>
            <p>Unlock an exclusive 15% discount on the Church Website Kit!</p>
            <div class="popup-code">LAUNCH15</div>
            <p class="popup-timer" id="exitTimer">Coupon expires in 05:00</p>
            <button class="btn-primary" onclick="document.getElementById('couponInput').value='LAUNCH15';document.getElementById('applyCoupon').click();document.getElementById('exitPopup').classList.remove('active');" style="margin-top: 16px;">Claim My Discount</button>
        </div>
    </div>

    <div class="timer-sticky" id="timerSticky">
        ⏱ Sale ends in: <span id="stickyTimer">00:00:00</span>
    </div>

    <script>
    (function(){
        var timerOffset = <?= $timerOffset ?>;
        var endTime = Date.now() + timerOffset;
        function updateTimers(){
            var now = Date.now();
            var remaining = Math.max(0, endTime - now);
            var h=Math.floor(remaining/3600000), m=Math.floor((remaining%3600000)/60000), s=Math.floor((remaining%60000)/1000);
            var fmt=String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
            document.querySelectorAll('#timer,#timer2,#stickyTimer').forEach(function(el){if(el)el.textContent=fmt});
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
    })();
    </script>

    <script>
    (function(){
        var currentAmount = <?= $salePrice ?>;
        var appliedCoupon = null;
        var originalPrice = <?= $originalPrice ?>;
        
        var emailInput = document.getElementById('emailInput');
        var couponInput = document.getElementById('couponInput');
        var applyBtn = document.getElementById('applyCoupon');
        var couponMessage = document.getElementById('couponMessage');
        var payBtn = document.getElementById('payBtn');
        
        applyBtn.addEventListener('click', function(){
            var code = couponInput.value.trim().toUpperCase();
            if(code === 'LAUNCH15') {
                var discount = originalPrice * 0.15;
                currentAmount = originalPrice - discount;
                appliedCoupon = 'LAUNCH15';
                couponMessage.innerHTML = '<div class="coupon-message coupon-success">✓ Coupon applied! 15% off - Price: ₦' + currentAmount.toLocaleString() + '</div>';
                payBtn.innerHTML = 'Pay ₦' + currentAmount.toLocaleString();
            } else {
                couponMessage.innerHTML = '<div class="coupon-message coupon-error">✗ Invalid coupon code</div>';
            }
        });
        
        payBtn.addEventListener('click', function(){
            var email = emailInput.value.trim();
            if(!email || !email.includes('@')){
                alert('Please enter a valid email');
                return;
            }
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
                if(!data.success) {
                    alert(data.message || 'Payment setup failed');
                    payBtn.disabled = false;
                    payBtn.innerHTML = 'Pay ₦'+currentAmount.toLocaleString();
                    return;
                }
                var paystack = PaystackPop.setup({
                    key: data.paystack_key,
                    email: data.email,
                    amount: data.amount,
                    reference: data.reference,
                    onClose: function(){ 
                        payBtn.disabled = false; 
                        payBtn.innerHTML = 'Pay ₦'+currentAmount.toLocaleString(); 
                    },
                    callback: function(response){ 
                        window.location.href = '<?= $baseUrl ?>/order/success?ref='+response.reference+'&order_id='+data.order_id; 
                    }
                });
                paystack.openIframe();
            })
            .catch(function(err){
                payBtn.disabled = false;
                payBtn.innerHTML = 'Pay ₦'+currentAmount.toLocaleString();
                alert('Payment setup failed. Please try again.');
            });
        });
    })();
    </script>
</body>
</html>