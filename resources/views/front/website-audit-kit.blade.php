@extends('layouts.app')

@section('title', 'Website Audit Kit - 20-Point Checklist')

@section('content')
<style>
:root { --blue: #3b82f6; --blue-dark: #1e40af; --blue-light: #dbeafe; --cream: #FDFBF7; --espresso: #1a1410; --stone: #6b6560; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: system-ui, sans-serif; background: var(--cream); color: var(--espresso); }

.hero { min-height: 100vh; background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%); color: white; display: flex; align-items: center; padding: 100px 40px; }
.hero-content { max-width: 1200px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.hero-text h1 { font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; margin-bottom: 16px; }
.hero-text .tagline { font-size: 1.2rem; opacity: 0.9; margin-bottom: 24px; }
.hero img { width: 100%; border-radius: 20px; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }

.price-tag { background: white; padding: 14px 24px; border-radius: 12px; display: inline-flex; align-items: baseline; gap: 10px; margin-bottom: 20px; }
.price-tag .sale { font-size: 1.8rem; font-weight: 700; color: var(--espresso); }
.price-tag .original { font-size: 1rem; color: var(--stone); text-decoration: line-through; }
.price-tag .badge { background: #22c55e; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }

.btn { display: inline-block; padding: 14px 28px; border-radius: 10px; font-weight: 600; text-decoration: none; }
.btn-white { background: white; color: var(--blue-dark); }
.btn-white:hover { transform: translateY(-2px); }

.features { padding: 80px 40px; background: white; }
.features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; max-width: 1100px; margin: 0 auto; }
.feature-card { padding: 24px; border: 1px solid #e5e7eb; border-radius: 14px; }
.feature-icon { width: 46px; height: 46px; background: var(--blue-light); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 14px; }
.feature-card h3 { font-size: 1.1rem; margin-bottom: 6px; }
.feature-card p { color: var(--stone); font-size: 0.9rem; }

.checklist { padding: 80px 40px; background: var(--blue-light); }
.section-header { text-align: center; max-width: 650px; margin: 0 auto 45px; }
.checklist-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; max-width: 1000px; margin: 0 auto; }
.checklist-item { background: white; padding: 16px; border-radius: 12px; border-left: 4px solid var(--blue); }
.checklist-item.critical { border-color: #dc2626; }
.checklist-item.high { border-color: #ea580c; }

.testimonials { padding: 80px 40px; background: var(--blue-dark); color: white; }
.testimonial-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; max-width: 1000px; margin: 0 auto; }
.testimonial-card { background: rgba(255,255,255,0.1); padding: 24px; border-radius: 14px; }
.testimonial-card .quote { font-style: italic; margin-bottom: 16px; }

.upsell { padding: 80px 40px; background: #fef3c7; text-align: center; }
.upsell-box { max-width: 700px; margin: 0 auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
.upsell-box h3 { font-size: 1.5rem; margin-bottom: 12px; color: #92400e; }

.cta { padding: 80px 40px; background: white; text-align: center; }
.cta-box { max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%); padding: 45px; border-radius: 20px; color: white; }
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

<nav style="position:fixed;top:18px;left:50%;transform:translateX(-50%);z-index:100;background:rgba(255,255,255,0.9);padding:10px 22px;border-radius:100px;">
    <a href="/" style="color:#1a1410;font-weight:700;text-decoration:none;">← Back</a>
</nav>

<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <span style="background:rgba(255,255,255,0.2);padding:8px 16px;border-radius:8px;font-size:0.9rem;font-weight:600;">📋 NEW</span>
            <h1 style="margin-top:12px;">Website Audit Kit</h1>
            <p class="tagline">Complete 20-point checklist to find what's wrong with your website and fix it fast.</p>
            
            <div class="price-tag">
                <span class="sale">₦5,000</span>
                <span class="badge">Limited Time</span>
            </div>
            
            <a href="/checkout/website-audit-kit" class="btn btn-white">Get Audit Kit Now</a>
            
            <p style="font-size:0.9rem;opacity:0.8;margin-top:16px;">🎯 Use results to get full fixes → ₦150k+</p>
        </div>
        
        <img src="/uploads/products/website-audit-kit-cover.svg" alt="Website Audit Kit">
    </div>
</section>

<section class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>20-Point Checklist</h3>
            <p>Complete checklist covering technical, UX, SEO, security, content & conversion</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎯</div>
            <h3>Priority Scoring</h3>
            <p>Score each item as Critical/High/Medium to know what to fix first</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📝</div>
            <h3>Action Plan Template</h3>
            <p>Built-in template to create your prioritized fix plan</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3>Quick Wins Section</h3>
            <p>10 items you can fix TODAY without any technical skills</p>
        </div>
    </div>
</section>

<section class="checklist">
    <div class="section-header">
        <h2 style="color:var(--blue-dark);">What You'll Audit</h2>
        <p>6 categories, 20 checklist items, infinite improvements</p>
    </div>
    
    <div class="checklist-grid">
        <div class="checklist-item critical">
            <strong>🔴 Technical Performance</strong>
            <ul style="margin-top:8px;padding-left:20px;font-size:0.9rem;">
                <li>Website speed test</li>
                <li>Mobile responsiveness</li>
                <li>HTTPS security</li>
                <li>Page weight optimization</li>
            </ul>
        </div>
        <div class="checklist-item high">
            <strong>🟠 User Experience</strong>
            <ul style="margin-top:8px;padding-left:20px;font-size:0.9rem;">
                <li>Navigation clarity</li>
                <li>Page load time</li>
                <li>CTA visibility</li>
                <li>Contact accessibility</li>
            </ul>
        </div>
        <div class="checklist-item high">
            <strong>🟠 SEO Optimization</strong>
            <ul style="margin-top:8px;padding-left:20px;font-size:0.9rem;">
                <li>Meta titles & descriptions</li>
                <li>Heading structure</li>
                <li>Image alt text</li>
                <li>Sitemap</li>
            </ul>
        </div>
        <div class="checklist-item critical">
            <strong>🔴 Security</strong>
            <ul style="margin-top:8px;padding-left:20px;font-size:0.9rem;">
                <li>SSL certificate</li>
                <li>Contact forms</li>
                <li>Update status</li>
                <li>Backups</li>
            </ul>
        </div>
        <div class="checklist-item high">
            <strong>🟠 Content</strong>
            <ul style="margin-top:8px;padding-left:20px;font-size:0.9rem;">
                <li>About page</li>
                <li>Contact info</li>
                <li>Legal pages</li>
                <li>Content freshness</li>
            </ul>
        </div>
        <div class="checklist-item high">
            <strong>🟠 Conversion</strong>
            <ul style="margin-top:8px;padding-left:20px;font-size:0.9rem;">
                <li>Phone number (mobile)</li>
                <li>WhatsApp button</li>
                <li>Social links</li>
                <li>Testimonials</li>
            </ul>
        </div>
    </div>
</section>

<section class="testimonials">
    <div class="section-header" style="text-align:center;">
        <h2 style="color:white;">Results from Nigerian Businesses</h2>
    </div>
    <div class="testimonial-grid">
        <div class="testimonial-card">
            <p class="quote">"Used the checklist. Found 8 critical issues. Fixed them in 2 weeks. My Google ranking improved by 40%!"</p>
            <p>— Chidi, E-commerce Store</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"The quick wins alone saved me ₦200k. Fixed them myself in one afternoon!"</p>
            <p>— Adaeze, Fashion Boutique</p>
        </div>
        <div class="testimonial-card">
            <p class="quote">"Gave the audit to my developer. Fixed everything in one week. Best ₦5k I ever spent."</p>
            <p>— Emeka, Tech Startup</p>
        </div>
    </div>
</section>

<section class="upsell">
    <div class="upsell-box">
        <h3>🔧 Need Help Fixing Issues?</h3>
        <p style="color:#666;margin-bottom:20px;">After your audit, we'll fix everything for you.</p>
        
        <div style="background:#fff7ed;padding:20px;border-radius:12px;margin:20px 0;">
            <h4 style="color:#c2410c;margin-bottom:10px;">🔥 Full Website Fix Package</h4>
            <ul style="text-align:left;padding-left:20px;color:#666;">
                <li>✓ All critical fixes</li>
                <li>✓ Speed optimization</li>
                <li>✓ SEO improvements</li>
                <li>✓ 30-day support</li>
                <li style="font-weight:bold;color:#c2410c;">Starting from ₦150,000</li>
            </ul>
        </div>
        
        <a href="/contact" class="btn" style="background:#ea580c;color:white;">Book a Call →</a>
    </div>
</section>

<section class="cta">
    <div class="cta-box">
        <h2>Start Your Website Audit Today</h2>
        <p>Find out what's wrong with your website before your customers do.</p>
        
        <div class="price">₦5,000</div>
        
        <a href="/checkout/website-audit-kit" class="btn btn-white">Get Instant Access →</a>
        
        <p style="margin-top:18px;font-size:0.8rem;opacity:0.8;">🔒 Paystack | 7-day guarantee</p>
    </div>
</section>

<footer class="footer">
    <p>© 2026 JoAla Ventures | <a href="/">Home</a> | <a href="/store">Store</a></p>
</footer>

@endsection