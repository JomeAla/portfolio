@extends('layouts.app')

@section('title', 'Local Business Digital Kit')

@section('content')
<style>
:root { --green: #10b981; --green-dark: #047857; --green-light: #d1fae5; --cream: #FDFBF7; --espresso: #1a1410; --stone: #6b6560; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: system-ui, sans-serif; background: var(--cream); color: var(--espresso); line-height: 1.7; }

.hero { min-height: 100vh; background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%); color: white; display: flex; align-items: center; padding: 100px 40px; }
.hero-content { max-width: 1200px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.hero-text h1 { font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; margin-bottom: 16px; }
.hero-text .tagline { font-size: 1.2rem; opacity: 0.9; margin-bottom: 24px; }
.hero img { width: 100%; border-radius: 20px; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }

.price-tag { background: white; padding: 14px 24px; border-radius: 12px; display: inline-flex; align-items: baseline; gap: 10px; margin-bottom: 20px; }
.price-tag .sale { font-size: 1.8rem; font-weight: 700; color: var(--espresso); }
.price-tag .original { font-size: 1rem; color: var(--stone); text-decoration: line-through; }
.price-tag .badge { background: var(--green); color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }

.btn { display: inline-block; padding: 14px 28px; border-radius: 10px; font-weight: 600; text-decoration: none; }
.btn-white { background: white; color: var(--green-dark); }
.btn-white:hover { transform: translateY(-2px); }
.btn-green { background: var(--green); color: white; }

.features { padding: 80px 40px; background: white; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; max-width: 1100px; margin: 0 auto; }
.feature-card { padding: 24px; border: 1px solid #e5e7eb; border-radius: 14px; }
.feature-card:hover { border-color: var(--green); }
.feature-icon { width: 46px; height: 46px; background: var(--green-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 14px; }
.feature-card h3 { font-size: 1.1rem; margin-bottom: 6px; }
.feature-card p { color: var(--stone); font-size: 0.9rem; }

.templates { padding: 80px 40px; background: var(--cream); }
.section-header { text-align: center; max-width: 650px; margin: 0 auto 45px; }
.section-header h2 { font-size: clamp(1.8rem, 3.5vw, 2.4rem); margin-bottom: 10px; }
.section-header p { color: var(--stone); }
.template-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; max-width: 1000px; margin: 0 auto; }
.template-card { background: white; padding: 18px; border-radius: 12px; border: 1px solid #e5e7eb; }
.template-card .num { width: 32px; height: 32px; background: var(--green); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; margin-bottom: 12px; }
.template-card h4 { font-size: 0.95rem; margin-bottom: 4px; }
.template-card p { font-size: 0.8rem; color: var(--stone); }

.testimonials { padding: 80px 40px; background: var(--green-dark); color: white; }
.testimonial-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; max-width: 1000px; margin: 0 auto; }
.testimonial-card { background: rgba(255,255,255,0.1); padding: 24px; border-radius: 14px; }
.testimonial-card .quote { font-style: italic; margin-bottom: 16px; }

.cta { padding: 80px 40px; background: white; text-align: center; }
.cta-box { max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%); padding: 45px; border-radius: 20px; color: white; }
.cta-box h2 { font-size: clamp(1.5rem, 3vw, 2rem); margin-bottom: 10px; }
.cta-box p { opacity: 0.9; margin-bottom: 20px; }
.cta-box .price { font-size: 2.2rem; font-weight: 700; margin: 18px 0; }
.cta-box .price span { font-size: 1.2rem; opacity: 0.8; text-decoration: line-through; }
.footer { padding: 25px; text-align: center; color: var(--stone); font-size: 0.85rem; }

@media (max-width: 768px) {
    .hero-content { grid-template-columns: 1fr; text-align: center; }
    .hero img { order: -1; max-width: 320px; margin: 0 auto; }
}
</style>

<script>document.addEventListener('DOMContentLoaded',function(){const e=new Date;e.setDate(e.getDate()+3);function t(){const n=new Date,a=(e-(n=new Date))/1e3,s=Math.floor(a/86400),i=Math.floor(a%86400/3600),r=Math.floor(a%3600/60);document.getElementById("h").textContent=24*s+i,document.getElementById("m").textContent=r}setInterval(t,1e3),t()});</script>

<nav style="position:fixed;top:18px;left:50%;transform:translateX(-50%);z-index:100;background:rgba(255,255,255,0.9);padding:10px 22px;border-radius:100px;">
    <a href="/" style="color:#1a1410;font-weight:700;text-decoration:none;">← Back</a>
</nav>

<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            🏪 NEW PRODUCT
            <h1 style="margin-top:12px;">Local Business Digital Kit</h1>
            <p class="tagline">Digital tools for Nigerian shops, clinics, salons & traders. WhatsApp, SMS, loyalty & more.</p>
            
            <div class="price-tag">
                <span class="sale">₦12,000</span>
                <span class="original">₦25,000</span>
                <span class="badge">-52%</span>
            </div>
            
            <a href="/checkout/local-business-digital-kit" class="btn btn-white">Get Local Business Kit</a>
        </div>
        
        <img src="/uploads/products/local-business-digital-kit-cover.svg" alt="Local Business Digital Kit">
    </div>
</section>

<section class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>WhatsApp Broadcasts</h3>
            <p>10 templates: new stock, flash sales, appointments, loyalty & more</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💬</div>
            <h3>SMS Scripts</h3>
            <p>8 promotional scripts ready to copy and send</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🧾</div>
            <h3>Receipt Templates</h3>
            <p>5 receipt designs with loyalty & upsell</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📍</div>
            <h3>Google Business</h3>
            <p>Step-by-step setup guide for local search</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎁</div>
            <h3>Loyalty Program</h3>
            <p>Points system, cards & milestone rewards</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👫</div>
            <h3>Referral System</h3>
            <p>4 templates to grow through customers</p>
        </div>
    </div>
</section>

<section class="templates">
    <div class="section-header">
        <h2>What's Inside?</h2>
        <p>Everything to digitize your local business</p>
    </div>
    
    <div class="template-grid">
        <div class="template-card"><div class="num">1</div><h4>New Stock Alert</h4><p>Announce new arrivals</p></div>
        <div class="template-card"><div class="num">2</div><h4>Flash Sale SMS</h4><p>Quick promo message</p></div>
        <div class="template-card"><div class="num">3</div><h4>Receipt with Upsell</h4><p>Add next visit offer</p></div>
        <div class="template-card"><div class="num">4</div><h4>Loyalty Points</h4><p>Points = credit system</p></div>
        <div class="template-card"><div class="num">5</div><h4>Birthday Offer</h4><p>Free giftmessage</p></div>
        <div class="template-card"><div class="num">6</div><h4>Refer a Friend</h4><p>Both earn rewards</p></div>
        <div class="template-card"><div class="num">7</div><h4>Appointment Reminder</h4><p>Never miss appointments</p></div>
        <div class="template-card"><div class="num">8</div><h4>Review Request</h4><p>Ask at happy moment</p></div>
    </div>
</section>

<section class="testimonials">
    <div class="section-header" style="text-align:center;">
        <h2 style="color:white;">Used by Nigerian Businesses</h2>
    </div>
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <p class="quote">"My salon uses the WhatsApp templates. Clients love the birthday messages!"</p>
            <p>— Blessing, Lagos Salon</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"The loyalty program increased repeat customers by 40% in 2 months!"</p>
            <p>— Chidi, Pharmacy</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"Simple and practical. Even my staff can use it."</p>
            <p>— Amadi, Spare Parts</p>
        </div>
    </div>
</section>

<section class="cta">
    <div class="cta-box">
        <h2>Digitize Your Business Today</h2>
        <p>40+ templates. Use them forever. No monthly fees.</p>
        
        <div class="price">₦12,000 <span>₦25,000</span></div>
        
        <a href="/checkout/local-business-digital-kit" class="btn btn-white">Get Instant Access →</a>
        
        <p style="margin-top:18px;font-size:0.8rem;opacity:0.8;">🔒 Paystack | 7-day guarantee</p>
    </div>
</section>

<footer class="footer">
    <p>© 2026 JoAla Ventures | <a href="/">Home</a> | <a href="/store">Store</a></p>
</footer>

@endsection