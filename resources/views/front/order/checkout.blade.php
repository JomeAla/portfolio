@extends('layouts.app')

@section('title', 'Checkout - ' . $product->title)

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
--bg:#0a0a0a;
--surface:rgba(255,255,255,0.04);
--surface2:rgba(255,255,255,0.02);
--border:rgba(255,255,255,0.08);
--text:#fafafa;
--muted:#71717a;
--accent:#e11d48;
--accent2:#f97316;
--accent3:#22c55e;
}
html{scroll-behavior:smooth}
body{
font-family:'Instrument Sans',system-ui,sans-serif !important;
background:#050505 !important;
color:var(--text) !important;
line-height:1.6;
overflow-x:hidden;
}
h1,h2,h3{font-family:'Instrument Serif',Georgia,serif;letter-spacing:-.02em}

.container{max-width:1100px;margin:0 auto;padding:0 24px}

/* nav */
.nav{
position:fixed;top:20px;left:50%;transform:translateX(-50%);
z-index:100;
background:rgba(5,5,5,0.85);
backdrop-filter:blur(20px);
-webkit-backdrop-filter:blur(20px);
border:1px solid var(--border);
border-radius:100px;
padding:10px 24px;
}
.nav-inner{display:flex;align-items:center;gap:12px}
.nav-brand{color:var(--text);font-weight:600;font-size:.9rem;text-decoration:none;display:flex;align-items:center;gap:8px}
.nav-brand svg{width:20px;height:20px}

.breadcrumb{
display:flex;align-items:center;gap:8px;
font-size:.85rem;color:var(--muted);
margin-bottom:48px;padding-top:120px;
}
.breadcrumb a{color:var(--muted);text-decoration:none;transition:color .3s}
.breadcrumb a:hover{color:var(--text)}
.breadcrumb span{color:var(--border)}

/* layout */
.checkout-grid{
display:grid;grid-template-columns:1fr 400px;gap:60px;align-items:start;
}
.checkout-left h1{font-size:2.5rem;margin-bottom:8px}
.checkout-left .subtitle{color:var(--muted);font-size:1rem;margin-bottom:40px}

/* form card */
.form-card{
background:var(--surface);
border:1px solid var(--border);
border-radius:24px;
padding:36px;
}
.field-group{margin-bottom:24px}
.field-group label{
display:block;font-size:.85rem;font-weight:500;color:var(--muted);
margin-bottom:8px;letter-spacing:.03em;
}
.field-group input,.field-group select{
width:100%;
background:rgba(255,255,255,0.04);
border:1px solid var(--border);
border-radius:14px;
padding:16px 20px;
font-size:1rem;
color:var(--text);
font-family:'Instrument Sans',system-ui,sans-serif;
transition:all .4s cubic-bezier(0.32,0.72,0,1);
outline:none;
}
.field-group input::placeholder{color:rgba(255,255,255,.2)}
.field-group input:focus,.field-group select:focus{
border-color:rgba(225,29,72,.5);
background:rgba(225,29,72,.04);
box-shadow:0 0 0 3px rgba(225,29,72,.1);
}

