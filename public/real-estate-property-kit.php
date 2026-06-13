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
        'source' => 'real_estate_sales_page',
    ]);

    $leadData = array_filter([
        'name' => $nameInput ?: null,
        'phone' => $phoneInput ?: null,
        'utm_source' => $utmData['utm_source'] ?: null,
        'utm_medium' => $utmData['utm_medium'] ?: null,
        'utm_campaign' => $utmData['utm_campaign'] ?: null,
    ], fn($v) => $v !== null);
    if (!empty($leadData)) $lead->update($leadData);

    $funnel = Funnel::find(14);
    if ($funnel) {
        $firstStage = $funnel->stages()->orderBy('order')->first();
        $existingFL = FunnelLead::where('funnel_id', 14)->where('email', $emailInput)->first();
        $newScore = $existingFL ? ($existingFL->score ?? 0) + 10 : 10;

        FunnelLead::updateOrCreate(
            ['funnel_id' => 14, 'email' => $emailInput],
            [
                'lead_id' => $lead->id,
                'stage_id' => $firstStage?->id,
                'entered_at' => $existingFL ? $existingFL->entered_at : now(),
                'last_activity' => now(),
                'source' => 'real_estate_sales_page',
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
                        'body' => str_replace(['{{name}}', '{{email}}', '{{product}}'], [$nameInput, $emailInput, 'Real Estate Property Kit'], $step->body ?? ''),
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

    $product = Product::where('slug', 'real-estate-property-kit')->where('is_active', true)->first();
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
                // min order not met, skip discount silently
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
        'source' => 'real_estate_sales_page',
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
            DB::table('funnel_leads')->where('funnel_id', 14)->where('email', $order->customer_email)
                ->update(['converted' => 1, 'converted_at' => now()]);
        }
    }

    header('Location: /order/success?reference=' . urlencode($_GET['reference']));
    exit;
}

