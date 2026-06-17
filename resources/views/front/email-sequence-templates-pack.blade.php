@extends('layouts.app')

@section('title', 'Email Sequence Templates Pack - Professional Email Marketing Templates')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Clash+Display:wght@600;700;800&display=swap');

:root {
    --cream: #FDFBF7;
    --espresso: #1a1410;
    --sage: #4a5d52;
    --ember: #c45a3b;
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

::selection {
    background: var(--sage);
    color: white;
}

.clash-display { font-family: 'Clash Display', sans-serif; }

/* Noise overlay */
.noise-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    pointer-events: none;
    opacity: 0.03;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
}

/* Custom cursor */
.cursor {
    width: 20px;
    height: 20px;
    border: 1.5px solid var(--sage);
    border-radius: 50%;
    position: fixed;
    pointer-events: none;
    z-index: 9998;
    transition: transform 0.15s cubic-bezier(0.32, 0.72, 0, 1);
    mix-blend-mode: difference;
}

/* Navbar */
.navbar {
    position: fixed;
    top: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background: rgba(253, 251, 247, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(26, 20, 16, 0.08);
    border-radius: 100px;
    padding: 12px 24px;
    transition: all 0.5s cubic-bezier(0.32, 0.72, 0, 1);
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
}

.hero-bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(74, 93, 82, 0.15) 0%, transparent 70%);
}

.hero-content {
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--sage);
    color: white;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 100px;
    margin-bottom: 24px;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeUp 0.8s cubic-bezier(0.32, 0.72, 0, 1) 0.2s forwards;
}

.hero-title {
    font-family: 'Clash Display', sans-serif;
    font-size: clamp(48px, 6vw, 80px);
    font-weight: 700;
    line-height: 1.05;
    letter-spacing: -0.03em;
    margin-bottom: 24px;
    opacity: 0;
    transform: translateY(30px);
    animation: fadeUp 0.8s cubic-bezier(0.32, 0.72, 0, 1) 0.3s forwards;
}

.hero-title span {
    color: var(--sage);
}

.hero-subtitle {
    font-size: 18px;
    color: var(--stone);
    max-width: 480px;
    margin-bottom: 40px;
    opacity: 0;
    transform: translateY(30px);
    animation: fadeUp 0.8s cubic-bezier(0.32, 0.72, 0, 1) 0.4s forwards;
}

.hero-cta {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: var(--espresso);
    color: white;
    font-weight: 600;
    padding: 18px 32px;
    border-radius: 100px;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.32, 0.72, 0, 1);
    opacity: 0;
    transform: translateY(30px);
    animation: fadeUp 0.8s cubic-bezier(0.32, 0.72, 0, 1) 0.5s forwards;
}

.hero-cta:hover {
    transform: scale(1.02) translateY(-2px);
    box-shadow: 0 20px 40px rgba(26, 20, 16, 0.15);
}

.hero-cta svg {
    transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}

.hero-cta:hover svg {
    transform: translateX(4px);
}

.hero-price {
    margin-top: 24px;
    font-size: 14px;
    color: var(--stone);
    opacity: 0;
    animation: fadeUp 0.8s cubic-bezier(0.32, 0.72, 0, 1) 0.6s forwards;
}

.hero-price strong {
    color: var(--espresso);
    font-size: 24px;
    font-weight: 700;
}

/* Bento Grid */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 40px;
}

.bento-card {
    background: white;
    border-radius: 24px;
    padding: 32px;
    border: 1px solid rgba(26, 20, 16, 0.06);
    position: relative;
    overflow: hidden;
}

.bento-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 24px;
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.8);
    pointer-events: none;
}

.card-wide {
    grid-column: span 8;
    background: linear-gradient(135deg, var(--sage) 0%, #3d4f45 100%);
    color: white;
}

.card-tall {
    grid-column: span 4;
    grid-row: span 2;
}

.card-featured {
    grid-column: span 6;
}

.card-stat {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.stat-number {
    font-family: 'Clash Display', sans-serif;
    font-size: 64px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 14px;
    opacity: 0.8;
}

/* Section spacing */
.section {
    padding: 120px 40px;
    max-width: 1200px;
    margin: 0 auto;
}

.section-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--ember);
    margin-bottom: 16px;
}

.section-title {
    font-family: 'Clash Display', sans-serif;
    font-size: clamp(36px, 4vw, 56px);
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 24px;
}

