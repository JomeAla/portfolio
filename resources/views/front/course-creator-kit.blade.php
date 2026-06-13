@extends('layouts.app')

@section('title', 'Course Creator Kit - Launch Your Online Course')

@section('content')
<style>
:root {
    --purple: #6366f1;
    --purple-dark: #4338ca;
    --purple-light: #a5b4fc;
    --cream: #FDFBF7;
    --espresso: #1a1410;
    --stone: #6b6560;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: system-ui, sans-serif; background: var(--cream); color: var(--espresso); line-height: 1.7; }
.clash-display { font-family: system-ui; font-weight: 800; }

.hero { min-height: 100vh; background: linear-gradient(135deg, var(--purple) 0%, var(--purple-dark) 100%); color: white; display: flex; align-items: center; padding: 100px 40px; position: relative; overflow: hidden; }
.hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.1) 0%, transparent 50%); }
.hero-content { max-width: 1200px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; }
.hero-text h1 { font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; margin-bottom: 20px; }
.hero-text .tagline { font-size: 1.25rem; opacity: 0.9; margin-bottom: 30px; }
.hero img { width: 100%; border-radius: 20px; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }

.price-tag { background: white; padding: 16px 28px; border-radius: 12px; display: inline-flex; align-items: baseline; gap: 12px; margin-bottom: 24px; }
.price-tag .sale { font-size: 2rem; font-weight: 700; color: var(--espresso); }
.price-tag .original { font-size: 1.1rem; color: var(--stone); text-decoration: line-through; }
.price-tag .badge { background: #10b981; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; }

.btn { display: inline-block; padding: 16px 32px; border-radius: 10px; font-weight: 600; text-decoration: none; transition: 0.3s; }
.btn-white { background: white; color: var(--purple); }
.btn-white:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
.btn-purple { background: var(--purple); color: white; }
.btn-purple:hover { background: var(--purple-dark); }

.countdown { display: flex; gap: 12px; margin: 24px 0; }
.countdown-item { background: rgba(255,255,255,0.15); padding: 10px 16px; border-radius: 10px; text-align: center; min-width: 65px; }
.countdown-item .num { font-size: 1.4rem; font-weight: 700; display: block; }
.countdown-item .lbl { font-size: 0.7rem; opacity: 0.8; text-transform: uppercase; }

.features { padding: 80px 40px; background: white; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; max-width: 1100px; margin: 0 auto; }
.feature-card { padding: 28px; border: 1px solid #e5e7eb; border-radius: 16px; transition: 0.3s; }
.feature-card:hover { border-color: var(--purple); transform: translateY(-3px); }
.feature-icon { width: 50px; height: 50px; background: #e0e7ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 16px; }
.feature-card h3 { font-size: 1.1rem; margin-bottom: 8px; }
.feature-card p { color: var(--stone); font-size: 0.95rem; }

.templates { padding: 80px 40px; background: var(--cream); }
.section-header { text-align: center; max-width: 700px; margin: 0 auto 50px; }
.section-header h2 { font-size: clamp(1.8rem, 3.5vw, 2.5rem); margin-bottom: 12px; }
.section-header p { color: var(--stone); font-size: 1.1rem; }
.template-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; max-width: 1100px; margin: 0 auto; }
.template-card { background: white; padding: 20px; border-radius: 14px; border: 1px solid #e5e7eb; }
.template-card .num { width: 36px; height: 36px; background: var(--purple); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-bottom: 14px; }
.template-card h4 { font-size: 1rem; margin-bottom: 6px; }
.template-card p { font-size: 0.85rem; color: var(--stone); }

.testimonials { padding: 80px 40px; background: var(--purple-dark); color: white; }
.testimonial-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; max-width: 1100px; margin: 0 auto; }
.testimonial-card { background: rgba(255,255,255,0.1); padding: 28px; border-radius: 16px; }
.testimonial-card .quote { font-style: italic; font-size: 1.05rem; margin-bottom: 20px; line-height: 1.7; }
.testimonial-card .author { font-weight: 600; }

.cta { padding: 80px 40px; background: white; text-align: center; }
.cta-box { max-width: 700px; margin: 0 auto; background: linear-gradient(135deg, var(--purple) 0%, var(--purple-dark) 100%); padding: 50px; border-radius: 24px; color: white; }
.cta-box h2 { font-size: clamp(1.5rem, 3vw, 2.2rem); margin-bottom: 12px; }
.cta-box p { opacity: 0.9; margin-bottom: 24px; }
.cta-box .price-display { font-size: 2.5rem; font-weight: 700; margin: 20px 0; }
.cta-box .price-display span { font-size: 1.3rem; opacity: 0.8; text-decoration: line-through; }
.cta-box .btn { background: white; color: var(--purple); }
.footer { padding: 30px; text-align: center; color: var(--stone); font-size: 0.85rem; }

@media (max-width: 768px) {
    .hero-content { grid-template-columns: 1fr; text-align: center; }
    .hero-text h1 { font-size: 2rem; }
    .hero img { order: -1; max-width: 350px; margin: 0 auto; }
    .countdown { justify-content: center; }
}
</style>

<script>document.addEventListener('DOMContentLoaded',function(){const e=new Date;e.setDate(e.getDate()+3);function t(){const n=new Date,e=t-(n= new Date),a=Math.floor(e/864e5),s=Math.floor(e%864e5/36e5),i=Math.floor(e%36e5/6e4),r=Math.floor(e%6e4/1e3);document.getElementById("hrs").textContent=24*a+s,document.getElementById("min").textContent=i,document.getElementById("sec").textContent=r}setInterval(t,1e3),t()});</script>

<nav class="navbar" style="position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:100;background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);padding:12px 24px;border-radius:100px;">
    <a href="/" style="color:#1a1410;font-weight:700;text-decoration:none;">← Back</a>
</nav>

<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <span style="background:rgba(255,255,255,0.2);padding:8px 16px;border-radius:8px;font-size:0.9rem;font-weight:600;">📚 NEW PRODUCT</span>
            <h1 class="clash-display" style="margin-top:16px;">Course Creator Kit</h1>
            <p class="tagline">50+ templates to launch and grow your online course. Landing pages, launch sequences, sales pages & more.</p>
            
            <div class="price-tag">
                <span class="sale">₦18,000</span>
                <span class="original">₦35,000</span>
                <span class="badge">-49%</span>
            </div>
            
            <a href="/checkout/course-creator-kit" class="btn btn-white">Get Course Creator Kit</a>
            
            <div class="countdown" id="countdown">
                <div class="countdown-item"><span class="num" id="hrs">00</span><span class="lbl">Hours</span></div>
                <div class="countdown-item"><span class="num" id="min">00</span><span class="lbl">Mins</span></div>
                <div class="countdown-item"><span class="num" id="sec">00</span><span class="lbl">Secs</span></div>
            </div>
            
            <p style="font-size:0.85rem;opacity:0.8;">🔥 18 people purchased this week</p>
        </div>
        
        <img src="/uploads/products/course-creator-kit-cover.svg" alt="Course Creator Kit">
    </div>
</section>

<section class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">📄</div>
            <h3>Landing Pages</h3>
            <p>3 templates: Pre-launch teaser, Launch day, and Pre-order page</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📧</div>
            <h3>Launch Sequence</h3>
            <p>10-email sequence: Announcement → Launch → Urgency → Close</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💰</div>
            <h3>Sales Pages</h3>
            <p>Long form, short/viral, and webinar registration pages</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎓</div>
            <h3>Onboarding</h3>
            <p>5-email student onboarding with progress tracking</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⭐</div>
            <h3>Testimonials</h3>
            <p>Collect and showcase student success stories</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🧮</div>
            <h3>Pricing Calculator</h3>
            <p>Calculate the perfect price for your course</p>
        </div>
    </div>
</section>

<section class="templates">
    <div class="section-header">
        <h2 class="clash-display">What's Inside?</h2>
        <p>Everything to launch a profitable online course</p>
    </div>
    
    <div class="template-grid">
        <div class="template-card"><div class="num">1</div><h4>Teaser Landing Page</h4><p>Build anticipation before launch</p></div>
        <div class="template-card"><div class="num">2</div><h4>Launch Day Page</h4><p>Convert visitors to students</p></div>
        <div class="template-card"><div class="num">3</div><h4>Pre-Order Page</h4><p>Early bird with discount</p></div>
        <div class="template-card"><div class="num">4</div><h4>Announcement Email</h4><p>Tease big news</p></div>
        <div class="template-card"><div class="num">5</div><h4>Launch Email</h4><p>Go live!</p></div>
        <div class="template-card"><div class="num">6</div><h4>Framework Email</h4><p>Share your method</p></div>
        <div class="template-card"><div class="num">7</div><h4>Testimonial Email</h4><p>Social proof</p></div>
        <div class="template-card"><div class="num">8</div><h4>Objection Handling</h4><p>Address doubts</p></div>
        <div class="template-card"><div class="num">9</div><h4>Last Chance Email</h4><p>Urgency close</p></div>
        <div class="template-card"><div class="num">10</div><h4>Welcome Email</h4><p>Deliver course</p></div>
        <div class="template-card"><div class="num">11</div><h4>Check-in Email</h4><p>Day 3 follow-up</p></div>
        <div class="template-card"><div class="num">12</div><h4>Upsell Email</h4><p>Coaching offer</p></div>
    </div>
</section>

<section class="testimonials">
    <div class="section-header" style="text-align:center;">
        <h2 class="clash-display">Loved by Course Creators</h2>
    </div>
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <p class="quote">"Used the launch sequence. Made ₦420,000 in first 3 days!"</p>
            <p class="author">— Chidinma, Lagos</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"The pricing calculator alone is worth the price. Perfect price!"</p>
            <p class="author">— Emeka, Abuja</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"Launched my first course in 2 weeks. Templates are plug-and-play."</p>
            <p class="author">— Amara, PH</p>
        </div>
    </div>
</section>

<section class="cta">
    <div class="cta-box">
        <h2 class="clash-display">Launch Your Course Today</h2>
        <p>Get all 50+templates. Use them for unlimited courses.</p>
        
        <div class="price-display">₦18,000 <span>₦35,000</span></div>
        
        <a href="/checkout/course-creator-kit" class="btn">Get Instant Access →</a>
        
        <p style="margin-top:20px;font-size:0.85rem;opacity:0.8;">🔒 Secure payment via Paystack | 7-day guarantee</p>
    </div>
</section>

<footer class="footer">
    <p>© 2026 JoAla Ventures | <a href="/">Home</a> | <a href="/store">Store</a></p>
</footer>

@endsection