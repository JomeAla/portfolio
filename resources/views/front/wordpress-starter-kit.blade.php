@extends('layouts.app')

@section('title', 'WordPress Starter Kit — Build Your Dream Website in Minutes')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
--bg:#0a0a0a;
--surface:rgba(255,255,255,0.04);
--border:rgba(255,255,255,0.08);
--text:#fafafa;
--muted:#71717a;
--accent:#e11d48;
--accent2:#f97316;
--accent3:#06b6d4;
}
html{scroll-behavior:smooth}
body{
font-family:'Instrument Sans',system-ui,sans-serif;
background:var(--bg);
color:var(--text);
line-height:1.6;
overflow-x:hidden;
}
h1,h2,h3{font-family:'Instrument Serif',Georgia,serif;letter-spacing:-0.02em}
.sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}
.font-serif{font-family:'Instrument Serif',Georgia,serif}

/* grain overlay */
.grain::before{
content:'';
position:fixed;inset:0;
background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.4'/%3E%3C/svg%3E");
opacity:.03;
pointer-events:none;z-index:999;
}

/* container */
.container{max-width:1200px;margin:0 auto;padding:0 24px}

/* nav */
.nav{
position:fixed;top:24px;left:50%;transform:translateX(-50%);
z-index:100;
background:rgba(10,10,10,0.8);
backdrop-filter:blur(20px);
-webkit-backdrop-filter:blur(20px);
border:1px solid var(--border);
border-radius:100px;
padding:10px 24px;
transition:all .7s cubic-bezier(0.32,0.72,0,1);
}
.nav.scrolled{top:12px;border-radius:16px}
.nav-inner{display:flex;align-items:center;gap:16px}
.nav-logo{color:var(--text);font-weight:600;font-size:.95rem;text-decoration:none;display:flex;align-items:center;gap:8px}
.nav-cta{
background:var(--accent);color:white;border:none;
border-radius:100px;padding:10px 20px;font-size:.85rem;font-weight:600;
cursor:pointer;text-decoration:none;
transition:all .4s cubic-bezier(0.32,0.72,0,1);
}
.nav-cta:hover{background:#be123c;transform:scale(1.03)}

/* hero */
.hero{
min-height:100dvh;
display:flex;align-items:center;
padding:140px 0 80px;
position:relative;
overflow:hidden;
}
.hero-glow{
position:absolute;
top:-20%;right:-10%;
width:700px;height:700px;
background:radial-gradient(ellipse,rgba(225,29,72,0.12) 0%,transparent 65%);
pointer-events:none;
}
.hero-glow2{
position:absolute;
bottom:-20%;left:-10%;
width:500px;height:500px;
background:radial-gradient(ellipse,rgba(6,182,212,0.08) 0%,transparent 65%);
pointer-events:none;
}
.hero-grid{
display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;
position:relative;z-index:1;
}
.hero-eyebrow{
display:inline-flex;align-items:center;gap:8px;
background:var(--surface);border:1px solid var(--border);
border-radius:100px;padding:6px 14px 6px 8px;
font-size:.8rem;font-weight:500;letter-spacing:.05em;text-transform:uppercase;
margin-bottom:28px;
}
.hero-dot{width:8px;height:8px;background:#22c55e;border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hero h1{font-size:clamp(2.8rem,5.5vw,4.5rem);line-height:1.08;margin-bottom:24px}
.hero-sub{font-size:1.15rem;color:var(--muted);max-width:480px;line-height:1.7;margin-bottom:40px}

.price-block{
background:var(--surface);border:1px solid var(--border);
border-radius:20px;padding:20px 28px;display:inline-flex;
align-items:center;gap:16px;margin-bottom:32px;
}
.price-current{font-size:2.5rem;font-weight:700;letter-spacing:-.02em}
.price-orig{font-size:1.2rem;color:var(--muted);text-decoration:line-through}
.price-badge{
background:var(--accent);color:white;padding:4px 12px;
border-radius:8px;font-size:.8rem;font-weight:700;
}

.cta-group{display:flex;flex-direction:column;gap:16px}
.btn-primary{
display:inline-flex;align-items:center;justify-content:center;gap:12px;
background:var(--accent);color:white;border:none;
border-radius:16px;padding:20px 36px;
font-size:1.05rem;font-weight:600;cursor:pointer;
text-decoration:none;
transition:all .5s cubic-bezier(0.32,0.72,0,1);
position:relative;overflow:hidden;
}
.btn-primary::after{
content:'';position:absolute;inset:0;
background:linear-gradient(135deg,rgba(255,255,255,.15) 0%,transparent 60%);
}
.btn-primary:hover{background:#be123c;transform:translateY(-2px);box-shadow:0 20px 40px rgba(225,29,72,.35)}
.btn-primary:active{transform:scale(.98)}
.btn-primary .btn-arrow{
width:40px;height:40px;
background:rgba(255,255,255,.15);
border-radius:12px;
display:flex;align-items:center;justify-content:center;
font-size:1.1rem;
transition:all .4s cubic-bezier(0.32,0.72,0,1);
}
.btn-primary:hover .btn-arrow{transform:translate(3px,-2px) scale(1.08)}

.countdown-bar{
display:flex;align-items:center;gap:12px;margin-top:16px;
}
.countdown-label{font-size:.8rem;color:var(--accent2);font-weight:600;white-space:nowrap}
.countdown-timer{display:flex;gap:6px}
.c-t{
background:rgba(249,115,22,.12);border:1px solid rgba(249,115,22,.25);
border-radius:10px;padding:10px 14px;text-align:center;min-width:62px;
}
.c-t .num{font-size:1.5rem;font-weight:700;color:var(--accent2);display:block;line-height:1}
.c-t .lbl{font-size:.65rem;color:rgba(249,115,22,.7);text-transform:uppercase;letter-spacing:.1em;margin-top:2px;display:block}

.stats-row{
display:flex;gap:40px;margin-top:48px;padding-top:32px;
border-top:1px solid var(--border);
}
.stats-item{}
.stats-item .val{font-size:1.8rem;font-weight:700;color:var(--accent3)}
.stats-item .lbl{font-size:.8rem;color:var(--muted);margin-top:4px}

.hero-right{position:relative}
.hero-image-wrap{
border-radius:28px;overflow:hidden;
border:1px solid var(--border);
aspect-ratio:4/3;
position:relative;
}
.hero-image-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.hero-image-wrap::after{
content:'';position:absolute;inset:0;
background:linear-gradient(180deg,transparent 50%,rgba(10,10,10,.4) 100%);
}
.hero-badge-float{
position:absolute;bottom:24px;left:24px;
background:rgba(10,10,10,.85);backdrop-filter:blur(10px);
border:1px solid var(--border);border-radius:14px;
padding:14px 20px;
}
.hero-badge-float .badge-num{font-size:1.6rem;font-weight:700;color:var(--accent2)}
.hero-badge-float .badge-lbl{font-size:.75rem;color:var(--muted);margin-top:2px}

/* section base */
.section{padding:120px 0}
.section-label{
display:inline-flex;align-items:center;gap:8px;
background:rgba(6,182,212,.1);border:1px solid rgba(6,182,212,.2);
border-radius:100px;padding:5px 14px;
font-size:.75rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;
color:var(--accent3);margin-bottom:24px;
}
.section h2{font-size:clamp(2rem,4vw,3.2rem);margin-bottom:16px;line-height:1.1}
.section-sub{font-size:1.1rem;color:var(--muted);max-width:560px;line-height:1.7}

/* problems */
.problems-section{background:rgba(255,255,255,.02)}
.problems-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:60px}
.problem-card{
background:var(--surface);border:1px solid var(--border);
border-radius:24px;padding:36px 28px;
transition:all .6s cubic-bezier(0.32,0.72,0,1);
}
.problem-card:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15);transform:translateY(-4px)}
.problem-icon{
width:52px;height:52px;border-radius:14px;
display:flex;align-items:center;justify-content:center;
font-size:1.6rem;margin-bottom:20px;
}
.problem-card:nth-child(1) .problem-icon{background:rgba(239,68,68,.12)}
.problem-card:nth-child(2) .problem-icon{background:rgba(249,115,22,.12)}
.problem-card:nth-child(3) .problem-icon{background:rgba(245,158,11,.12)}
.problem-card h3{font-size:1.15rem;margin-bottom:10px;font-family:'Instrument Sans',system-ui,sans-serif;font-weight:600}
.problem-card p{font-size:.95rem;color:var(--muted);line-height:1.6}

