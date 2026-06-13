@extends('layouts.app')

@section('title', 'Premium Bundle - Complete Email Marketing Solution')

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
        var hEl = document.getElementById('phours');
        var mEl = document.getElementById('pminutes');
        var sEl = document.getElementById('pseconds');
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

:root { --cream: #FDFBF7; --espresso: #1a1410; --sage: #4a5d52; --gold: #f59e0b; --stone: #6b6560; --mist: #E8E4DF; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--cream); color: var(--espresso); line-height: 1.7; overflow-x: hidden; }
html { scroll-behavior: smooth; }

::selection { background: var(--sage); color: white; }
.clash-display { font-family: 'Space Grotesk', sans-serif; }

.noise-overlay { position: fixed; inset: 0; z-index: 9999; pointer-events: none; opacity: 0.03; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }

.navbar { position: fixed; top: 24px; left: 50%; transform: translateX(-50%); z-index: 1000; background: rgba(253,251,247,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(26,20,16,0.08); border-radius: 100px; padding: 12px 24px; }
.navbar a { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 18px; color: var(--espresso); text-decoration: none; }

.hero { min-height: 100dvh; display: flex; align-items: center; padding: 140px 40px 80px; position: relative; overflow: hidden; }
.hero-bg { position: absolute; inset: 0; background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(245,158,11,0.1) 0%, transparent 70%); }
.hero-content { max-width: 1200px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 80px; align-items: center; }

.premium-badge { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; padding: 8px 16px; border-radius: 100px; margin-bottom: 24px; }

.hero-title { font-family: 'Space Grotesk', sans-serif; font-size: clamp(42px, 5vw, 64px); font-weight: 700; line-height: 1.05; margin-bottom: 20px; }
.hero-title span { color: #f59e0b; }

.hero-subtitle { font-size: 18px; color: var(--stone); max-width: 480px; margin-bottom: 32px; }
.hero-cta { display: inline-flex; align-items: center; gap: 12px; background: var(--espresso); color: white; font-weight: 600; padding: 18px 32px; border-radius: 100px; text-decoration: none; transition: all 0.4s cubic-bezier(0.32,0.72,0,1); }
.hero-cta:hover { transform: scale(1.02); box-shadow: 0 20px 40px rgba(26,20,16,0.15); }
.hero-price { margin-top: 24px; font-size: 14px; color: var(--stone); }
.hero-price strong { font-size: 28px; font-weight: 700; }
.hero-price del { opacity: 0.6; margin-left: 8px; }

.hero-visual { position: relative; }
.visual-card { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 24px; padding: 48px; height: 420px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; box-shadow: 0 30px 60px rgba(245,158,11,0.3); }
.visual-card .star { font-size: 64px; margin-bottom: 16px; }
.visual-card .title { font-family: 'Space Grotesk', sans-serif; font-size: 28px; font-weight: 700; margin-bottom: 16px; }
.visual-card .features { font-size: 14px; opacity: 0.9; margin-bottom: 24px; }
.visual-card .price { background: white; color: var(--espresso); padding: 12px 32px; border-radius: 100px; font-weight: 700; font-size: 24px; }
.visual-card .original { text-decoration: line-through; opacity: 0.7; margin-right: 8px; }

.floating-badge { position: absolute; bottom: -20px; left: -20px; background: white; padding: 20px; border-radius: 16px; box-shadow: 0 20px 40px rgba(26,20,16,0.15); display: flex; align-items: center; gap: 12px; }
.floating-badge .icon { width: 48px; height: 48px; background: var(--sage); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
.floating-badge .text { font-weight: 700; font-size: 16px; }
.floating-badge .sub { font-size: 13px; color: var(--stone); }

.section { padding: 100px 40px; max-width: 1100px; margin: 0 auto; }
.section-title { font-family: 'Space Grotesk', sans-serif; font-size: clamp(32px, 4vw, 44px); font-weight: 700; text-align: center; margin-bottom: 16px; }
.section-subtitle { font-size: 18px; color: var(--stone); text-align: center; margin-bottom: 48px; }

.benefits-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
.benefit-card { background: white; border-radius: 20px; padding: 32px; border: 1px solid var(--mist); }
.benefit-icon { width: 56px; height: 56px; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 20px; }
.benefit-title { font-weight: 700; font-size: 20px; margin-bottom: 8px; }
.benefit-desc { font-size: 15px; color: var(--stone); }
.benefit-value { font-weight: 700; color: #f59e0b; margin-top: 12px; }

.comparison { background: white; border-radius: 24px; padding: 40px; border: 1px solid var(--mist); margin-top: 40px; }
.comparison-title { font-family: 'Space Grotesk', sans-serif; font-size: 24px; font-weight: 700; text-align: center; margin-bottom: 32px; }
.compare-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; padding: 16px 0; border-bottom: 1px solid var(--mist); }
.compare-row:last-child { border: none; }
.compare-header { font-weight: 700; text-align: center; }
.compare-cell { text-align: center; }
.compare-check { color: #22c55e; font-size: 20px; }
.compare-cross { color: #ef4444; font-size: 20px; }

.cta-section { padding: 100px 40px; text-align: center; }
.cta-box { background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 32px; padding: 60px 40px; max-width: 700px; margin: 0 auto; color: white; }
.cta-title { font-family: 'Space Grotesk', sans-serif; font-size: clamp(28px, 4vw, 40px); font-weight: 700; margin-bottom: 16px; }
.cta-price { font-size: 18px; opacity: 0.9; margin-bottom: 32px; }
.cta-price span { font-size: 40px; font-weight: 700; }
.cta-btn { display: inline-flex; align-items: center; gap: 12px; background: white; color: var(--espresso); font-weight: 700; font-size: 18px; padding: 20px 40px; border-radius: 100px; text-decoration: none; transition: all 0.4s; }
.cta-btn:hover { transform: scale(1.05); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
.guarantee { margin-top: 20px; font-size: 14px; opacity: 0.8; }

.footer { background: var(--mist); padding: 40px; text-align: center; font-size: 14px; color: var(--stone); }

@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
@media (max-width: 768px) {
    .hero { padding: 120px 24px 60px; }
    .hero-content { grid-template-columns: 1fr; gap: 40px; }
    .hero-title { font-size: 36px; }
    .section { padding: 60px 24px; }
    .comparison { overflow-x: auto; }
    .compare-row { grid-template-columns: 1fr; gap: 12px; }
}
</style>

<div class="noise-overlay"></div>

<nav class="navbar">
    <a href="/">JoAla</a>
</nav>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-text">
            <div class="premium-badge">★ Premium Bundle</div>
            <h1 class="hero-title clash-display">Complete Email<br><span>Marketing Solution</span></h1>
            <p class="hero-subtitle">Get everything you need for email marketing success. Templates + Done-For-You setup + Priority support. One price.</p>
            <a href="#pricing" class="hero-cta">Get Premium Bundle <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></a>
            <p class="hero-price"><strong>₦65,000</strong> <del>₦90,000</del> • One-time payment</p>
        </div>
        <div class="hero-visual">
            <div class="visual-card">
                <div class="star">★</div>
                <div class="title">Premium Bundle</div>
                <div class="features">✓ Templates Pack<br>✓ Done-For-You Setup<br>✓ Priority Support</div>
                <div class="price"><span class="original">₦90,000</span>₦65,000</div>
            </div>
            <div class="floating-badge">
                <div class="icon">✓</div>
                <div class="text">Save ₦25,000</div>
                <div class="sub">28% savings</div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <h2 class="section-title">What's Included</h2>
    <p class="section-subtitle">Everything you need for complete email marketing success</p>
    
    <div class="benefits-grid">
        <div class="benefit-card">
            <div class="benefit-icon">📧</div>
            <h3 class="benefit-title">Email Templates Pack</h3>
            <p class="benefit-desc">24 professional email templates across 6 sequences. Copy, paste, and send.</p>
            <div class="benefit-value">₦15,000 value</div>
        </div>
        
        <div class="benefit-card">
            <div class="benefit-icon">⚙️</div>
            <h3 class="benefit-title">Done-For-You Setup</h3>
            <p class="benefit-desc">We set up 3 email sequences for you. Customized with your business details.</p>
            <div class="benefit-value">₦50,000 value</div>
        </div>
        
        <div class="benefit-card">
            <div class="benefit-icon">🎯</div>
            <h3 class="benefit-title">Priority Support</h3>
            <p class="benefit-desc">30 days priority email support. We answer within 24 hours.</p>
            <div class="benefit-value">₦25,000 value</div>
        </div>
        
        <div class="benefit-card">
            <div class="benefit-icon">📚</div>
            <h3 class="benefit-title">Bonus Materials</h3>
            <p class="benefit-desc">Email marketing strategy playbook, subject line swipe file, and more.</p>
            <div class="benefit-value">Included free</div>
        </div>
    </div>
</section>

<section class="comparison">
    <h3 class="comparison-title">Why Premium Bundle?</h3>
    <div class="compare-row">
        <div class="compare-header"></div>
        <div class="compare-header">Templates Only</div>
        <div class="compare-header">Premium Bundle</div>
    </div>
    <div class="compare-row">
        <div class="compare-cell">24 Email Templates</div>
        <div class="compare-cell"><span class="compare-check">✓</span></div>
        <div class="compare-cell"><span class="compare-check">✓</span></div>
    </div>
    <div class="compare-row">
        <div class="compare-cell">Setup Service</div>
        <div class="compare-cell"><span class="compare-cross">✗</span></div>
        <div class="compare-cell"><span class="compare-check">✓</span></div>
    </div>
    <div class="compare-row">
        <div class="compare-cell">Priority Support</div>
        <div class="compare-cell"><span class="compare-cross">✗</span></div>
        <div class="compare-cell"><span class="compare-check">✓</span></div>
    </div>
    <div class="compare-row">
        <div class="compare-cell">Bonus Materials</div>
        <div class="compare-cell"><span class="compare-cross">✗</span></div>
        <div class="compare-cell"><span class="compare-check">✓</span></div>
    </div>
    <div class="compare-row">
        <div class="compare-cell">Total Value</div>
        <div class="compare-cell">₦15,000</div>
        <div class="compare-cell"><strong>₦90,000</strong></div>
    </div>
</section>

<section class="cta-section" id="pricing">
    <div class="cta-box">
        <h2 class="cta-title">Get the Premium Bundle</h2>
        <p class="cta-price"><span>₦65,000</span> one-time payment<br><small>Instant access • Lifetime updates</small></p>
        <a href="https://joala.com.ng/buy/email-marketing-premium-bundle" class="cta-btn">Get Premium Now →</a>
        <p class="guarantee">30-day money-back guarantee</p>
        
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.2);">
            <p style="font-size: 13px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: white; margin-bottom: 16px;">⚡ Limited Time Offer</p>
            <div style="display: flex; justify-content: center; gap: 16px; margin-bottom: 16px;">
                <div style="background: rgba(255,255,255,0.2); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 42px; font-weight: 700; line-height: 1;" id="phours">23</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Hours</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 42px; font-weight: 700; line-height: 1;" id="pminutes">59</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Minutes</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 42px; font-weight: 700; line-height: 1; color: #fcd34d;" id="pseconds">59</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Seconds</div>
                </div>
            </div>
            <p style="font-size: 16px; font-weight: 600; color: white;">Get <strong style="color: #fcd34d;">₦10,000 off</strong> when timer ends!</p>
        </div>
    </div>
</section>

<footer class="footer">
    <p>© 2026 JoAla Ventures. All rights reserved.</p>
</footer>

@endsection