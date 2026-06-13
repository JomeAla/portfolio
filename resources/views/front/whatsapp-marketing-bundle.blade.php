@extends('layouts.app')

@section('title', 'WhatsApp Marketing Bundle - 48 Ready-to-Send Templates')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Clash+Display:wght@600;700;800&display=swap');

:root {
    --wa-green: #25D366;
    --wa-dark: #128C7E;
    --wa-light: #DCF8C6;
    --cream: #FDFBF7;
    --espresso: #1a1410;
    --mist: #e8e4df;
    --stone: #6b6560;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html { scroll-behavior: smooth; }

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--cream);
    color: var(--espresso);
    line-height: 1.7;
    overflow-x: hidden;
}

.clash-display { font-family: 'Clash Display', sans-serif; }

/* Navbar */
.navbar {
    position: fixed;
    top: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background: rgba(253, 251, 247, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(26, 20, 16, 0.08);
    border-radius: 100px;
    padding: 12px 24px;
}

.navbar.scrolled {
    top: 16px;
    border-radius: 16px;
}

/* Hero Section */
.hero {
    min-height: 100dvh;
    display: flex;
    align-items: center;
    padding: 140px 40px 80px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--wa-green) 0%, var(--wa-dark) 100%);
}

.hero-bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(255,255,255,0.15) 0%, transparent 70%);
}

.hero-content {
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.hero-text h1 {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    color: white;
    line-height: 1.1;
    margin-bottom: 24px;
}

.hero-text .tagline {
    font-size: 1.25rem;
    color: rgba(255,255,255,0.9);
    margin-bottom: 32px;
}

.hero-visual {
    position: relative;
}

.hero-visual img {
    width: 100%;
    max-width: 500px;
    border-radius: 24px;
    box-shadow: 0 40px 80px rgba(0,0,0,0.3);
}

/* Price Tag */
.price-tag {
    display: inline-flex;
    align-items: baseline;
    gap: 12px;
    background: white;
    padding: 16px 32px;
    border-radius: 16px;
    margin-bottom: 32px;
}

.price-tag .sale-price {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--espresso);
}

.price-tag .original-price {
    font-size: 1.25rem;
    color: var(--stone);
    text-decoration: line-through;
}

.price-tag .discount {
    background: var(--wa-green);
    color: white;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px 32px;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: white;
    color: var(--wa-dark);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.2);
}

.btn-whatsapp {
    background: #25D366;
    color: white;
}

.btn-whatsapp:hover {
    background: #128C7E;
}

/* Countdown Timer */
.countdown {
    display: flex;
    gap: 16px;
    margin: 24px 0;
}

.countdown-item {
    background: rgba(255,255,255,0.15);
    padding: 12px 16px;
    border-radius: 12px;
    text-align: center;
    min-width: 70px;
}

.countdown-item .number {
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    display: block;
}

.countdown-item .label {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.8);
    text-transform: uppercase;
}

/* Features Section */
.features {
    padding: 100px 40px;
    background: white;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card {
    padding: 32px;
    border: 1px solid var(--mist);
    border-radius: 20px;
    transition: all 0.3s ease;
}

.feature-card:hover {
    border-color: var(--wa-green);
    transform: translateY(-4px);
}

.feature-icon {
    width: 56px;
    height: 56px;
    background: var(--wa-light);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 20px;
}

.feature-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 12px;
}

.feature-card p {
    color: var(--stone);
}

/* Templates Section */
.templates {
    padding: 100px 40px;
    background: var(--cream);
}

.section-header {
    text-align: center;
    max-width: 800px;
    margin: 0 auto 60px;
}

.section-header h2 {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    margin-bottom: 16px;
}

.section-header p {
    color: var(--stone);
    font-size: 1.125rem;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    max-width: 1200px;
    margin: 0 auto;
}

.template-card {
    background: white;
    padding: 24px;
    border-radius: 16px;
    border: 1px solid var(--mist);
}

.template-card .number {
    width: 40px;
    height: 40px;
    background: var(--wa-green);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-bottom: 16px;
}