/* features */
.features-section{background:var(--bg)}
.features-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:60px}
.feature-card{
background:var(--surface);border:1px solid var(--border);
border-radius:20px;padding:28px 28px;
display:flex;gap:20px;align-items:flex-start;
transition:all .6s cubic-bezier(0.32,0.72,0,1);
}
.feature-card:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15)}
.feature-check{
width:36px;height:36px;flex-shrink:0;
background:rgba(34,197,94,.12);border-radius:10px;
display:flex;align-items:center;justify-content:center;
font-size:1.1rem;
}
.feature-card h4{font-size:1rem;font-weight:600;margin-bottom:6px;font-family:'Instrument Sans',system-ui,sans-serif}
.feature-card p{font-size:.88rem;color:var(--muted);line-height:1.6}

/* testimonials */
.testimonials-section{background:rgba(255,255,255,.02)}
.testimonials-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:60px}
.testimonial-card{
background:var(--surface);border:1px solid var(--border);
border-radius:20px;padding:32px 28px;
}
.testimonial-stars{color:#f59e0b;font-size:1.1rem;margin-bottom:16px;letter-spacing:2px}
.testimonial-text{font-size:.95rem;color:var(--text);line-height:1.7;margin-bottom:20px;font-style:italic}
.testimonial-author{display:flex;align-items:center;gap:12px}
.testimonial-avatar{width:40px;height:40px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;color:white}
.testimonial-name{font-size:.9rem;font-weight:600}
.testimonial-role{font-size:.8rem;color:var(--muted)}

/* bonuses */
.bonus-section{background:linear-gradient(180deg,rgba(6,182,212,.04) 0%,transparent 100%)}
.bonus-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:60px}
.bonus-card{
background:rgba(6,182,212,.05);border:1px solid rgba(6,182,212,.15);
border-radius:24px;padding:32px;
display:flex;gap:24px;align-items:flex-start;
transition:all .6s cubic-bezier(0.32,0.72,0,1);
}
.bonus-card:hover{border-color:rgba(6,182,212,.3);transform:translateY(-3px)}
.bonus-num{
width:48px;height:48px;flex-shrink:0;
background:linear-gradient(135deg,var(--accent3),#0891b2);
border-radius:14px;display:flex;align-items:center;justify-content:center;
font-size:1.2rem;font-weight:700;
}
.bonus-card h4{font-size:1.05rem;font-weight:600;margin-bottom:8px;font-family:'Instrument Sans',system-ui,sans-serif}
.bonus-card p{font-size:.9rem;color:var(--muted);line-height:1.6}
.bonus-value{font-size:.78rem;font-weight:600;color:var(--accent3);margin-top:8px}

/* pricing */
.pricing-section{background:var(--bg);position:relative;overflow:hidden}
.pricing-glow{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:800px;height:800px;background:radial-gradient(ellipse,rgba(225,29,72,.08) 0%,transparent 65%);pointer-events:none}
.pricing-card{
max-width:680px;margin:60px auto 0;
background:rgba(255,255,255,.03);border:1px solid var(--border);
border-radius:32px;padding:56px;
text-align:center;position:relative;z-index:1;
}
.pricing-badge{
display:inline-flex;align-items:center;gap:8px;
background:rgba(249,115,22,.15);border:1px solid rgba(249,115,22,.3);
border-radius:100px;padding:6px 16px;
font-size:.82rem;font-weight:600;color:var(--accent2);margin-bottom:20px;
}
.pricing-card h3{font-size:2rem;margin-bottom:8px}
.pricing-price{margin:24px 0}
.pricing-price .strike{font-size:1.6rem;opacity:.4;text-decoration:line-through;margin-right:12px}
.pricing-price .amount{font-size:4rem;font-weight:700;letter-spacing:-.03em}
.pricing-features{
background:var(--surface);border:1px solid var(--border);
border-radius:18px;padding:28px;margin:28px 0;text-align:left;
}
.pricing-features ul{list-style:none;display:flex;flex-direction:column;gap:14px}
.pricing-features li{display:flex;align-items:center;gap:12px;font-size:.95rem}
.pricing-features li .fi{color:#22c55e;font-size:1.1rem}
.pricing-features li .lbl{color:var(--muted);font-size:.8rem;margin-left:auto;background:rgba(255,255,255,.05);padding:2px 8px;border-radius:6px}

.pricing-cta{margin-top:28px}
.btn-large{
display:inline-flex;align-items:center;justify-content:center;gap:14px;
background:var(--accent);color:white;border:none;
border-radius:18px;padding:24px 48px;
font-size:1.15rem;font-weight:700;cursor:pointer;
text-decoration:none;
width:100%;
transition:all .5s cubic-bezier(0.32,0.72,0,1);
}
.btn-large:hover{background:#be123c;transform:translateY(-2px);box-shadow:0 24px 48px rgba(225,29,72,.4)}
.btn-large:active{transform:scale(.98)}
.btn-large .btn-arrow{transition:all .4s cubic-bezier(0.32,0.72,0,1)}
.btn-large:hover .btn-arrow{transform:translate(4px,-2px)}

.pricing-countdown{margin-top:24px;display:flex;flex-direction:column;align-items:center;gap:12px}
.pricing-countdown .timer-row{display:flex;gap:8px}
.pricing-countdown .c-t{
background:rgba(249,115,22,.1);border:1px solid rgba(249,115,22,.2);
}
.pricing-countdown .urgency-msg{font-size:.82rem;color:var(--accent2);font-weight:600}
.pricing-guarantee{font-size:.8rem;color:var(--muted);margin-top:16px}

/* cta section */
.cta-bottom-section{background:rgba(255,255,255,.02);text-align:center}
.cta-bottom-section h2{font-size:clamp(2rem,4vw,3rem);margin-bottom:16px}
.cta-bottom-section p{color:var(--muted);font-size:1.05rem;margin-bottom:40px}
.cta-bottom-countdown{
display:flex;align-items:center;justify-content:center;gap:12px;margin-top:24px;
}
.cta-bottom-countdown .c-t{
background:rgba(6,182,212,.08);border:1px solid rgba(6,182,212,.2);
}

/* footer */
.footer{padding:60px 0 40px;background:var(--bg);border-top:1px solid var(--border)}
.footer-inner{display:flex;justify-content:space-between;align-items:center}
.footer-brand{font-size:.95rem;font-weight:600}
.footer-links{display:flex;gap:24px}
.footer-links a{color:var(--muted);text-decoration:none;font-size:.85rem;transition:color .3s}
.footer-links a:hover{color:var(--text)}

/* scroll reveal */
.reveal{opacity:0;transform:translateY(30px);transition:all .8s cubic-bezier(0.32,0.72,0,1)}
.reveal.visible{opacity:1;transform:translateY(0)}

/* mobile */
@media(max-width:900px){
.hero-grid{grid-template-columns:1fr;gap:48px;text-align:center}
.hero-right{order:-1}
.hero-sub{margin:0 auto 40px}
.price-block{margin:0 auto 32px}
.cta-group{width:100%;max-width:400px;margin:0 auto}
.stats-row{justify-content:center;gap:32px}
.problems-grid,.features-grid,.testimonials-grid,.bonus-grid{grid-template-columns:1fr}
.footer-inner{flex-direction:column;gap:20px;text-align:center}
.hero-image-wrap{aspect-ratio:16/9}
.pricing-card{padding:32px}
.pricing-price .amount{font-size:3rem}
}
@media(max-width:600px){
.container{padding:0 16px}
.section{padding:80px 0}
.btn-primary,.btn-large{padding:16px 28px;font-size:1rem}
.nav{padding:8px 16px;top:12px}
}
</style>
@endpush

@section('content')

<nav class="nav" id="mainNav">
    <div class="nav-inner">
        <a href="/" class="nav-logo">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><rect width="20" height="20" rx="6" fill="#e11d48"/><path d="M6 14l4-8 4 8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Joala Digital
        </a>
        <a href="/wordpress-checkout.php" class="nav-cta">Get Started</a>
    </div>
</nav>

<section class="hero grain">
    <div class="hero-glow"></div>
    <div class="hero-glow2"></div>
    <div class="container">
        <div class="hero-grid">
            <div class="hero-text">
                <div class="hero-eyebrow">
                    <span class="hero-dot"></span>
                    Instant Digital Download
                </div>
                <h1>Build Your Dream Website in Under 30 Minutes</h1>
                <p class="hero-sub">50+ professional WordPress pages, templates & plugins — everything you need to launch a conversion-ready website without hiring a developer.</p>

                <div class="price-block">
                    <span class="price-current">₦12,000</span>
                    <span class="price-orig">₦28,000</span>
                    <span class="price-badge">57% OFF</span>
                </div>

                <div class="cta-group">
                    <a href="/wordpress-checkout.php" class="btn-primary">
                        Get WordPress Starter Kit
                        <span class="btn-arrow">→</span>
                    </a>
                    <div class="countdown-bar">
                        <span class="countdown-label">⏱ Offer expires in</span>
                        <div class="countdown-timer">
                            <div class="c-t"><span class="num" id="h1">00</span><span class="lbl">Hrs</span></div>
                            <div class="c-t"><span class="num" id="m1">00</span><span class="lbl">Min</span></div>
                            <div class="c-t"><span class="num" id="s1">00</span><span class="lbl">Sec</span></div>
                        </div>
                    </div>
                </div>

                <div class="stats-row">
                    <div class="stats-item">
                        <div class="val">50+</div>
                        <div class="lbl">Pages & Templates</div>
                    </div>
                    <div class="stats-item">
                        <div class="val">20+</div>
                        <div class="lbl">Premium Plugins</div>
                    </div>
                    <div class="stats-item">
                        <div class="val">2,400+</div>
                        <div class="lbl">Happy Users</div>
                    </div>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-image-wrap">
                    <img src="/public/uploads/products/wordpress-starter-kit-cover.jpg" alt="WordPress Starter Kit" onerror="this.style.display='none'">
                </div>
                <div class="hero-badge-float">
                    <div class="badge-num">57%</div>
                    <div class="badge-lbl">Discount Applied</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section problems-section">
    <div class="container">
        <div class="section-label">The Problem</div>
        <h2>Building a WordPress Site<br>Shouldn't Be This Hard</h2>
        <p class="section-sub">Most entrepreneurs waste weeks and thousands of naira trying to get their website right. Here's why:</p>

        <div class="problems-grid">
            <div class="problem-card reveal">
                <div class="problem-icon">⏰</div>
                <h3>Months of Setup Time</h3>
                <p>Starting from scratch means hours of research, configuration, and design work before you can even publish your first page.</p>
            </div>
            <div class="problem-card reveal">
                <div class="problem-icon">💸</div>
                <h3>Huge Developer Costs</h3>
                <p>Hiring a professional WordPress developer costs ₦80,000+. Premium themes and plugins add up to thousands more.</p>
            </div>
            <div class="problem-card reveal">
                <div class="problem-icon">😰</div>
                <h3>Endless Tutorial rabbit Holes</h3>
                <p>WordPress has a steep learning curve. Too many plugins, too many options, and too many conflicting tutorials online.</p>
            </div>
        </div>
    </div>
</section>

<section class="section features-section">
    <div class="container">
        <div class="section-label">What's Inside</div>
        <h2>Everything You Need<br>to Launch Fast</h2>
        <p class="section-sub">The WordPress Starter Kit gives you a complete head start with professionally designed pages and proven tools.</p>

        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-check">✓</div>
                <div>
                    <h4>10 Pre-Built Homepage Designs</h4>
                    <p>Business, portfolio, e-commerce, agency, blog — pick a design and launch in minutes.</p>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-check">✓</div>
                <div>
                    <h4>15+ Inner Page Templates</h4>
                    <p>About, services, contact, pricing, FAQ, team, careers — all professionally designed.</p>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-check">✓</div>
                <div>
                    <h4>20+ Premium Plugins Included</h4>
                    <p>SEO, forms, security, speed, analytics — pre-configured and ready to activate.</p>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-check">✓</div>
                <div>
                    <h4>Conversion-Ready Sections</h4>
                    <p>Hero banners, CTAs, testimonials, pricing tables — all tested for maximum conversions.</p>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-check">✓</div>
                <div>
                    <h4>WooCommerce Ready</h4>
                    <p>Start selling products or services immediately with our pre-built shop templates.</p>
                </div>
            </div>
            <div class="feature-card reveal">
                <div class="feature-check">✓</div>
                <div>
                    <h4>Step-by-Step Setup Guide</h4>
                    <p>From domain purchase to launch — a complete walkthrough in plain English.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section testimonials-section">
    <div class="container">
        <div class="section-label">Social Proof</div>
        <h2>Loved by 2,400+<br>Entrepreneurs</h2>
        <p class="section-sub">Real results from real users who've launched their websites with the Starter Kit.</p>

        <div class="testimonials-grid">
            <div class="testimonial-card reveal">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text">"I launched my coaching website in one afternoon. The templates are incredibly professional — my clients always compliment the design."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">AO</div>
                    <div>
                        <div class="testimonial-name">Adaeze O.</div>
                        <div class="testimonial-role">Life Coach, Lagos</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card reveal">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text">"Saved me over ₦150,000 in developer fees. The kit had everything I needed. Set up my e-commerce store in under 2 hours."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">CE</div>
                    <div>
                        <div class="testimonial-name">Chidi E.</div>
                        <div class="testimonial-role">Online Seller, Abuja</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card reveal">
                <div class="testimonial-stars">★★★★★</div>
                <p class="testimonial-text">"The step-by-step guide is brilliant. I'm not tech-savvy at all, but the instructions made everything so simple."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">TM</div>
                    <div>
                        <div class="testimonial-name">Tunde M.</div>
                        <div class="testimonial-role">Freelance Designer, PH</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bonus-section">
    <div class="container">
        <div class="section-label">🎁 Bonus Packs</div>
        <h2>Get Even More<br>Value Free</h2>
        <p class="section-sub">Every purchase includes these powerful bonus tools worth over ₦15,000.</p>

        <div class="bonus-grid">
            <div class="bonus-card reveal">
                <div class="bonus-num">1</div>
                <div>
                    <h4>Email Marketing Templates</h4>
                    <p>10 high-converting email sequences — welcome, follow-up, promotional and cart recovery templates.</p>
                    <div class="bonus-value">Worth ₦5,000</div>
                </div>
            </div>
            <div class="bonus-card reveal">
                <div class="bonus-num">2</div>
                <div>
                    <h4>Lead Capture Bundle</h4>
                    <p>Popup forms, landing pages & opt-in boxes designed to grow your email list fast.</p>
                    <div class="bonus-value">Worth ₦3,000</div>
                </div>
            </div>
            <div class="bonus-card reveal">
                <div class="bonus-num">3</div>
                <div>
                    <h4>90-Day Content Calendar</h4>
                    <p>270 blog post ideas organized in a 90-day content plan with SEO keywords included.</p>
                    <div class="bonus-value">Worth ₦4,000</div>
                </div>
            </div>
            <div class="bonus-card reveal">
                <div class="bonus-num">4</div>
                <div>
                    <h4>Speed Optimization Guide</h4>
                    <p>Make your WordPress site load in under 2 seconds with these proven techniques.</p>
                    <div class="bonus-value">Worth ₦3,000</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section pricing-section">
    <div class="pricing-glow"></div>
    <div class="container">
        <div class="section-label">Limited Time Offer</div>
        <h2>Get Everything<br>for One Price</h2>
        <p class="section-sub">One-time payment. Lifetime access. No recurring fees.</p>

        <div class="pricing-card reveal">
            <div class="pricing-badge">★ BEST VALUE</div>
            <h3>WordPress Starter Kit</h3>
            <div class="pricing-price">
                <span class="strike">₦28,000</span>
                <span class="amount">₦12,000</span>
            </div>
            <div class="pricing-features">
                <ul>
                    <li><span class="fi">✓</span> 50+ Page Templates <span class="lbl">Worth ₦50,000</span></li>
                    <li><span class="fi">✓</span> 20+ Premium Plugins <span class="lbl">Worth ₦30,000</span></li>
                    <li><span class="fi">✓</span> WooCommerce Ready <span class="lbl">Worth ₦15,000</span></li>
                    <li><span class="fi">✓</span> Step-by-Step Setup Guide <span class="lbl">Worth ₦10,000</span></li>
                    <li><span class="fi">✓</span> All 4 Bonus Packs <span class="lbl">Worth ₦15,000</span></li>
                    <li><span class="fi">✓</span> Lifetime Free Updates <span class="lbl">Worth Priceless</span></li>
                    <li><span class="fi">✓</span> Instant Digital Delivery</li>
                </ul>
            </div>

            <div class="pricing-cta">
                <a href="/wordpress-checkout.php" class="btn-large">
                    Get Instant Access Now
                    <span class="btn-arrow">→</span>
                </a>
                <div class="pricing-countdown">
                    <div class="timer-row">
                        <div class="c-t"><span class="num" id="h2">00</span><span class="lbl">Hrs</span></div>
                        <div class="c-t"><span class="num" id="m2">00</span><span class="lbl">Min</span></div>
                        <div class="c-t"><span class="num" id="s2">00</span><span class="lbl">Sec</span></div>
                    </div>
                    <span class="urgency-msg">⏱ Offer expires in the next 24 hours</span>
                    <span class="pricing-guarantee">🔒 30-day money-back guarantee — no questions asked</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section cta-bottom-section">
    <div class="container">
        <h2>Ready to Launch Your<br>Dream Website?</h2>
        <p>Join 2,400+ entrepreneurs who've already built their sites with the Starter Kit.</p>

        <a href="/wordpress-checkout.php" class="btn-primary" style="margin:0 auto;width:max-content;">
            Get Started for ₦12,000
            <span class="btn-arrow">→</span>
        </a>

        <div class="cta-bottom-countdown">
            <span class="countdown-label">⏱ Offer expires in</span>
            <div class="countdown-timer">
                <div class="c-t"><span class="num" id="h3">00</span><span class="lbl">Hrs</span></div>
                <div class="c-t"><span class="num" id="m3">00</span><span class="lbl">Min</span></div>
                <div class="c-t"><span class="num" id="s3">00</span><span class="lbl">Sec</span></div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand">© 2024 Joala Digital. All rights reserved.</div>
            <div class="footer-links">
                <a href="/privacy">Privacy Policy</a>
                <a href="/refund">Refund Policy</a>
                <a href="/contact">Contact Support</a>
            </div>
        </div>
    </div>
</footer>

<script>
// 24hr countdown timer — resets every day at midnight
function getResetTime() {
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(0, 0, 0, 0);
    return tomorrow;
}

function tick() {
    const now = new Date();
    const end = getResetTime();
    const diff = Math.max(0, end - now);

    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);

    const pad = n => String(n).padStart(2, '0');

    [['h1','m1','s1'],['h2','m2','s2'],['h3','m3','s3']].forEach(([hh,mm,ss]) => {
        const he = document.getElementById(hh), me = document.getElementById(mm), se = document.getElementById(ss);
        if(he) he.textContent = pad(h);
        if(me) me.textContent = pad(m);
        if(se) se.textContent = pad(s);
    });

    requestAnimationFrame(tick);
}
tick();

// nav scroll effect
window.addEventListener('scroll', () => {
    const nav = document.getElementById('mainNav');
    nav.classList.toggle('scrolled', window.scrollY > 60);
}, { passive: true });

// scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
            setTimeout(() => entry.target.classList.add('visible'), i * 100);
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.15 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
@endsection