/* Template cards */
.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 60px;
}

.template-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(26, 20, 16, 0.06);
    transition: all 0.5s cubic-bezier(0.32, 0.72, 0, 1);
}

.template-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 30px 60px rgba(26, 20, 16, 0.1);
}

.template-header {
    padding: 24px;
    border-bottom: 1px solid var(--mist);
}

.template-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

.template-body {
    padding: 24px;
}

.template-name {
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 8px;
}

.template-desc {
    font-size: 14px;
    color: var(--stone);
    line-height: 1.6;
}

/* Social proof */
.proof-section {
    background: var(--espresso);
    color: white;
    padding: 100px 40px;
    text-align: center;
}

.proof-content {
    max-width: 900px;
    margin: 0 auto;
}

.proof-quote {
    font-family: 'Clash Display', sans-serif;
    font-size: clamp(24px, 3vw, 40px);
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 40px;
}

.proof-author {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
}

.proof-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--sage);
}

.proof-info {
    text-align: left;
}

.proof-name {
    font-weight: 600;
}

.proof-title {
    font-size: 14px;
    opacity: 0.7;
}

/* Timer */
.timer-section {
    padding: 0 40px 40px;
    max-width: 1200px;
    margin: 0 auto;
}

.timer-box {
    background: linear-gradient(135deg, var(--ember) 0%, #a84e30 100%);
    border-radius: 20px;
    padding: 32px;
    color: white;
    text-align: center;
}

.timer-label {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    opacity: 0.9;
    margin-bottom: 16px;
}

.timer-display {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 16px;
}

.timer-unit {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 16px 20px;
    min-width: 80px;
    backdrop-filter: blur(10px);
}

.timer-value {
    font-family: 'Clash Display', sans-serif;
    font-size: 36px;
    font-weight: 700;
    line-height: 1;
}

.timer-unit-label {
    font-size: 11px;
    text-transform: uppercase;
    opacity: 0.8;
    margin-top: 4px;
}

.timer-offer {
    font-size: 14px;
    opacity: 0.9;
}

.timer-offer strong {
    font-weight: 700;
}

/* CTA Section */
.cta-section {
    padding: 60px 40px 80px;
    text-align: center;
}

.cta-box {
    background: white;
    border-radius: 32px;
    padding: 80px 40px;
    max-width: 700px;
    margin: 0 auto;
    border: 1px solid rgba(26, 20, 16, 0.06);
}

.cta-title {
    font-family: 'Clash Display', sans-serif;
    font-size: clamp(32px, 4vw, 48px);
    font-weight: 700;
    margin-bottom: 16px;
}

.cta-price {
    font-size: 14px;
    color: var(--stone);
    margin-bottom: 32px;
}

.cta-price span {
    font-size: 32px;
    font-weight: 700;
    color: var(--espresso);
}

.cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: var(--ember);
    color: white;
    font-weight: 700;
    font-size: 18px;
    padding: 24px 48px;
    border-radius: 100px;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.32, 0.72, 0, 1);
}

.cta-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 20px 40px rgba(196, 90, 59, 0.3);
}

.guarantee {
    margin-top: 24px;
    font-size: 13px;
    color: var(--stone);
}

/* Footer */
.footer {
    background: var(--mist);
    padding: 60px 40px 40px;
    text-align: center;
}

.footer-text {
    font-size: 14px;
    color: var(--stone);
}