.template-card h4 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.template-card p {
    font-size: 0.875rem;
    color: var(--stone);
}

/* Testimonials */
.testimonials {
    padding: 100px 40px;
    background: var(--wa-dark);
    color: white;
}

.testimonial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.testimonial-card {
    background: rgba(255,255,255,0.1);
    padding: 32px;
    border-radius: 20px;
}

.testimonial-card .quote {
    font-size: 1.125rem;
    font-style: italic;
    margin-bottom: 24px;
    line-height: 1.8;
}

.testimonial-card .author {
    font-weight: 600;
}

/* CTA Section */
.cta {
    padding: 100px 40px;
    background: white;
    text-align: center;
}

.cta-box {
    max-width: 800px;
    margin: 0 auto;
    background: linear-gradient(135deg, var(--wa-green) 0%, var(--wa-dark) 100%);
    padding: 60px;
    border-radius: 32px;
    color: white;
}

.cta-box h2 {
    font-size: clamp(1.75rem, 3vw, 2.5rem);
    font-weight: 800;
    margin-bottom: 16px;
}

.cta-box p {
    font-size: 1.125rem;
    opacity: 0.9;
    margin-bottom: 32px;
}

.cta-box .price-display {
    font-size: 3rem;
    font-weight: 800;
    margin: 24px 0;
}

.cta-box .price-display span {
    font-size: 1.5rem;
    opacity: 0.8;
    text-decoration: line-through;
}

/* Footer */
.footer {
    padding: 40px;
    text-align: center;
    color: var(--stone);
    font-size: 0.875rem;
}

.footer a {
    color: var(--wa-dark);
    text-decoration: none;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-content {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-text h1 {
        font-size: 2rem;
    }
    
    .hero-visual {
        order: -1;
    }
    
    .countdown {
        justify-content: center;
    }
}
</style>

<!-- Countdown Timer Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const offerEnd = new Date();
    offerEnd.setDate(offerEnd.getDate() + 3);
    
    function updateCountdown() {
        const now = new Date();
        const diff = offerEnd - now;
        
        if (diff <= 0) {
            document.getElementById('countdown').innerHTML = '<p>Offer expired!</p>';
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);
        
        document.getElementById('hours').textContent = days * 24 + hours;
        document.getElementById('mins').textContent = mins;
        document.getElementById('secs').textContent = secs;
    }
    
    setInterval(updateCountdown, 1000);
    updateCountdown();
});
</script>

<!-- Navbar -->
<nav class="navbar">
    <a href="/" style="color: var(--espresso); font-weight: 700; text-decoration: none;">
        ← Back to JoAla
    </a>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-text">
            <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; font-size: 0.875rem; color: white; font-weight: 600;">
                📱 NEW PRODUCT
            </span>
            <h1 class="clash-display" style="margin-top: 16px;">
                WhatsApp Marketing<br>Bundle
            </h1>
            <p class="tagline">
                48 ready-to-send templates for your WhatsApp Business. 
                Broadcast sequences, auto-replies, status templates & more.
            </p>
            
            <div class="price-tag">
                <span class="sale-price">₦8,000</span>
                <span class="original-price">₦15,000</span>
                <span class="discount">-47%</span>
            </div>
            
            <a href="/checkout/whatsapp-marketing-bundle" class="btn btn-primary" style="margin-right: 12px;">
                Buy Now - ₦8,000
            </a>
            
            <div class="countdown" id="countdown">
                <div class="countdown-item">
                    <span class="number" id="hours">00</span>
                    <span class="label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span class="number" id="mins">00</span>
                    <span class="label">Mins</span>
                </div>
                <div class="countdown-item">
                    <span class="number" id="secs">00</span>
                    <span class="label">Secs</span>
                </div>
            </div>
            
            <p style="color: rgba(255,255,255,0.8); font-size: 0.875rem;">
                🔥 24 people purchased in the last 7 days
            </p>
        </div>
        
        <div class="hero-visual">
            <img src="/uploads/products/whatsapp-marketing-bundle-cover.svg" alt="WhatsApp Marketing Bundle">
        </div>
    </div>