/* coupon */
.coupon-row{display:flex;gap:12px;margin-bottom:20px}
.coupon-row input{flex:1}
.coupon-row button{
background:var(--surface2);border:1px solid var(--border);
border-radius:14px;padding:16px 24px;
font-size:.9rem;font-weight:600;color:var(--muted);
cursor:pointer;font-family:'Instrument Sans',system-ui,sans-serif;
transition:all .4s cubic-bezier(0.32,0.72,0,1);
white-space:nowrap;
}
.coupon-row button:hover{border-color:var(--accent);color:var(--accent)}
.coupon-msg{font-size:.88rem;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:none}
.coupon-msg.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:#22c55e;display:block}
.coupon-msg.error{background:rgba(239,68,68,.1);border:1px solid rgba(249,68,68,.2);color:#ef4444;display:block}

/* submit btn */
.pay-btn{
width:100%;
background:var(--accent);color:white;border:none;
border-radius:18px;padding:24px;
font-size:1.1rem;font-weight:700;cursor:pointer;
font-family:'Instrument Sans',system-ui,sans-serif;
transition:all .5s cubic-bezier(0.32,0.72,0,1);
display:flex;align-items:center;justify-content:center;gap:12px;
position:relative;overflow:hidden;
}
.pay-btn::after{
content:'';position:absolute;inset:0;
background:linear-gradient(135deg,rgba(255,255,255,.1) 0%,transparent 60%);
}
.pay-btn:hover{background:#be123c;transform:translateY(-2px);box-shadow:0 20px 40px rgba(225,29,72,.4)}
.pay-btn:active{transform:scale(.98)}
.pay-btn .lock-icon{
width:36px;height:36px;
background:rgba(255,255,255,.15);
border-radius:10px;
display:flex;align-items:center;justify-content:center;
transition:all .4s cubic-bezier(0.32,0.72,0,1);
}
.pay-btn:hover .lock-icon{transform:translate(2px,-1px) scale(1.05)}

.security-note{
display:flex;align-items:center;justify-content:center;gap:8px;
font-size:.8rem;color:var(--muted);margin-top:16px;
}

/* order summary */
.order-summary{
position:sticky;top:100px;
background:var(--surface);
border:1px solid var(--border);
border-radius:24px;overflow:hidden;
}
.summary-header{
padding:24px 28px;
background:rgba(225,29,72,.08);
border-bottom:1px solid var(--border);
}
.summary-header h3{font-size:1.1rem;font-family:'Instrument Sans',system-ui,sans-serif;font-weight:600;margin-bottom:4px}
.summary-header p{font-size:.8rem;color:var(--muted)}
.summary-body{padding:28px}
.product-row{display:flex;gap:16px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border)}
.product-img{
width:72px;height:72px;border-radius:16px;
background:linear-gradient(135deg,rgba(225,29,72,.15),rgba(6,182,212,.1));
border:1px solid var(--border);
display:flex;align-items:center;justify-content:center;
font-size:1.8rem;
flex-shrink:0;
}
.product-info{flex:1;min-width:0}
.product-info h4{font-size:.95rem;font-weight:600;font-family:'Instrument Sans',system-ui,sans-serif;margin-bottom:4px;line-height:1.3}
.product-info .pdesc{font-size:.8rem;color:var(--muted)}
.product-tag{
display:inline-block;
background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);
color:#22c55e;font-size:.7rem;font-weight:600;
padding:3px 10px;border-radius:8px;margin-top:8px;
}