/* Animations */
@keyframes fadeUp {
    to {
        opacity: 1;
        transform: translateY(0) translateX(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .hero {
        padding: 120px 24px 60px;
    }
    
    .hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .hero-title {
        font-size: 40px;
    }
    
    .bento-grid {
        grid-template-columns: 1fr;
        padding: 0 24px;
    }
    
    .card-wide, .card-tall, .card-featured {
        grid-column: span 1;
    }
    
    .section {
        padding: 80px 24px;
    }
    
    .navbar {
        padding: 10px 20px;
    }
    
    .cta-box {
        padding: 48px 24px;
    }
}
</style>

<div class="noise-overlay"></div>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <a href="/" style="font-family: 'Clash Display', sans-serif; font-weight: 700; font-size: 18px; color: var(--espresso); text-decoration: none;">JoAla</a>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-text">
            <div class="eyebrow">
                <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                24 Proven Templates
            </div>
            <h1 class="hero-title clash-display">
                Stop Writing Emails<br>from <span>Scratch</span>
            </h1>
            <p class="hero-subtitle">
                Get 24 professionally crafted email templates that generated over ₦500,000 in sales for Nigerian businesses. Ready to customize, copy, and send.
            </p>
            <a href="#pricing" class="hero-cta">
                Get Instant Access
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <p class="hero-price">Starting at <strong>₦15,000</strong> only</p>
        </div>
        <div class="hero-visual">
            <div style="position: relative;">
                <div style="background: linear-gradient(135deg, #4a5d52 0%, #3d4f45 100%); border-radius: 24px; padding: 40px; height: 400px; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 30px 60px rgba(26, 20, 16, 0.15);">
                    <div style="color: white; text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 16px;">📧</div>
                        <div style="font-family: 'Clash Display', sans-serif; font-size: 32px; font-weight: 700; margin-bottom: 12px;">Email Sequence<br>Templates Pack</div>
                        <div style="font-size: 16px; opacity: 0.9; margin-bottom: 24px;">24 Professional Templates<br>6 Complete Sequences</div>
                        <div style="background: rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 100px; display: inline-block;">
                            ₦15,000 <span style="text-decoration: line-through; opacity: 0.7;">₦35,000</span>
                        </div>
                    </div>
                </div>
                <div style="position: absolute; bottom: -20px; left: -20px; background: white; padding: 20px; border-radius: 16px; box-shadow: 0 20px 40px rgba(26, 20, 16, 0.15);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; background: var(--sage); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <svg width="24" height="24" fill="white" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 18px;">24 Templates</div>
                            <div style="font-size: 13px; color: var(--stone);">Included</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bento Stats -->
<section style="padding: 0 40px 30px;">
    <div class="bento-grid">
        <div class="bento-card card-wide">
            <div style="display: flex; gap: 40px; flex-wrap: wrap;">
                <div>
                    <div class="stat-number">68%</div>
                    <div class="stat-label">Welcome email open rate</div>
                </div>
                <div>
                    <div class="stat-number">35%</div>
                    <div class="stat-label">Cart recovery rate</div>
                </div>
                <div>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Nigerian businesses</div>
                </div>
            </div>
        </div>
        <div class="bento-card card-tall card-stat">
            <div class="stat-number">₦500K+</div>
            <div class="stat-label">Revenue generated for customers</div>
        </div>
        <div class="bento-card card-featured">
            <div style="font-size: 14px; color: var(--stone); margin-bottom: 12px;">Delivery</div>
            <div style="font-weight: 700; font-size: 20px;">Instant Download</div>
            <div style="font-size: 14px; color: var(--stone); margin-top: 8px;">Get access immediately after payment</div>
        </div>
    </div>
</section>

<!-- Templates -->
<section class="section" style="padding: 60px 40px;">
    <p class="section-eyebrow">What's Inside</p>
    <h2 class="section-title">6 Complete Email Sequences</h2>
    
    <div class="template-grid">
        <div class="template-card">
            <div class="template-header">
                <div class="template-icon" style="background: rgba(74, 93, 82, 0.1);">
                    <svg width="24" height="24" fill="none" stroke="#4a5d52" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="template-body">
                <h3 class="template-name">Welcome Series (5 emails)</h3>
                <p class="template-desc">Build relationships from day one. Introduce your brand, deliver value, and set expectations for new subscribers.</p>
            </div>
        </div>
        
        <div class="template-card">
            <div class="template-header">
                <div class="template-icon" style="background: rgba(196, 90, 59, 0.1);">
                    <svg width="24" height="24" fill="none" stroke="#c45a3b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4z"/></svg>
                </div>
            </div>
            <div class="template-body">
                <h3 class="template-name">Abandoned Cart (3 emails)</h3>
                <p class="template-desc">Gentle reminders that recover up to 35% of lost sales. Tested across 50+ Nigerian stores.</p>
            </div>
        </div>
        
        <div class="template-card">
            <div class="template-header">
                <div class="template-icon" style="background: rgba(74, 93, 82, 0.1);">
                    <svg width="24" height="24" fill="none" stroke="#4a5d52" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
            </div>
            <div class="template-body">
                <h3 class="template-name">Re-engagement (4 emails)</h3>
                <p class="template-desc">Win back inactive subscribers with the "we miss you" approach that typically sees 22% engagement.</p>
            </div>
        </div>
        
        <div class="template-card">
            <div class="template-header">
                <div class="template-icon" style="background: rgba(196, 90, 59, 0.1);">
                    <svg width="24" height="24" fill="none" stroke="#c45a3b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.818v5.364a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="template-body">
                <h3 class="template-name">Webinar Follow-up (5 emails)</h3>
                <p class="template-desc">Convert webinar attendees to customers with a proven pre and post-webinar sequence.</p>
            </div>
        </div>
        
        <div class="template-card">
            <div class="template-header">
                <div class="template-icon" style="background: rgba(74, 93, 82, 0.1);">
                    <svg width="24" height="24" fill="none" stroke="#4a5d52" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="template-body">
                <h3 class="template-name">Product Launch (4 emails)</h3>
                <p class="template-desc">Launch new products with maximum impact. The anticipation → reveal → urgency formula.</p>
            </div>
        </div>
        
        <div class="template-card">
            <div class="template-header">
                <div class="template-icon" style="background: rgba(196, 90, 59, 0.1);">
                    <svg width="24" height="24" fill="none" stroke="#c45a3b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="template-body">
                <h3 class="template-name">Thank You & Upsell (3 emails)</h3>
                <p class="template-desc">Maximize customer lifetime value with post-purchase thank you and strategic upsell offers.</p>
            </div>
        </div>
    </div>
</section>

<!-- Social Proof -->
<section class="proof-section">
    <div class="proof-content">
        <p class="proof-quote">"I recovered ₦180,000 in lost sales within 2 weeks using the cart abandonment template. That single template paid for the pack 12 times over."</p>
        <div class="proof-author">
            <div class="proof-avatar"></div>
            <div class="proof-info">
                <p class="proof-name">Sarah O.</p>
                <p class="proof-title">E-commerce Store Owner, Lagos</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" id="pricing">
    <div class="cta-box">
        <h2 class="cta-title">Get the Full Pack</h2>
        <p class="cta-price">
            <span>₦15,000</span> one-time payment<br>
            <small>Instant download • Lifetime access</small>
        </p>
        <a href="https://joala.com.ng/buy/email-sequence-templates-pack" class="cta-btn">
            Buy Now — ₦15,000
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        <p class="guarantee">30-day money-back guarantee. No questions asked.</p>
        
        <!-- Timer below Buy Now button -->
        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(26,20,16,0.1);">
            <p style="font-size: 13px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ember); margin-bottom: 16px;">⚡ Limited Time Offer</p>
            <div style="display: flex; justify-content: center; gap: 16px; margin-bottom: 16px;">
                <div style="background: var(--espresso); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Clash Display', sans-serif; font-size: 42px; font-weight: 700; line-height: 1;" id="hours">23</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Hours</div>
                </div>
                <div style="background: var(--espresso); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Clash Display', sans-serif; font-size: 42px; font-weight: 700; line-height: 1;" id="minutes">59</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Minutes</div>
                </div>
                <div style="background: var(--espresso); color: white; border-radius: 16px; padding: 20px 28px; min-width: 90px;">
                    <div style="font-family: 'Clash Display', sans-serif; font-size: 42px; font-weight: 700; line-height: 1; color: var(--ember);" id="seconds">59</div>
                    <div style="font-size: 12px; text-transform: uppercase; opacity: 0.8;">Seconds</div>
                </div>
            </div>
            <p style="font-size: 16px; font-weight: 600; color: var(--espresso);">Get <strong style="color: var(--ember);">₦20,000 off</strong> when timer ends!</p>
        </div>
    </div>
</section>

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
        
        var hEl = document.getElementById('hours');
        var mEl = document.getElementById('minutes');
        var sEl = document.getElementById('seconds');
        if(hEl) hEl.textContent = String(hours).padStart(2, '0');
        if(mEl) mEl.textContent = String(minutes).padStart(2, '0');
        if(sEl) sEl.textContent = String(seconds).padStart(2, '0');
    }
    
    updateTimer();
    setInterval(updateTimer, 1000);
})();
</script>

<!-- Footer -->
<footer class="footer">
    <p class="footer-text">© 2026 JoAla Ventures. All rights reserved.</p>
</footer>

@endsection