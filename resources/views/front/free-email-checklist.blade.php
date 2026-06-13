@extends('layouts.app')

@section('title', 'Free Email Marketing Checklist + Templates')

@section('content')
<style>
    .gradient-text {
        background: linear-gradient(135deg, #059669 0%, #0891b2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(5, 150, 105, 0.15);
    }
    .pulse-button {
        animation: pulse-glow 2s infinite;
    }
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(5, 150, 105, 0.4); }
        50% { box-shadow: 0 0 0 12px rgba(5, 150, 105, 0); }
    }
    .shake {
        animation: shake 0.5s ease-in-out;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
</style>

<div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-20 lg:py-28">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-10 w-72 h-72 bg-emerald-500 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-cyan-500 rounded-full blur-3xl"></div>
        </div>
        
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span class="inline-block px-4 py-1.5 bg-emerald-500/20 text-emerald-400 text-sm font-medium rounded-full mb-6 border border-emerald-500/30">
                    🎁 Free Download
                </span>
                
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    <span class="gradient-text">Email Marketing</span><br>
                    Checklist + Templates
                </h1>
                
                <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto mb-8">
                    Get the complete playbook for emails that convert. Includes a printable checklist 
                    and 3 ready-to-use templates that generated ₦500K+ in sales.
                </p>
                
                <div class="flex flex-wrap justify-center gap-4 mb-10">
                    <div class="flex items-center gap-2 text-slate-400">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>PDF Checklist</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-400">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>3 Email Templates</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-400">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>100% Free</span>
                    </div>
                </div>
                
                <a href="#download" class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold text-lg transition-all pulse-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Get Free Access Now
                </a>
                
                <p class="text-sm text-slate-500 mt-4">
                    🔒 No credit card required • Instant download
                </p>
            </div>
        </div>
    </section>

    <!-- What's Inside Section -->
    <section class="py-20 bg-slate-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">What's Inside</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">Everything you need to send emails that actually get opened and convert.</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Email Marketing Checklist (12 Pages)</h3>
                    <p class="text-slate-600">A printable guide covering strategy, list building, subject lines, body copy, automation, and analytics. Print it out and check off each step.</p>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-cyan-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Welcome Email Template</h3>
                    <p class="text-slate-600">Introduce yourself, deliver value, and set the tone. This template achieved 68% open rate for our clients.</p>
                </div>
                
                <!-- Card 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Cart Abandonment Template</h3>
                    <p class="text-slate-600">Gentle reminder that recovers up to 35% of lost sales. Already tested across 50+ Nigerian e-commerce stores.</p>
                </div>
                
                <!-- Card 4 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm card-hover border border-slate-100">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Re-engagement Template</h3>
                    <p class="text-slate-600">Win back inactive subscribers. Includes a "we miss you" offer that typically sees 22% engagement.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Proof -->
    <section class="py-16 bg-white border-y border-slate-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-8">Used by 500+ Nigerian businesses</p>
            <div class="flex flex-wrap justify-center gap-8 items-center opacity-60">
                <span class="text-slate-400 font-semibold">E-Commerce Stores</span>
                <span class="text-slate-400 font-semibold">Coaches</span>
                <span class="text-slate-400 font-semibold">Agencies</span>
                <span class="text-slate-400 font-semibold">Startups</span>
            </div>
        </div>
    </section>

    <!-- Download Form Section -->
    <section id="download" class="py-20 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 md:p-12 border border-white/20">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold mb-4">Get Your Free Copy</h2>
                    <p class="text-slate-300">Enter your email below and I'll send the checklist and templates immediately.</p>
                </div>
                
                <form id="downloadForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="source" value="email_checklist_lead_magnet">
                    
                    <div>
                        <input type="text" name="name" id="name" placeholder="Your first name" required
                            class="w-full px-6 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <input type="email" name="email" id="email" placeholder="Your email address" required
                            class="w-full px-6 py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    
                    <button type="submit" id="submitBtn"
                        class="w-full bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold text-lg transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>Send Me The Free Templates</span>
                    </button>
                </form>
                
                <div id="successMessage" class="hidden mt-6 p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-xl text-center">
                    <div class="flex items-center justify-center gap-2 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span class="font-medium">Check your inbox! I've sent the download link.</span>
                    </div>
                </div>
                
                <div id="errorMessage" class="hidden mt-4 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-center text-red-400"></div>
                
                <p class="text-center text-sm text-slate-500 mt-6">
                    🔒 Your email is safe. No spam, ever. Unsubscribe anytime.
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 bg-slate-900 text-slate-400">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2025 JoAla Ventures. All rights reserved.</p>
        </div>
    </footer>
</div>

<script>
document.getElementById('downloadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    
    if (!name || !email) {
        showError('Please fill in all fields');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    
    try {
        const response = await fetch('/api/submit-lead', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, email, source: 'email_checklist_lead_magnet' })
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('downloadForm').classList.add('hidden');
            document.getElementById('successMessage').classList.remove('hidden');
        } else {
            showError(data.message || 'Something went wrong. Please try again.');
        }
    } catch (error) {
        showError('Error: ' + error.message);
    }
    
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg><span>Send Me The Free Templates</span>';
});

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.remove('hidden');
    errorDiv.classList.add('shake');
    setTimeout(() => errorDiv.classList.remove('shake'), 500);
}
</script>
@endsection