@extends('layouts.app')

@section('title', 'Done-For-You Email Automation - We Build While You Focus')

@section('content')
<!-- Timer Script -->
<script>
(function() {
    const duration = 24 * 60 * 60 * 1000;
    const endTime = Date.now() + duration;
    function updateTimer() {
        const now = Date.now();
        const remaining = Math.max(0, endTime - now);
        const hours = Math.floor(remaining / (1000 * 60 * 60));
        const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((remaining % (1000 * 60)) / 1000);
        var hEl = document.getElementById('dhours');
        var mEl = document.getElementById('dminutes');
        var sEl = document.getElementById('dseconds');
        if(hEl) hEl.textContent = String(hours).padStart(2, '0');
        if(mEl) mEl.textContent = String(minutes).padStart(2, '0');
        if(sEl) sEl.textContent = String(seconds).padStart(2, '0');
    }
    updateTimer();
    setInterval(updateTimer, 1000);
})();
</script>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap');

:root { --cream: #FDFBF7; --espresso: #1a1410; --blue: #4f46e5; --indigo: #4338ca; --stone: #6b6560; --mist: #E8E4DF; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--cream); color: var(--espresso); line-height: 1.7; overflow-x: hidden; }
html { scroll-behavior: smooth; }

::selection { background: var(--blue); color: white; }
.clash-display { font-family: 'Space Grotesk', sans-serif; }

.navbar { position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 1000; background: rgba(253,251,247,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(26,20,16,0.08); border-radius: 100px; padding: 12px 24px; }
.navbar a { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 18px; color: var(--espresso); text-decoration: none; }

.hero { min-height: 100dvh; display: flex; align-items: center; padding: 140px 40px 80px; position: relative; overflow: hidden; background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); }
.hero-bg { position: absolute; inset: 0; background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(79,70,229,0.1) 0%, transparent 70%); }
.hero-content { max-width: 1200px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }

.eyebadge { display: inline-flex; align-items: center; gap: 8px; background: var(--blue); color: white; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; padding: 8px 16px; border-radius: 100px; margin-bottom: 24px; }

.hero-title { font-family: 'Space Grotesk', sans-serif; font-size: clamp(42px, 5vw, 64px); font-weight: 700; line-height: 1.05; margin-bottom: 20px; }
.hero-title span { color: var(--blue); }

.hero-subtitle { font-size: 18px; color: var(--stone); max-width: 480px; margin-bottom: 32px; }
.hero-cta { display: inline-flex; align-items: center; gap: 12px; background: var(--blue); color: white; font-weight: 600; padding: 18px 32px; border-radius: 100px; text-decoration: none; transition: all 0.4s; }
.hero-cta:hover { transform: scale(1.02); box-shadow: 0 20px 40px rgba(79,70,229,0.3); }
.hero-price { margin-top: 24px; font-size: 14px; color: var(--stone); }
.hero-price strong { font-size: 28px; font-weight: 700; }

.hero-visual { position: relative; }
.visual-card { background: white; border-radius: 24px; padding: 48px; height: 420px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; box-shadow: 0 30px 60px rgba(79,70,229,0.2); }

.gear-icon { width: 120px; height: 120px; stroke: var(--blue); stroke-width: 6; fill: none; margin-bottom: 24px; }
.gear-icon circle { stroke-miterlimit: 10; }
.gear-icon line { stroke-linecap: round; }

.visual-card .title { font-family: 'Space Grotesk', sans-serif; font-size: 24px; font-weight: 700; margin-bottom: 12px; color: var(--espresso); }
.visual-card .subtitle { font-size: 16px; color: var(--stone); margin-bottom: 24px; }
.visual-card .price { font-size: 32px; font-weight: 700; color: var(--blue); }