.price-breakdown{margin-bottom:20px}
.price-line{
display:flex;justify-content:space-between;align-items:center;
margin-bottom:10px;font-size:.9rem;
}
.price-line .lbl{color:var(--muted)}
.price-line .val{font-weight:500}
.price-line.discount .lbl{color:#22c55e}
.price-line.discount .val{color:#22c55e}
.price-line.total{
font-size:1.1rem;font-weight:700;
padding-top:14px;margin-top:14px;
border-top:1px solid var(--border);
}
.price-line.total .val{font-size:1.4rem;color:var(--accent)}

/* countdown in summary */
.summary-countdown{
background:rgba(249,115,22,.08);border:1px solid rgba(249,115,22,.2);
border-radius:14px;padding:16px;margin-top:20px;text-align:center;
}
.countdown-label{font-size:.78rem;color:var(--accent2);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px}
.countdown-timer{display:flex;gap:8px;justify-content:center}
.c-t{
background:rgba(249,115,22,.12);border:1px solid rgba(249,115,22,.25);
border-radius:10px;padding:10px 14px;text-align:center;min-width:60px;
}
.c-t .num{font-size:1.4rem;font-weight:700;color:var(--accent2);display:block;line-height:1}
.c-t .lbl{font-size:.62rem;color:rgba(249,115,22,.7);text-transform:uppercase;letter-spacing:.08em;margin-top:2px;display:block}

.urgency-note{
font-size:.78rem;color:var(--accent2);font-weight:600;margin-top:10px;
display:flex;align-items:center;justify-content:center;gap:6px;
}

.guarantee-row{
display:flex;align-items:center;justify-content:center;gap:10px;
margin-top:20px;padding:14px;
background:var(--surface2);border:1px solid var(--border);
border-radius:12px;font-size:.82rem;color:var(--muted);
}
.guarantee-row svg{width:16px;height:16px;color:#22c55e;flex-shrink:0}

/* trust badges */
.trust-row{
display:flex;align-items:center;justify-content:center;gap:20px;
margin-top:24px;
}
.trust-item{display:flex;align-items:center;gap:6px;font-size:.78rem;color:var(--muted)}
.trust-item svg{width:14px;height:14px}

/* loading state */
.pay-btn.loading{opacity:.7;cursor:not-allowed}
.pay-btn.loading .btn-text{display:none}
.pay-btn .loading-spinner{display:none}
.pay-btn.loading .loading-spinner{display:flex;align-items:center;gap:10px}
@keyframes spin{to{transform:rotate(360deg)}}
.spin{animation:spin 1s linear infinite;width:20px;height:20px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%}

/* error message */
.error-msg{
background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);
color:#ef4444;padding:14px 18px;border-radius:12px;
font-size:.9rem;margin-bottom:20px;display:none;
}
.error-msg.show{display:block}

/* hide layout nav */
nav.fixed, .fixed.top-0, .bg-white\/80 { display: none !important; }
main.pt-16 { padding-top: 0 !important; }

@media(max-width:900px){
.checkout-grid{grid-template-columns:1fr}
.order-summary{position:static;margin-top:40px}
.container{padding:0 16px}
}
</style>
@endsection

@section('content')

<nav class="nav">
    <div class="nav-inner">
        <a href="/" class="nav-brand">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="6" fill="#e11d48"/><path d="M6 14l4-8 4 8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Joala Digital
        </a>
    </div>
</nav>

<div class="container">
    <div class="breadcrumb">
        <a href="/">Home</a>
        <span>/</span>
        <a href="/store">Store</a>
        <span>/</span>
        <a href="/store/{{ $product->slug }}">{{ $product->title }}</a>
        <span>/</span>
        <span style="color:var(--text)">Checkout</span>
    </div>

    <div class="checkout-grid">
        <div class="checkout-left">
            <h1>Complete Your Order</h1>
            <p class="subtitle">Fill in your details below to get instant access.</p>

            <div class="form-card">
                <div id="errorMsg" class="error-msg"></div>

                <div class="field-group">
                    <label>Full Name</label>
                    <input type="text" id="name" placeholder="e.g. Jome Ade" autocomplete="name">
                </div>

                <div class="field-group">
                    <label>Email Address</label>
                    <input type="email" id="buyerEmail" placeholder="your@email.com" autocomplete="email">
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

                <button type="button" class="pay-btn" id="payBtn" onclick="payWithPaystack()">
                    <span class="btn-text" style="display:flex;align-items:center;justify-content:center;gap:12px;width:100%">
                        <span class="lock-icon">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 7V5a4 4 0 0 1 8 0v2M2 7h12a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </span>
                        Pay <?php $d = $product->sale_price ?? $product->price; echo '₦' . number_format($d); ?>
                    </span>
                    <span class="loading-spinner">
                        <span class="spin"></span>
                        Processing...
                    </span>
                </button>

                <div class="security-note">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="3" width="12" height="9" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M4 3V2a3 3 0 0 1 6 0v1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    Secured by Paystack — 256-bit SSL encryption
                </div>
            </div>

            <div class="trust-row">
                <div class="trust-item">
                    <svg viewBox="0 0 14 14" fill="none"><path d="M7 1L1 4v4c0 3.31 2.56 6.41 6 7 3.44-.59 6-3.69 6-7V4L7 1z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Buyer Protected
                </div>
                <div class="trust-item">
                    <svg viewBox="0 0 14 14" fill="none"><path d="M2 7h10M5 10l2-2 2 2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Instant Access
                </div>
                <div class="trust-item">
                    <svg viewBox="0 0 14 14" fill="none"><path d="M1 4h12M3 7h8M2 10h10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
                    30-Day Guarantee
                </div>
            </div>
        </div>

        <div class="checkout-right">
            <div class="order-summary">
                <div class="summary-header">
                    <h3>Order Summary</h3>
                    <p>{{ $product->title }}</p>
                </div>
                <div class="summary-body">
                    <div class="product-row">
                        <div class="product-img">📦</div>
                        <div class="product-info">
                            <h4><?php echo $product->title; ?></h4>
                            <p class="pdesc"><?php echo $product->short_description ?? 'Digital download'; ?></p>
                            <span class="product-tag">Digital Download</span>
                        </div>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-line">
                            <span class="lbl">Original Price</span>
                            <span class="val" id="origPrice">₦<?php echo number_format($product->price); ?></span>
                        </div>
                        <div class="price-line discount" id="discountRow" style="display:none">
                            <span class="lbl">Coupon Discount</span>
                            <span class="val" id="discountVal">-₦0</span>
                        </div>
                        <div class="price-line total">
                            <span class="lbl">Total</span>
                            <span class="val" id="totalPrice">₦<?php $dp = $product->sale_price ?? $product->price; echo number_format($dp); ?></span>
                        </div>
                    </div>

                    <div class="summary-countdown">
                        <div class="countdown-label">⏱ Limited offer expires in</div>
                        <div class="countdown-timer">
                            <div class="c-t"><span class="num" id="h1">00</span><span class="lbl">Hrs</span></div>
                            <div class="c-t"><span class="num" id="m1">00</span><span class="lbl">Min</span></div>
                            <div class="c-t"><span class="num" id="s1">00</span><span class="lbl">Sec</span></div>
                        </div>
                        <div class="urgency-note">⚡ Don't miss this deal!</div>
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

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
var productId = <?php echo $product->id; ?>;
var productTitle = '<?php echo addslashes($product->title); ?>';
var displayPrice = <?php $dp = $product->sale_price ?? $product->price; echo $dp * 100; ?>;
var currentAmount = displayPrice;
var paystackKey = '<?php echo $paystackKey; ?>';

function showError(msg) {
    var el = document.getElementById('errorMsg');
    el.textContent = msg;
    el.classList.add('show');
}
function clearError() {
    document.getElementById('errorMsg').classList.remove('show');
}

function applyCoupon() {
    var code = document.getElementById('couponCode').value.trim();
    if (!code) return;
    var msg = document.getElementById('couponMsg');
    msg.className = 'coupon-msg';
    msg.textContent = '';
    msg.style.display = 'none';

    fetch('/order/validate-coupon?code=' + encodeURIComponent(code) + '&product_id=' + productId)
        .then(r => r.json())
        .then(data => {
            msg.style.display = 'block';
            if (data.valid) {
                currentAmount = data.finalAmount * 100;
                document.getElementById('discountRow').style.display = 'flex';
                document.getElementById('discountVal').textContent = '-₦' + data.discount.toLocaleString();
                document.getElementById('totalPrice').textContent = '₦' + Math.round(data.finalAmount).toLocaleString();
                document.getElementById('origPrice').style.textDecoration = 'line-through';
                msg.className = 'coupon-msg success';
                msg.textContent = '✓ Coupon applied! You save ₦' + data.discount.toLocaleString();
                var btn = document.querySelector('.pay-btn .btn-text');
                btn.innerHTML = '<span class="lock-icon"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M4 7V5a4 4 0 0 1 8 0v2M2 7h12a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg></span> Pay ₦' + Math.round(data.finalAmount).toLocaleString();
            } else {
                msg.className = 'coupon-msg error';
                msg.textContent = '✕ ' + (data.message || 'Invalid coupon code');
                currentAmount = displayPrice;
            }
        })
        .catch(() => {
            msg.style.display = 'block';
            msg.className = 'coupon-msg error';
            msg.textContent = '✕ Error validating coupon. Try again.';
        });
}

function payWithPaystack() {
    clearError();
    var name = document.getElementById('name').value.trim();
    var email = document.getElementById('buyerEmail').value.trim();
    var phone = document.getElementById('phone').value.trim();
    var coupon = document.getElementById('couponCode').value.trim();

    if (!name || !email || !phone) {
        showError('Please fill in all required fields.');
        return;
    }
    if (!email.includes('@') || !email.includes('.')) {
        showError('Please enter a valid email address.');
        return;
    }
    if (phone.length < 7) {
        showError('Please enter a valid phone number.');
        return;
    }

    var btn = document.getElementById('payBtn');
    btn.classList.add('loading');

    fetch('/store/initiate-payment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
        },
        body: JSON.stringify({
            product_id: productId,
            name: name,
            email: email,
            phone: phone,
            coupon_code: coupon || null
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.classList.remove('loading');
        if (data.error) {
            showError(data.error);
            return;
        }
        var paystackKey = data.paystack_public_key || 'pk_live_xxxxxxxxxxxxx';
        var handler = PaystackPop.setup({
            key: paystackKey,
            email: email,
            amount: data.amount || currentAmount,
            reference: data.order?.order_number || '',
            metadata: {
                product_id: productId,
                product_title: productTitle,
                name: name,
                phone: phone,
                coupon_code: coupon || ''
            },
            callback: function(response) {
                window.location.href = '/order/success?reference=' + response.reference + '&trxref=' + response.trxref;
            },
            onClose: function() {
                btn.classList.remove('loading');
            }
        });
        handler.openIframe();
    })
    .catch(err => {
        btn.classList.remove('loading');
        showError('Payment initialization failed. Please try again.');
    });
}

// 24hr countdown timer
function getResetTime() {
    var now = new Date();
    var tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(0, 0, 0, 0);
    return tomorrow;
}
function tick() {
    var now = new Date();
    var diff = Math.max(0, getResetTime() - now);
    var h = Math.floor(diff / 3600000);
    var m = Math.floor((diff % 3600000) / 60000);
    var s = Math.floor((diff % 60000) / 1000);
    var pad = function(n) { return String(n).padStart(2, '0'); };
    var he = document.getElementById('h1'), me = document.getElementById('m1'), se = document.getElementById('s1');
    if (he) he.textContent = pad(h);
    if (me) me.textContent = pad(m);
    if (se) se.textContent = pad(s);
    requestAnimationFrame(tick);
}
tick();
</script>
@endsection