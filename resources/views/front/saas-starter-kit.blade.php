@extends('layouts.app')

@section('title', 'SaaS Starter Kit - Build Your Own SaaS')

@section('content')
<style>
:root { --purple: #8b5cf6; --purple-dark: #6d28d9; --purple-light: #ede9fe; --cream: #FDFBF7; --espresso: #1a1410; --stone: #6b6560; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: system-ui, sans-serif; background: var(--cream); color: var(--espresso); line-height: 1.7; }

.hero { min-height: 100vh; background: linear-gradient(135deg, var(--purple) 0%, var(--purple-dark) 100%); color: white; display: flex; align-items: center; padding: 100px 40px; }
.hero-content { max-width: 1200px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.hero-text h1 { font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; margin-bottom: 16px; }
.hero-text .tagline { font-size: 1.2rem; opacity: 0.9; margin-bottom: 24px; }
.hero img { width: 100%; border-radius: 20px; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }

.price-tag { background: white; padding: 14px 24px; border-radius: 12px; display: inline-flex; align-items: baseline; gap: 10px; margin-bottom: 20px; }
.price-tag .sale { font-size: 1.8rem; font-weight: 700; color: var(--espresso); }
.price-tag .original { font-size: 1rem; color: var(--stone); text-decoration: line-through; }
.price-tag .badge { background: #22c55e; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }

.btn { display: inline-block; padding: 14px 28px; border-radius: 10px; font-weight: 600; text-decoration: none; }
.btn-white { background: white; color: var(--purple-dark); }
.btn-white:hover { transform: translateY(-2px); }
.btn-dark { background: var(--espresso); color: white; }
.btn-dark:hover { background: #000; }

.features { padding: 80px 40px; background: white; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; max-width: 1100px; margin: 0 auto; }
.feature-card { padding: 24px; border: 1px solid #e5e7eb; border-radius: 14px; }
.feature-card:hover { border-color: var(--purple); }
.feature-icon { width: 46px; height: 46px; background: var(--purple-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 14px; }
.feature-card h3 { font-size: 1.1rem; margin-bottom: 6px; }
.feature-card p { color: var(--stone); font-size: 0.9rem; }

.templates { padding: 80px 40px; background: var(--cream); }
.section-header { text-align: center; max-width: 650px; margin: 0 auto 45px; }
.section-header h2 { font-size: clamp(1.8rem, 3.5vw, 2.4rem); margin-bottom: 10px; }
.section-header p { color: var(--stone); }
.template-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; max-width: 1000px; margin: 0 auto; }
.template-card { background: white; padding: 18px; border-radius: 12px; border: 1px solid #e5e7eb; }
.template-card .num { width: 32px; height: 32px; background: var(--purple); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; margin-bottom: 12px; }
.template-card h4 { font-size: 0.95rem; margin-bottom: 4px; }
.template-card p { font-size: 0.8rem; color: var(--stone); }

.testimonials { padding: 80px 40px; background: var(--purple-dark); color: white; }
.testimonial-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; max-width: 1000px; margin: 0 auto; }
.testimonial-card { background: rgba(255,255,255,0.1); padding: 24px; border-radius: 14px; }
.testimonial-card .quote { font-style: italic; margin-bottom: 16px; }

.cta { padding: 80px 40px; background: white; text-align: center; }
.cta-box { max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, var(--purple) 0%, var(--purple-dark) 100%); padding: 45px; border-radius: 20px; color: white; }
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
            ⚡ NEW PRODUCT
            <h1 style="margin-top:12px;">SaaS Starter Kit</h1>
            <p class="tagline">Complete Laravel template to launch your own SaaS business. Auth, payments, subscriptions & more.</p>
            
            <div class="price-tag">
                <span class="sale">₦45,000</span>
                <span class="original">₦85,000</span>
                <span class="badge">-47%</span>
            </div>
            
            <a href="/checkout/saas-starter-kit" class="btn btn-white">Get SaaS Starter Kit</a>
            
            <p style="font-size:0.9rem;opacity:0.8;margin-top:16px;">For developers. Ready to deploy.</p>
        </div>
        
        <img src="/uploads/products/saas-starter-kit-cover.svg" alt="SaaS Starter Kit">
    </div>
</section>

<section class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">🔐</div>
            <h3>Authentication</h3>
            <p>Laravel Breeze + Social login, roles, 2FA ready</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💳</div>
            <h3>Payments</h3>
            <p>Paystack integrated - one-time & subscriptions</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Admin Dashboard</h3>
            <p>Users, revenue, analytics, user management</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📧</div>
            <h3>Email Marketing</h3>
            <p>Queue system, templates, Mailgun ready</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔌</div>
            <h3>REST API</h3>
            <p>Sanctum auth, API endpoints, webhooks</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🚀</div>
            <h3>Deployment</h3>
            <p>Deploy scripts, cron jobs, server setup</p>
        </div>
    </div>
</section>

<section class="templates">
    <div class="section-header">
        <h2>What's Included?</h2>
        <p>Everything to launch your SaaS</p>
    </div>
    
    <div class="template-grid">
        <div class="template-card"><div class="num">1</div><h4>Laravel Core</h4><p>Latest Laravel 10+ setup</p></div>
        <div class="template-card"><div class="num">2</div><h4>User Auth</h4><p>Registration, login, password reset</p></div>
        <div class="template-card"><div class="num">3</div><h4>Admin Panel</h4><p>Dashboard, user management</p></div>
        <div class="template-card"><div class="num">4</div><h4>Paystack</h4><p>Payment processing</p></div>
        <div class="template-card"><div class="num">5</div><h4>Billing</h4><p>Subscription plans, intervals</p></div>
        <div class="template-card"><div class="num">6</div><h4>Email Queue</h4><p>Automated sending</p></div>
        <div class="template-card"><div class="num">7</div><h4>API</h4><p>RESTful endpoints</p></div>
        <div class="template-card"><div class="num">8</div><h4>Deploy Script</h4><p>One-click deployment</p></div>
    </div>
</section>

<section class="testimonials">
    <div class="section-header" style="text-align:center;">
        <h2 style="color:white;">Used by Developers</h2>
    </div>
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <p class="quote">"Launched my invoicing SaaS in 2 weeks. The code is clean and well-structured."</p>
            <p>— Dev @techstart</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"Paystack integration worked out of the box. Saved me hours of development."</p>
            <p>— Sarah, Lagos</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"Best investment for anyone building SaaS in Nigeria."</p>
            <p>— Chidi, Abuja</p>
        </div>
    </div>
</section>

<section class="cta">
    <div class="cta-box">
        <h2>Launch Your SaaS Today</h2>
        <p>Complete code. Documentation included. No monthly fees.</p>
        
        <div class="price">₦45,000 <span>₦85,000</span></div>
        
        <a href="/checkout/saas-starter-kit" class="btn btn-white">Get Instant Access →</a>
        
        <p style="margin-top:18px;font-size:0.8rem;opacity:0.8;">🔒 Paystack | 7-day guarantee</p>
    </div>
</section>

<footer class="footer">
    <p>© 2026 JoAla Ventures | <a href="/">Home</a> | <a href="/store">Store</a></p>
</footer>

@endsection