$product = Product::where('slug', 'real-estate-property-kit')->first();
$productImage = $product && $product->image ? $product->image : '';
$productTitle = $product ? $product->title : 'Real Estate Property Kit';
$productPrice = '35,000';
$productOldPrice = '50,000';
$productPriceRaw = 35000;
$productOldRaw = 50000;
$savings = number_format($productOldRaw - $productPriceRaw, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productTitle); ?> - Professional Property Websites | Joala Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #F1F5F9;
            --card: #FFFFFF;
            --text: #0F172A;
            --accent: #059669;
            --accent-light: #10B981;
            --secondary: #475569;
            --wood: #92400E;
            --wood-light: #B45309;
            --border: #E2E8F0;
            --muted: #94A3B8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Work Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
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

        .btn {
            display: inline-block;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            font-family: 'Work Sans', sans-serif;
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(5, 150, 105, 0.3);
        }

        .btn-full {
            width: 100%;
            text-align: center;
        }

        .section {
            padding: 80px 0;
        }

        .hero {
            background: linear-gradient(135deg, var(--bg) 0%, #E2E8F0 100%);
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(5, 150, 105, 0.08) 0%, transparent 50%);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-content {
            max-width: 600px;
        }

        .eyebrow {
            display: inline-block;
            background: rgba(5, 150, 105, 0.1);
            color: var(--accent);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .hero h1 {
            font-size: 56px;
            color: var(--text);
            margin-bottom: 24px;
            letter-spacing: -1px;
        }

        .hero-sub {
            font-size: 20px;
            color: var(--secondary);
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .price-display {
            display: flex;
            align-items: baseline;
            gap: 16px;
            margin-bottom: 24px;
        }

        .price-current {
            font-size: 42px;
            font-weight: 700;
            color: var(--accent);
            font-family: 'Cormorant Garamond', serif;
        }

        .price-old {
            font-size: 24px;
            color: var(--muted);
            text-decoration: line-through;
        }

        .price-savings {
            background: var(--wood);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .timer-box {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: var(--card);
            padding: 12px 20px;
            border-radius: 8px;
            border: 1px solid var(--border);
            margin-top: 20px;
        }

        .timer-icon {
            font-size: 20px;
        }

        .timer-text {
            font-size: 14px;
            color: var(--secondary);
        }

        .timer-value {
            font-weight: 700;
            color: var(--text);
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
        }

        .hero-visual {
            position: relative;
        }

        .property-svg {
            width: 100%;
            max-width: 500px;
            height: auto;
        }

        .stats-row {
            display: flex;
            gap: 40px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid var(--border);
        }

        .stat-item {
            text-align: left;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            font-family: 'Cormorant Garamond', serif;
        }

        .stat-label {
            font-size: 14px;
            color: var(--secondary);
        }

        .features {
            background: var(--card);
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 44px;
            color: var(--text);
            margin-bottom: 16px;
        }

        .section-title p {
            font-size: 18px;
            color: var(--secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .feature-card {
            background: var(--bg);
            padding: 40px 30px;
            border-radius: 12px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border-color: var(--accent);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .feature-card h3 {
            font-size: 22px;
            color: var(--text);
            margin-bottom: 12px;
        }

        .feature-card p {
            font-size: 15px;
            color: var(--secondary);
            line-height: 1.6;
        }

        .pricing-section {
            background: linear-gradient(135deg, var(--bg) 0%, #E2E8F0 100%);
            padding: 80px 0;
        }

        .pricing-card {
            background: var(--card);
            max-width: 600px;
            margin: 0 auto;
            padding: 50px;
            border-radius: 16px;
            text-align: center;
            border: 2px solid var(--accent);
            box-shadow: 0 20px 60px rgba(5, 150, 105, 0.15);
        }

        .pricing-title {
            font-size: 32px;
            color: var(--text);
            margin-bottom: 10px;
        }

        .pricing-subtitle {
            font-size: 16px;
            color: var(--secondary);
            margin-bottom: 30px;
        }

        .pricing-price {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .pricing-current {
            font-size: 56px;
            font-weight: 700;
            color: var(--accent);
            font-family: 'Cormorant Garamond', serif;
        }

        .pricing-old {
            font-size: 28px;
            color: var(--muted);
            text-decoration: line-through;
        }

        .pricing-features {
            text-align: left;
            margin: 30px 0;
            padding: 20px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .pricing-features li {
            list-style: none;
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            color: var(--secondary);
        }

        .pricing-features li::before {
            content: '✓';
            color: var(--accent);
            font-weight: bold;
        }

        .guarantee {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            font-size: 14px;
            color: var(--secondary);
        }

        .final-cta {
            background: var(--text);
            padding: 80px 0;
        }

        .final-cta h2 {
            font-size: 48px;
            color: white;
            margin-bottom: 20px;
            text-align: center;
        }

        .final-cta p {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            margin-bottom: 40px;
        }

        .checkout-section {
            background: var(--card);
            padding: 80px 0;
            min-height: 100vh;
        }

        .checkout-container {
            max-width: 500px;
            margin: 0 auto;
        }

        .checkout-card {
            background: var(--bg);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid var(--border);
        }

        .checkout-title {
            font-size: 32px;
            color: var(--text);
            text-align: center;
            margin-bottom: 30px;
        }

        .checkout-summary {
            background: var(--card);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .checkout-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 16px;
        }

        .checkout-summary-row.total {
            border-top: 2px solid var(--accent);
            margin-top: 10px;
            padding-top: 15px;
            font-weight: 700;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Work Sans', sans-serif;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .coupon-row {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .coupon-row input {
            flex: 1;
        }

        .coupon-row button {
            padding: 14px 20px;
            background: var(--secondary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .checkout-btn {
            width: 100%;
            padding: 18px;
            font-size: 18px;
        }

        .error-msg {
            background: #FEF2F2;
            color: #DC2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-msg {
            background: #F0FDF4;
            color: var(--accent);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: var(--accent);
        }

        .exit-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .exit-popup.active {
            display: flex;
        }

        .exit-popup-content {
            background: var(--card);
            max-width: 450px;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            position: relative;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .exit-popup h3 {
            font-size: 28px;
            color: var(--text);
            margin-bottom: 15px;
        }

        .exit-popup p {
            font-size: 16px;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .exit-popup .coupon-code {
            background: var(--bg);
            padding: 15px 25px;
            border-radius: 8px;
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: 2px;
            margin-bottom: 20px;
            display: inline-block;
        }

        .exit-popup-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--muted);
        }

        .hero-timer,.pricing-timer,.final-cta-timer{display:flex;align-items:center;gap:12px;padding:14px 24px;background:rgba(255,77,0,0.08);border:1px solid rgba(255,77,0,0.2);border-radius:12px;width:fit-content}
        .hero-timer span,.pricing-timer span,.final-cta-timer span{color:var(--secondary);font-size:14px;font-weight:500}
        .hero-timer strong,.pricing-timer strong,.final-cta-timer strong{color:var(--accent);font-weight:800;font-size:17px;letter-spacing:0.05em}
        .summary-image{width:80px;height:80px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-light));display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden}
        .summary-image img{width:100%;height:100%;object-fit:cover}
        .summary-timer{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:24px;padding:12px;background:rgba(255,77,0,0.08);border-radius:8px;color:var(--secondary);font-weight:500}
        .summary-timer strong{color:var(--accent);font-weight:700}

        footer {
            background: var(--text);
            padding: 30px 0;
            text-align: center;
        }

        footer p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        @media(max-width:1024px){.hero-grid,.checkout-grid{grid-template-columns:1fr;gap:40px}.hero-right{order:-1}.hero-sub{margin:0 auto 32px}.price-block{margin:0 auto 28px}.stats-row{justify-content:center;gap:24px}.features-grid{grid-template-columns:1fr}.footer-inner{flex-direction:column;gap:16px;text-align:center}.checkout-grid{padding:0 16px}.order-summary{position:static;margin-top:32px}.checkout-left,.checkout-right{width:100%;padding:0}}
    @media(max-width:768px){.hero-grid{grid-template-columns:1fr;gap:24px}.hero{padding:100px 16px 40px}.hero-title{font-size:clamp(1.8rem,6vw,2.5rem)}.hero-sub{font-size:.9rem}.nav{padding:8px 16px;top:8px}.nav-brand span{display:none}.price-block{flex-wrap:wrap;padding:12px 16px;justify-content:center}.price-current{font-size:1.6rem}.cta-btn{padding:14px 24px;font-size:.9rem;width:100%;justify-content:center}.countdown-bar{max-width:100%}.timer-value{font-size:1.5rem}.stats-row{grid-template-columns:repeat(2,1fr);gap:12px}.stat-item{padding:16px 8px}.features-grid{grid-template-columns:1fr;gap:16px}.section{padding:60px 16px}.section-title{font-size:clamp(1.5rem,5vw,2rem)}.footer{padding:40px 16px 30px}.pricing-card{padding:32px 20px}.pricing-price .amount{font-size:2.2rem}.pricing-features li{font-size:.85rem}.cta-section{padding:60px 16px}.checkout-page{padding:20px 16px}.checkout-form{padding:24px 16px}.field-group input{padding:12px 14px;font-size:.9rem}.pay-btn{padding:16px;font-size:.95rem;width:100%}.order-summary-box{padding:20px 16px}.timer-sticky{padding:10px 16px;font-size:.8rem;width:calc(100% - 32px);left:16px;transform:none;bottom:12px}.exit-popup-box{padding:32px 20px;margin:16px}.exit-popup-box h2{font-size:1.4rem}.exit-code-wrap input{font-size:1rem;padding:12px}.exit-link{padding:14px 24px;font-size:.9rem}}
    @media(max-width:480px){.hero{padding:90px 12px 32px}.hero-title{font-size:1.6rem;letter-spacing:-.02em}.eyebrow{padding:4px 12px;font-size:.65rem}.price-new{font-size:2rem}.timer-box{padding:14px}.timer-value{font-size:1.3rem}.feature-card,.pricing-card{padding:20px}.feature-card h3{font-size:.95rem}.btn-primary,.cta-btn{font-size:.9rem;padding:12px 20px}.strip-bar{padding:8px 12px;font-size:.7rem}.nav{top:0;border-radius:0}.section{padding:48px 12px}.section-title{font-size:1.4rem}.cta-section{padding:40px 12px;border-radius:16px}.cta-section h2{font-size:1.5rem}.pricing-card{border-radius:16px}.stats-row{grid-template-columns:1fr 1fr}}
    </style>
</head>
<body>
    <?php if ($step === 'checkout'): ?>
    <section class="checkout-section">
        <div class="container">
            <div class="checkout-container">
                <a href="?step=landing" class="back-link">← Back to product page</a>
                <div class="checkout-card">
                    <h2 class="checkout-title">Complete Your Order</h2>
                    
                    <div id="checkout-error" class="error-msg" style="display: none;"></div>
                    <div id="checkout-success" class="success-msg" style="display: none;"></div>

                    <div class="checkout-summary">
                        <div class="checkout-summary-row">
                            <span>Product</span>
                            <span>Real Estate Property Kit</span>
                        </div>
                        <div class="checkout-summary-row">
                            <span>Original Price</span>
                            <span style="text-decoration: line-through; color: var(--muted);">₦50,000</span>
                        </div>
                        <div class="checkout-summary-row">
                            <span>Discount</span>
                            <span style="color: var(--accent);">-₦15,000</span>
                        </div>
                        <div class="checkout-summary-row total">
                            <span>Total</span>
                            <span>₦35,000</span>
                        </div>
                    </div>

                    <form id="checkout-form">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" required placeholder="08012345678">
                        </div>
                        <div class="form-group">
                            <label>Coupon Code (Optional)</label>
                            <div class="coupon-row">
                                <input type="text" name="coupon_code" placeholder="Enter coupon">
                                <button type="button" id="apply-coupon">Apply</button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary checkout-btn">
                            Pay ₦35,000 &rarr;
                        </button>
                    </form>

                    <div class="guarantee" style="margin-top: 20px;">
                        <span>🔒</span>
                        <span>Secure payment via Paystack</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php else: ?>

    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <span class="eyebrow">For Property Agents & Developers</span>
                    <h1>Close More Property Deals With a Professional Online Presence</h1>
                    <p class="hero-sub">Property listings, virtual tours, lead capture forms, agent profiles & inquiry management — built for Nigerian real estate</p>
                    
                    <div class="price-display">
                        <span class="price-current">₦35,000</span>
                        <span class="price-old">₦50,000</span>
                        <span class="price-savings">Save ₦15,000</span>
                    </div>

                    <a href="?step=checkout" class="btn btn-primary">Get Started Now →</a>

                    <div class="timer-box">
                        <span class="timer-icon">⏱</span>
                        <span class="timer-text">Offer ends in:</span>
                        <span class="timer-value" id="hero-timer">59:59</span>
                    </div>

                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-number">Property Listings</div>
                            <div class="stat-label">Unlimited properties</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">Virtual Tour Ready</div>
                            <div class="stat-label">360° integration</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">Lead Capture</div>
                            <div class="stat-label">Built-in forms</div>
                        </div>
                    </div>
                </div>
                <div class="hero-visual">
                    <svg class="property-svg" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="50" y="100" width="400" height="250" rx="8" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="2"/>
                        <rect x="50" y="100" width="400" height="120" rx="8" fill="#059669"/>
                        <rect x="70" y="130" width="80" height="60" rx="4" fill="#0F172A" opacity="0.3"/>
                        <rect x="160" y="130" width="80" height="60" rx="4" fill="#0F172A" opacity="0.3"/>
                        <rect x="250" y="130" width="80" height="60" rx="4" fill="#0F172A" opacity="0.3"/>
                        <rect x="340" y="130" width="80" height="60" rx="4" fill="#0F172A" opacity="0.3"/>
                        <rect x="70" y="210" width="80" height="60" rx="4" fill="#0F172A" opacity="0.2"/>
                        <circle cx="110" cy="240" r="8" fill="#059669"/>
                        <rect x="160" y="210" width="80" height="60" rx="4" fill="#0F172A" opacity="0.2"/>
                        <circle cx="200" cy="240" r="8" fill="#059669"/>
                        <rect x="250" y="210" width="80" height="60" rx="4" fill="#0F172A" opacity="0.2"/>
                        <circle cx="290" cy="240" r="8" fill="#059669"/>
                        <rect x="340" y="210" width="80" height="60" rx="4" fill="#0F172A" opacity="0.2"/>
                        <circle cx="380" cy="240" r="8" fill="#059669"/>
                        <rect x="150" y="320" width="200" height="40" rx="4" fill="#059669"/>
                        <text x="250" y="345" font-family="Work Sans" font-size="14" fill="white" text-anchor="middle">Virtual Tour Ready</text>
                        <circle cx="420" cy="80" r="30" fill="#059669" opacity="0.2"/>
                        <circle cx="420" cy="80" r="20" fill="#059669" opacity="0.3"/>
                        <path d="M410 75 L415 85 L425 70" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Everything You Need</h2>
                <p>Build a professional real estate website that converts visitors into clients</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🏠</div>
                    <h3>Property Listing Pages</h3>
                    <p>Showcase properties with detailed descriptions, high-quality images, specifications, and location maps.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Virtual Tour Integration</h3>
                    <p>Embed 360° virtual tours and video walkthroughs directly on property pages for immersive viewing.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👤</div>
                    <h3>Agent Profile Pages</h3>
                    <p>Create professional profiles for each agent with photos, contact info, certifications, and listed properties.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3>Lead Capture & Management</h3>
                    <p>Built-in inquiry forms capture visitor details automatically and organize leads in your dashboard.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Inquiry Management System</h3>
                    <p>Track, respond, and manage all property inquiries from one centralized system with email notifications.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile Responsive Design</h3>
                    <p>Beautiful on any device — desktops, tablets, and smartphones — so you never miss a potential client.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing-section">
        <div class="container">
            <div class="section-title">
                <h2>Simple, One-Time Pricing</h2>
                <p>No monthly fees. No hidden costs. Pay once, own it forever.</p>
            </div>
            <div class="pricing-card">
                <h3 class="pricing-title">Real Estate Property Kit</h3>
                <p class="pricing-subtitle">Complete website solution for property professionals</p>
                
                <div class="pricing-price">
                    <span class="pricing-current">₦35,000</span>
                    <span class="pricing-old">₦50,000</span>
                </div>

                <ul class="pricing-features">
                    <li>Full Website with Property Listings</li>
                    <li>Virtual Tour Integration Support</li>
                    <li>Agent Profile Pages</li>
                    <li>Lead Capture Forms</li>
                    <li>Inquiry Management Dashboard</li>
                    <li>Mobile Responsive Design</li>
                    <li>Free Installation Support</li>
                    <li>30-Day Guarantee</li>
                </ul>

                <a href="?step=checkout" class="btn btn-primary btn-full">Get Started Now →</a>

                <div class="timer-box" style="margin-top: 20px; justify-content: center;">
                    <span class="timer-icon">⏱</span>
                    <span class="timer-text">Offer ends in:</span>
                    <span class="timer-value" id="pricing-timer">59:59</span>
                </div>

                <div class="guarantee">
                    <span>🛡️</span>
                    <span>30-Day Money-Back Guarantee</span>
                </div>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="container">
            <h2>Ready to Grow Your Real Estate Business?</h2>
            <p>Get your professional property website today and start closing more deals.</p>
            <a href="?step=checkout" class="btn btn-primary" style="background: white; color: var(--text);">Get Started Now →</a>
            <div class="timer-box" style="margin-top: 30px; justify-content: center; background: rgba(255,255,255,0.1);">
                <span class="timer-icon" style="color: white;">⏱</span>
                <span class="timer-text" style="color: rgba(255,255,255,0.8);">Offer ends in:</span>
                <span class="timer-value" style="color: white;" id="final-timer">59:59</span>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <div class="exit-popup" id="exit-popup">
        <div class="exit-popup-content">
            <button class="exit-popup-close" id="close-exit">&times;</button>
            <h3>Wait! Don't Miss Out</h3>
            <p>Get <strong>15% OFF</strong> your order with this exclusive coupon:</p>
            <div class="coupon-code">LAUNCH15</div>
            <a href="?step=checkout" class="btn btn-primary">Claim My Discount →</a>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 Joala Digital. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://js.paystack.co/v2/inline.js"></script>
    <script>
    (function() {
        function getStoredTime() {
            const stored = localStorage.getItem('re_timer_end');
            return stored ? parseInt(stored) : null;
        }

        function setStoredTime(endTime) {
            localStorage.setItem('re_timer_end', endTime.toString());
        }

        function initTimer(elementId) {
            let endTime = getStoredTime();
            const now = Date.now();
            
            if (!endTime || endTime <= now) {
                endTime = now + 3600000;
                setStoredTime(endTime);
            }

            const el = document.getElementById(elementId);
            if (!el) return;

            function update() {
                const remaining = endTime - Date.now();
                if (remaining <= 0) {
                    endTime = Date.now() + 3600000;
                    setStoredTime(endTime);
                    update();
                    return;
                }

                const hours = Math.floor(remaining / (1000 * 60 * 60));
                const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

                el.textContent = 
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');
            }

            update();
            setInterval(update, 1000);
        }

        ['hero-timer', 'pricing-timer', 'final-timer'].forEach(initTimer);

        const exitPopup = document.getElementById('exit-popup');
        const closeExit = document.getElementById('close-exit');
        
        if (exitPopup && closeExit) {
            let shown = false;
            
            document.addEventListener('mouseleave', function(e) {
                if (e.clientY <= 0 && !shown) {
                    shown = true;
                    exitPopup.classList.add('active');
                }
            });

            closeExit.addEventListener('click', function() {
                exitPopup.classList.remove('active');
            });

            exitPopup.addEventListener('click', function(e) {
                if (e.target === exitPopup) {
                    exitPopup.classList.remove('active');
                }
            });
        }

        const checkoutForm = document.getElementById('checkout-form');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(checkoutForm);
                formData.append('action', 'init_payment');

                const submitBtn = checkoutForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Processing...';
                submitBtn.disabled = true;

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('checkout-error').textContent = data.error;
                        document.getElementById('checkout-error').style.display = 'block';
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                        return;
                    }

                    const paystack = PaystackPop.setup({
                        key: data.paystack_key,
                        email: data.email,
                        amount: data.amount,
                        ref: data.reference,
                        callback: function(response) {
                            window.location.href = '?reference=' + response.reference + '&trxref=' + response.trxref;
                        },
                        onClose: function() {
                            submitBtn.textContent = originalText;
                            submitBtn.disabled = false;
                        }
                    });
                    paystack.open();
                })
                .catch(err => {
                    document.getElementById('checkout-error').textContent = 'An error occurred. Please try again.';
                    document.getElementById('checkout-error').style.display = 'block';
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });

            const applyCouponBtn = document.getElementById('apply-coupon');
            if (applyCouponBtn) {
                applyCouponBtn.addEventListener('click', function() {
                    const code = checkoutForm.querySelector('input[name="coupon_code"]').value.toUpperCase();
                    if (code === 'LAUNCH15') {
                        document.getElementById('checkout-success').textContent = 'Coupon applied! 15% discount has been added.';
                        document.getElementById('checkout-success').style.display = 'block';
                    } else {
                        document.getElementById('checkout-error').textContent = 'Invalid coupon code.';
                        document.getElementById('checkout-error').style.display = 'block';
                    }
                });
            }
        }
    })();
    </script>
</body>
</html>