.hero-list { margin-top: 32px; text-align: left; }
.hero-list li { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; font-size: 15px; }
.hero-list .check { color: #22c55e; }

.process { padding: 80px 40px; background: white; }
.process-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; max-width: 1100px; margin: 40px auto 0; }
.process-step { text-align: center; padding: 24px; }
.process-num { width: 48px; height: 48px; background: var(--blue); color: white; font-weight: 700; font-size: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.process-title { font-weight: 700; margin-bottom: 8px; }
.process-desc { font-size: 14px; color: var(--stone); }

.cta-section { padding: 100px 40px; text-align: center; }
.cta-box { background: var(--blue); border-radius: 32px; padding: 60px 40px; max-width: 700px; margin: 0 auto; color: white; }
.cta-title { font-family: 'Space Grotesk', sans-serif; font-size: clamp(28px, 4vw, 40px); font-weight: 700; margin-bottom: 16px; }
.cta-price { font-size: 18px; opacity: 0.9; margin-bottom: 32px; }
.cta-price span { font-size: 40px; font-weight: 700; }
.cta-btn { display: inline-flex; align-items: center; gap: 12px; background: white; color: var(--blue); font-weight: 700; font-size: 18px; padding: 20px 40px; border-radius: 100px; text-decoration: none; }
.cta-btn:hover { transform: scale(1.05); }
.guarantee { margin-top: 20px; font-size: 14px; opacity: 0.8; }

.includes { padding: 80px 40px; max-width: 900px; margin: 0 auto; }
.includes-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 40px; }
.include-card { display: flex; gap: 16px; padding: 20px; background: white; border-radius: 16px; border: 1px solid var(--mist); }
.include-icon { width: 48px; height: 48px; min-width: 48px; background: var(--blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
.include-text h4 { font-weight: 700; margin-bottom: 4px; }
.include-text p { font-size: 14px; color: var(--stone); }

.footer { background: var(--mist); padding: 40px; text-align: center; font-size: 14px; color: var(--stone); }

@media (max-width: 768px) {
    .hero { padding: 120px 24px 60px; }
    .hero-content { grid-template-columns: 1fr; gap: 40px; }
    .process-grid { grid-template-columns: 1fr 1fr; }
    .includes-grid { grid-template-columns: 1fr; }
}
</style>

<nav class="navbar">
    <a href="/">JoAla</a>
</nav>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-text">
            <div class="eyebadge">⚙️ Done-For-You</div>
            <h1 class="hero-title clash-display">We Build Your Email<br><span>Marketing System</span></h1>
            <p class="hero-subtitle">Don't have time to set up emails? We build your complete email marketing system while you focus on running your business.</p>
            <a href="#pricing" class="hero-cta">Book Strategy Call →</a>
            <p class="hero-price"><strong>₦150,000</strong> • One-time payment</p>
            
            <ul class="hero-list">
                <li><span class="check">✓</span> 3 Custom sequences built</li>
                <li><span class="check">✓</span> Full implementation & testing</li>
                <li><span class="check">✓</span> 30 days support included</li>
                <li><span class="check">✓</span> Results guaranteed</li>
            </ul>
        </div>
        <div class="hero-visual">
            <div class="visual-card">
                <svg class="gear-icon" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="40" stroke-miterlimit="10"/>
                    <circle cx="50" cy="50" r="20" stroke-miterlimit="10"/>
                    <line x1="50" y1="5" x2="50" y2="20"/>
                    <line x1="50" y1="80" x2="50" y2="95"/>
                    <line x1="5" y1="50" x2="20" y2="50"/>
                    <line x1="80" y1="50" x2="95" y2="50"/>
                    <line x1="15" y1="15" x2="28" y2="28"/>
                    <line x1="72" y1="72" x2="85" y2="85"/>
                    <line x1="15" y1="85" x2="28" y2="72"/>
                    <line x1="72" y1="28" x2="85" y2="15"/>
                </svg>
                <div class="title">Done-For-You Email</div>
                <div class="subtitle">We build. You grow.</div>
                <div class="price">₦150,000</div>
            </div>
        </div>
    </div>
</section>

<section class="process">
    <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 32px; font-weight: 700; text-align: center;">How It Works</h2>
    
    <div class="process-grid">
        <div class="process-step">
            <div class="process-num">1</div>
            <h4 class="process-title">Discovery</h4>
            <p class="process-desc">30-minute strategy call to understand your business and goals.</p>
        </div>
        <div class="process-step">
            <div class="process-num">2</div>
            <h4 class="process-title">Implementation</h4>
            <p class="process-desc">We build 3 custom email sequences tailored to your business.</p>
        </div>
        <div class="process-step">
            <div class="process-num">3</div>
            <h4 class="process-title">Testing</h4>
            <p class="process-desc">We test everything and optimize for maximum results.</p>
        </div>
        <div class="process-step">
            <div class="process-num">4</div>
            <h4 class="process-title">Handover</h4>
            <p class="process-desc">Documentation, training, and 30 days support.</p>
        </div>
    </div>
</section>

<section class="includes">
    <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 32px; font-weight: 700; text-align: center;">What's Included</h2>
    
    <div class="includes-grid">
        <div class="include-card">
            <div class="include-icon">📧</div>
            <div class="include-text">
                <h4>Welcome Sequence</h4>
                <p>3-5 personalized welcome emails that convert new subscribers.</p>
            </div>
        </div>
        <div class="include-card">
            <div class="include-icon">🛒</div>
            <div class="include-text">
                <h4>Cart Abandonment</h4>
                <p>2-3 recovery emails to recover lost sales.</p>
            </div>
        </div>
        <div class="include-card">
            <div class="include-icon">❤️</div>
            <div class="include-text">
                <h4>Re-engagement</h4>
                <p>3-4 emails to win back inactive subscribers.</p>
            </div>
        </div>
        <div class="include-card">
            <div class="include-icon">📞</div>
            <div class="include-text">
                <h4>Priority Support</h4>
                <p>30 days email support. We answer within 24 hours.</p>
            </div>
        </div>
        <div class="include-card">
            <div class="include-icon">📚</div>
            <div class="include-text">
                <h4>Documentation</h4>
                <p>Complete guide on managing your email system.</p>
            </div>
        </div>
        <div class="include-card">
            <div class="include-icon">🎯</div>
            <div class="include-text">
                <h4>90-Day Check-in</h4>
                <p>We follow up after 90 days to ensure results.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section" id="pricing">
    <div class="cta-box">
        <h2 class="cta-title">Ready to Get Started?</h2>
        <p class="cta-price"><span>₦150,000</span> one-time payment<br><small>Results guaranteed • No hidden fees</small></p>
        <a href="https://joala.com.ng/buy/done-for-you-email-automation" class="cta-btn">Book Strategy Call →</a>
        <p class="guarantee">100% satisfaction guarantee or your money back</p>
        
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.2);">
            <p style="font-size: 13px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: white; margin-bottom: 16px;">⚡ Limited Time Offer</p>
            <div style="display: flex; justify-content: center; gap: 16px; margin-bottom: 16px;">
                <div style="background: rgba(255,255,255,0.2); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 42px; font-weight: 700; line-height: 1;" id="dhours">23</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Hours</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 42px; font-weight: 700; line-height: 1;" id="dminutes">59</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Minutes</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 42px; font-weight: 700; line-height: 1; color: #a5b4fc;" id="dseconds">59</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Seconds</div>
                </div>
            </div>
            <p style="font-size: 16px; font-weight: 600; color: white;">Get <strong style="color: #a5b4fc;">₦25,000 off</strong> when timer ends!</p>
        </div>
    </div>
</section>

<footer class="footer">
    <p>© 2026 JoAla Ventures. All rights reserved.</p>
</footer>

@endsection