</section>

<!-- Features -->
<section class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">📣</div>
            <h3>Broadcast Sequences</h3>
            <p>12 templates for promotional broadcasts, new product launches, flash sales, and customer re-engagement.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">🤖</div>
            <h3>Auto-Replies</h3>
            <p>8 automated response templates for pricing, orders, shipping, refunds, and after-hours.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Status Templates</h3>
            <p>10 WhatsApp Status updates for new arrivals, testimonials, countdowns, and behind-the-scenes.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">💬</div>
            <h3>Chatbot Flows</h3>
            <p>6 chatbot conversation flows for leads, orders, appointments, and exit intent.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">📦</div>
            <h3>Order Fulfillment</h3>
            <p>6 templates for order confirmation, shipping, delivery, and follow-up sequences.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">⚙️</div>
            <h3>Business Profile Guide</h3>
            <p>Complete setup guide for WhatsApp Business profile, labels, catalog, and quick replies.</p>
        </div>
    </div>
</section>

<!-- Templates Preview -->
<section class="templates">
    <div class="section-header">
        <h2 class="clash-display">What's Inside?</h2>
        <p>Everything you need to automate your WhatsApp marketing</p>
    </div>
    
    <div class="template-grid">
        <div class="template-card">
            <div class="number">1</div>
            <h4>New Product Launch</h4>
            <p>Announce new products with features and bonuses</p>
        </div>
        <div class="template-card">
            <div class="number">2</div>
            <h4>Flash Sale Alert</h4>
            <p>Create urgency with limited-time offers</p>
        </div>
        <div class="template-card">
            <div class="number">3</div>
            <h4>Cart Abandonment</h4>
            <p>Recover lost sales with gentle reminders</p>
        </div>
        <div class="template-card">
            <div class="number">4</div>
            <h4>Customer Testimonial</h4>
            <p>Share social proof with reviews</p>
        </div>
        <div class="template-card">
            <div class="number">5</div>
            <h4>Pricing Auto-Reply</h4>
            <p>Instant pricing information</p>
        </div>
        <div class="template-card">
            <div class="number">6</div>
            <h4>Order Tracking</h4>
            <p>Auto-reply with tracking info</p>
        </div>
        <div class="template-card">
            <div class="number">7</div>
            <h4>Status Countdown</h4>
            <p>Build anticipation for launches</p>
        </div>
        <div class="template-card">
            <div class="number">8</div>
            <h4>Chatbot Greeting</h4>
            <p>Interactive welcome flow</p>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <div class="section-header" style="text-align: center;">
        <h2 class="clash-display">Loved by Nigerian Businesses</h2>
    </div>
    
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <p class="quote">"Used the broadcast templates for my boutique. Recovered ₦85,000 in 2 days from cart abandonment messages!"</p>
            <p class="author">— Adaeze, Lagos</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"The auto-replies saved me so much time. Customers get instant answers 24/7. Game changer!"</p>
            <p class="author">— Chidi, Abuja</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"Already recommended to 3 other traders. The business profile guide alone is worth the price."</p>
            <p class="author">— Mercy, Port Harcourt</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta">
    <div class="cta-box">
        <h2 class="clash-display">Start WhatsApp Marketing Today</h2>
        <p>Get all 48 templates instantly. Use them forever. No monthly fees.</p>
        
        <div class="price-display">
            ₦8,000 <span>₦15,000</span>
        </div>
        
        <a href="/checkout/whatsapp-marketing-bundle" class="btn" style="background: white; color: var(--wa-dark);">
            Get Instant Access →
        </a>
        
        <p style="margin-top: 24px; font-size: 0.875rem; opacity: 0.8;">
            🔒 Secure payment via Paystack<br>
            7-day money-back guarantee
        </p>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <p>© 2026 JoAla Ventures | <a href="/">Home</a> | <a href="/store">Store</a> | <a href="/contact">Contact</a></p>
</footer>

@endsection