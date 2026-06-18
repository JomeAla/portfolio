<?php
error_reporting(0);
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

use App\Models\Product;

$baseUrl = 'https://joala.com.ng';
$product = Product::where('slug', 'email-sequence-templates-pack')->first();
$productPriceRaw = $product ? (float)($product->sale_price ?? $product->price) : 15000;
$productOldRaw = $product ? (float)$product->price : 35000;
$productTitle = $product ? $product->title : 'Email Sequence Templates Pack';
$productImage = $product && $product->image ? $product->image : '/uploads/products/email-sequence-templates-pack-cover.svg';

$timerOffset = rand(20000, 40000);
$step = 'landing';
$price = $productOldRaw;
$finalPrice = $productPriceRaw;
$email = '';
$couponMsg = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Sequence Templates Pack - Boost Your Email Marketing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&display=swap" rel="stylesheet">
    <script src="https://js.paystack.co/v2/inline.js"></script>
    <style>
        :root {
            --bg: #FDF2F8;
            --card-bg: #ffffff;
            --primary: #DB2777;
            --secondary: #F472B6;
            --accent: #9D174D;
            --text: #831843;
            --sub-text: #6B7280;
            --border: #FBCFE8;
            --light-pink: #FCE7F3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 20px rgba(219, 39, 119, 0.08);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-family: 'Fraunces', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .hero {
            padding: 60px 0 40px;
            text-align: center;
            background: linear-gradient(180deg, var(--bg) 0%, var(--light-pink) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -20%;
            width: 60%;
            height: 200%;
            background: radial-gradient(ellipse, rgba(219, 39, 119, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            right: -10%;
            width: 50%;
            height: 150%;
            background: radial-gradient(ellipse, rgba(244, 114, 182, 0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            color: var(--accent);
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(219, 39, 119, 0.1);
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            line-height: 1.2;
            color: var(--text);
        }
        
        .hero h1 span {
            color: var(--primary);
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--sub-text);
            max-width: 600px;
            margin: 0 auto 40px;
        }
        
        .hero-image-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        
        .hero-image {
            width: 100%;
            aspect-ratio: 4/3;
            border-radius: 24px;
            object-fit: cover;
            box-shadow: 0 20px 60px rgba(219, 39, 119, 0.15), 0 8px 30px rgba(0, 0, 0, 0.08);
            border: 4px solid var(--card-bg);
        }
        
        .hero-image-fallback {
            width: 100%;
            aspect-ratio: 4/3;
            border-radius: 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8rem;
            color: white;
            box-shadow: 0 20px 60px rgba(219, 39, 119, 0.2);
            border: 4px solid var(--card-bg);
        }
        
        .floating-icons {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            overflow: hidden;
        }
        
        .floating-icon {
            position: absolute;
            font-size: 2rem;
            opacity: 0.3;
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-icon:nth-child(1) { top: 10%; left: 5%; animation-delay: 0s; }
        .floating-icon:nth-child(2) { top: 20%; right: 8%; animation-delay: 1s; }
        .floating-icon:nth-child(3) { bottom: 15%; left: 10%; animation-delay: 2s; }
        .floating-icon:nth-child(4) { bottom: 25%; right: 5%; animation-delay: 3s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        .timer-bar {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 20px;
            text-align: center;
            border-radius: 12px;
            margin: 30px auto;
            max-width: 400px;
            font-weight: 600;
            box-shadow: 0 8px 30px rgba(219, 39, 119, 0.25);
        }
        
        .timer-bar span {
            font-family: 'DM Sans', monospace;
            font-size: 1.5rem;
            letter-spacing: 2px;
        }
        
        .features {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
            color: var(--text);
        }
        
        .section-title p {
            color: var(--sub-text);
            font-size: 1.1rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }
        
        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(219, 39, 119, 0.12);
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--light-pink), var(--bg));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
            border: 1px solid var(--border);
        }
        
        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
            color: var(--text);
        }
        
        .feature-card p {
            color: var(--sub-text);
            font-size: 0.95rem;
        }
        
        .feature-count {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
        }
        
        .timeline-section {
            padding: 80px 0;
            background: var(--card-bg);
        }
        
        .timeline {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            padding: 20px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary), var(--secondary), var(--accent));
            border-radius: 4px;
        }
        
        .timeline-item {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            position: relative;
        }
        
        .timeline-marker {
            width: 64px;
            height: 64px;
            background: var(--card-bg);
            border: 4px solid var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
            z-index: 1;
        }
        
        .timeline-content {
            background: var(--bg);
            padding: 20px 24px;
            border-radius: 16px;
            flex: 1;
            border: 1px solid var(--border);
        }
        
        .timeline-content h4 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .timeline-content p {
            color: var(--sub-text);
            font-size: 0.9rem;
        }
        
        .pricing-section {
            padding: 80px 0;
        }
        
        .pricing-card {
            max-width: 500px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(219, 39, 119, 0.15);
            border: 2px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        .pricing-card::before {
            content: 'BEST VALUE';
            position: absolute;
            top: 20px;
            right: -35px;
            background: var(--primary);
            color: white;
            padding: 6px 40px;
            font-size: 0.75rem;
            font-weight: 700;
            transform: rotate(45deg);
        }
        
        .price-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .price-label {
            font-size: 1rem;
            color: var(--sub-text);
            margin-bottom: 10px;
        }
        
        .price-amount {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 10px;
        }
        
        .price-current {
            font-family: 'Fraunces', serif;
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .price-original {
            font-size: 1.5rem;
            color: var(--sub-text);
            text-decoration: line-through;
        }
        
        .price-discount {
            background: var(--light-pink);
            color: var(--accent);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .price-features {
            list-style: none;
            margin: 30px 0;
        }
        
        .price-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }
        
        .price-features li:last-child {
            border-bottom: none;
        }
        
        .check-icon {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .email-input-group {
            margin: 30px 0;
        }
        
        .email-input-group label {
            display: block;
            font-size: 0.9rem;
            color: var(--sub-text);
            margin-bottom: 8px;
        }
        
        .email-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.3s ease;
            background: var(--bg);
        }
        
        .email-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(219, 39, 119, 0.1);
        }
        
        .coupon-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .coupon-input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
        }
        
        .coupon-input:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        .apply-btn {
            padding: 12px 24px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'DM Sans', sans-serif;
        }
        
        .apply-btn:hover {
            background: var(--primary);
        }
        
        .pay-btn {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'DM Sans', sans-serif;
            box-shadow: 0 10px 40px rgba(219, 39, 119, 0.3);
        }
        
        .pay-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px rgba(219, 39, 119, 0.4);
        }
        
        .pay-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .guarantee {
            text-align: center;
            margin-top: 20px;
            color: var(--sub-text);
            font-size: 0.9rem;
        }
        
        .cta-section {
            padding: 80px 0;
            text-align: center;
            background: linear-gradient(180deg, var(--light-pink) 0%, var(--bg) 100%);
        }
        
        .cta-box {
            max-width: 600px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 24px;
            padding: 50px;
            border: 2px solid var(--border);
        }
        
        .cta-box h2 {
            font-size: 2rem;
            margin-bottom: 16px;
            color: var(--text);
        }
        
        .cta-box p {
            color: var(--sub-text);
            margin-bottom: 30px;
        }
        
        footer {
            background: var(--accent);
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        
        footer p {
            opacity: 0.8;
        }
        
        .exit-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
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
        
        .exit-popup-content {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 40px;
            max-width: 450px;
            text-align: center;
            transform: scale(0.9);
            transition: all 0.3s ease;
            border: 3px solid var(--primary);
        }
        
        .exit-popup.active .exit-popup-content {
            transform: scale(1);
        }
        
        .exit-popup-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .exit-popup h3 {
            font-size: 1.8rem;
            margin-bottom: 16px;
            color: var(--text);
        }
        
        .exit-popup p {
            color: var(--sub-text);
            margin-bottom: 20px;
        }
        
        .exit-coupon {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }
        
        .exit-popup .pay-btn {
            padding: 16px;
            font-size: 1.1rem;
        }
        
        .exit-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--sub-text);
            background: none;
            border: none;
        }
        
        .sticky-cta {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: var(--card-bg);
            padding: 15px 20px;
            box-shadow: 0 -5px 30px rgba(0, 0, 0, 0.1);
            display: none;
            align-items: center;
            justify-content: space-between;
            z-index: 99;
            border-top: 1px solid var(--border);
        }
        
        .sticky-cta.show {
            display: flex;
        }
        
        .sticky-price {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sticky-current {
            font-family: 'Fraunces', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .sticky-original {
            color: var(--sub-text);
            text-decoration: line-through;
        }
        
        .sticky-btn {
            padding: 12px 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
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
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">📧</div>
                    EmailPro
                </div>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <span>📬</span> Email Marketing Made Easy
            </div>
            <h1>Supercharge Your Email Marketing with <span>Pro Templates</span></h1>
            <p class="hero-subtitle">Get 29 ready-to-use email sequences that convert visitors into customers, recover abandoned carts, and grow your business on autopilot.</p>
            
            <div class="hero-image-container">
                <div class="floating-icons">
                    <span class="floating-icon">📨</span>
                    <span class="floating-icon">✉️</span>
                    <span class="floating-icon">📬</span>
                    <span class="floating-icon">📮</span>
                </div>
                <?php if ($productImage): ?>
                    <img src="<?= htmlspecialchars($productImage) ?>" alt="Email Sequence Templates Pack" class="hero-image">
                <?php else: ?>
                    <div class="hero-image-fallback">📧</div>
                <?php endif; ?>
            </div>
            
            <div class="timer-bar">
                🔥 Special Offer Ends In: <span id="timer">00:00:00</span>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>What's Included in the Pack?</h2>
                <p>Everything you need to automate your email marketing and boost conversions</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-count">5</span>
                    <div class="feature-icon">👋</div>
                    <h3>Welcome Sequence</h3>
                    <p>A 5-email onboarding sequence that warms up new subscribers, introduces your brand, and drives first purchases.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-count">3</span>
                    <div class="feature-icon">🛒</div>
                    <h3>Cart Abandonment Series</h3>
                    <p>A 3-email recovery sequence designed to bring back lost customers and recover potentially lost revenue.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-count">4</span>
                    <div class="feature-icon">💝</div>
                    <h3>Re-engagement Campaign</h3>
                    <p>Win back inactive subscribers with a strategic series that rekindles interest and boosts list health.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-count">7</span>
                    <div class="feature-icon">🚀</div>
                    <h3>Product Launch Sequence</h3>
                    <p>A 7-email product announcement sequence that builds anticipation, creates buzz, and drives sales.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-count">4</span>
                    <div class="feature-icon">📦</div>
                    <h3>Post-Purchase Follow-up</h3>
                    <p>A 4-email customer nurturing sequence that increases repeat purchases and builds loyal advocates.</p>
                </div>
                
                <div class="feature-card">
                    <span class="feature-count">10</span>
                    <div class="feature-icon">📰</div>
                    <h3>Newsletter Templates</h3>
                    <p>10 reusable newsletter designs for consistent, professional communication with your audience.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="timeline-section">
        <div class="container">
            <div class="section-title">
                <h2>Your Email Sequence Journey</h2>
                <p>See how each sequence fits into your customer lifecycle</p>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker">👋</div>
                    <div class="timeline-content">
                        <h4>Welcome Sequence (Day 0-3)</h4>
                        <p>New subscribers receive 5 warming emails introducing your brand and value proposition.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">🛒</div>
                    <div class="timeline-content">
                        <h4>Cart Abandonment (Day 1-3)</h4>
                        <p>When a cart is abandoned, trigger a 3-email recovery sequence to bring them back.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">🚀</div>
                    <div class="timeline-content">
                        <h4>Product Launch (Day 0-7)</h4>
                        <p>7 emails building hype, revealing features, and driving launch day purchases.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">📦</div>
                    <div class="timeline-content">
                        <h4>Post-Purchase (Day 1-14)</h4>
                        <p>4 follow-up emails confirming order, building anticipation, and encouraging reviews.</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-marker">💝</div>
                    <div class="timeline-content">
                        <h4>Re-engagement (Monthly)</h4>
                        <p>Quarterly win-back campaigns to re-activate dormant subscribers and maintain list health.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($step === 'checkout'): ?>
    <section class="checkout-page" style="background:#FDF2F8;min-height:100vh;padding:40px 0">
        <div class="container" style="max-width:560px">
            <div style="text-align:center;margin-bottom:32px">
                <a href="email-sequence-templates-pack.php" style="display:inline-flex;align-items:center;gap:8px;color:#6B7280;text-decoration:none;font-size:.9rem">← Back</a>
                <h2 style="font-family:'Fraunces',serif;font-size:2rem;font-weight:700;margin-top:16px">Checkout</h2>
                <p style="color:#6B7280">Complete your order for Email Sequence Templates Pack</p>
            </div>
            <div style="background:#fff;border-radius:24px;padding:40px;border:2px solid #FBCFE8;position:relative">
                <div style="text-align:center;margin-bottom:28px">
                    <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:#6B7280;margin-bottom:6px">Total</div>
                    <div style="font-family:'Fraunces',serif;font-size:2.8rem;font-weight:700;color:#DB2777">&#x20A6;<?= number_format($finalPrice) ?></div>
                    <?php if ($finalPrice < $price): ?>
                    <div style="font-size:.9rem;color:#16a34a;margin-top:4px">You saved &#x20A6;<?= number_format($price - $finalPrice) ?>!</div>
                    <?php endif; ?>
                </div>
                <form method="POST">
                    <div style="margin-bottom:18px">
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:6px;color:#831843">Email *</label>
                        <input type="email" id="checkoutEmail" required value="<?= htmlspecialchars($email) ?>" style="width:100%;padding:14px 16px;border:1px solid #FBCFE8;border-radius:12px;font-size:1rem;font-family:inherit">
                    </div>
                    <div style="margin-bottom:18px">
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:6px;color:#831843">Coupon</label>
                        <div style="display:flex;gap:10px">
                            <input type="text" name="coupon_code" id="couponInput" placeholder="Code" style="flex:1;padding:14px 16px;border:1px solid #FBCFE8;border-radius:12px;font-size:.9rem;font-family:inherit">
                            <button type="submit" name="apply_coupon" style="background:#FDF2F8;border:1px solid #FBCFE8;padding:0 20px;border-radius:12px;font-weight:600;cursor:pointer;font-size:.85rem">Apply</button>
                        </div>
                        <?php if ($couponMsg): ?><div style="font-size:.8rem;margin-top:8px"><?= $couponMsg ?></div><?php endif; ?>
                    </div>
                    <button type="button" id="payBtn" style="width:100%;background:#DB2777;color:#fff;padding:18px;border-radius:16px;font-size:1.1rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:10px">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Pay &#x20A6;<?= number_format($finalPrice) ?>
                    </button>
                    <p style="text-align:center;margin-top:12px;font-size:.75rem;color:#6B7280">🔒 Paystack</p>
                </form>
            </div>
        </div>
    </section>
<?php else: ?>

    <section class="pricing-section">
        <div class="container">
            <div class="section-title">
                <h2>Get Started Today</h2>
                <p>Limited time offer - Lock in your discount</p>
            </div>
            
            <div class="pricing-card">
                <div class="price-header">
                    <p class="price-label">Email Sequence Templates Pack</p>
                    <div class="price-amount">
                        <span class="price-current">₦<?php echo number_format($finalPrice); ?></span>
                        <span class="price-original">₦<?php echo number_format($price); ?></span>
                        <span class="price-discount">-<?php echo round((1 - $finalPrice / $price) * 100); ?>%</span>
                    </div>
                </div>
                
                <ul class="price-features">
                    <li><span class="check-icon">✓</span> 29 Professional Email Templates</li>
                    <li><span class="check-icon">✓</span> 6 Complete Email Sequences</li>
                    <li><span class="check-icon">✓</span> Easy-to-Edit Copy & Design</li>
                    <li><span class="check-icon">✓</span> Mobile-Responsive Templates</li>
                    <li><span class="check-icon">✓</span> Instant Download Access</li>
                    <li><span class="check-icon">✓</span> Free Updates Included</li>
                </ul>
                
                <div class="email-input-group">
                    <label for="email">Enter your email to purchase</label>
                    <input type="email" id="email" class="email-input" placeholder="your@email.com" required>
                </div>
                
                <div class="coupon-group">
                    <input type="text" id="coupon" class="coupon-input" placeholder="Have a coupon?">
                    <button type="button" id="applyCoupon" class="apply-btn">Apply</button>
                </div>
                
                <button type="button" id="payBtn" class="pay-btn">Pay ₦<?php echo number_format($finalPrice); ?></button>
                
                <p class="guarantee">🔒 Secure payment via Paystack • 30-day money-back guarantee</p>
            </div>
        </div>
    </section>

<?php endif; ?>

    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <h2>Ready to Transform Your Email Marketing?</h2>
                <p>Join thousands of marketers who've boosted their conversions with our proven email sequences.</p>
                <button type="button" id="ctaPayBtn" class="pay-btn" style="max-width: 300px;">Get Started Now</button>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2026 EmailPro. All rights reserved.</p>
        </div>
    </footer>

    <div class="exit-popup" id="exitPopup">
        <div class="exit-popup-content">
            <button class="exit-close" id="exitClose">×</button>
            <div class="exit-popup-icon">🎁</div>
            <h3>Wait! Don't Miss Out!</h3>
            <p>Before you go, here's an exclusive offer just for you:</p>
            <div class="exit-coupon">LAUNCH15</div>
            <p style="font-size: 0.9rem;">Use this code to get <strong>15% OFF</strong> your purchase!</p>
            <button type="button" class="pay-btn" id="exitPayBtn">Claim 15% Discount</button>
        </div>
    </div>

    <div class="sticky-cta" id="timerSticky">
        <div class="sticky-price">
            <span class="sticky-current">₦<?php echo number_format($finalPrice); ?></span>
            <span class="sticky-original">₦<?php echo number_format($price); ?></span>
            <span id="stickyTimer" style="font-weight: 600; color: var(--primary);"></span>
        </div>
        <button type="button" class="sticky-btn" id="stickyPayBtn">Buy Now</button>
    </div>

    <script>
        (function(){
            var timerOffset = <?= $timerOffset ?>;
            var endTime = Date.now() + timerOffset;
            var currentAmount = <?= $finalPrice ?>;
            var appliedCoupon = null;
            var appliedCoupon = null;
            var isCheckout = false;

            function updateTimers(){
                var now = Date.now();
                var remaining = Math.max(0, endTime - now);
                var h=Math.floor(remaining/3600000), m=Math.floor((remaining%3600000)/60000), s=Math.floor((remaining%60000)/1000);
                var fmt=String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
                var els = document.querySelectorAll('#timer,#stickyTimer');
                els.forEach(function(el){if(el)el.textContent=fmt});
                if(remaining < 300000) document.getElementById('timerSticky').classList.add('show');
            }
            setInterval(updateTimers, 1000);
            updateTimers();

            var popupShown = false;
            function showExitPopup(){
                if(popupShown) return;
                popupShown = true;
                var exitP = document.getElementById('exitPopup');
                if(exitP) exitP.classList.add('active');
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
            var exitClose = document.getElementById('exitClose');
            if(exitClose) exitClose.addEventListener('click', function(){
                var exitP = document.getElementById('exitPopup');
                if(exitP) exitP.classList.remove('active');
                popupShown = true;
            });

            var emailInput = document.getElementById('email') || document.getElementById('checkoutEmail');
            var payBtn = document.getElementById('payBtn');
            var ctaPayBtn = document.getElementById('ctaPayBtn');
            var exitPayBtn = document.getElementById('exitPayBtn');
            var stickyPayBtn = document.getElementById('stickyPayBtn');
            var applyCouponBtn = document.getElementById('applyCoupon');

            var checkoutUrl = '<?= $baseUrl ?>/buy/email-sequence-templates-pack';

            if(payBtn) {
                payBtn.addEventListener('click', function(){
                    window.location.href = checkoutUrl;
                });
            }

            if(ctaPayBtn) {
                ctaPayBtn.addEventListener('click', function(){
                    window.location.href = checkoutUrl;
                });
            }

            if(exitPayBtn) {
                exitPayBtn.addEventListener('click', function(){
                    var exitP = document.getElementById('exitPopup');
                    if(exitP) exitP.classList.remove('active');
                    window.location.href = checkoutUrl;
                });
            }

            if(stickyPayBtn) {
                stickyPayBtn.addEventListener('click', function(){
                    window.location.href = checkoutUrl;
                });
            }

            if(applyCouponBtn) {
                applyCouponBtn.addEventListener('click', function(){
                    window.location.href = checkoutUrl;
                });
            }
        })();
    </script>
</body>
</html>