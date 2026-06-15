<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Your Free Kit - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Geist', sans-serif; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.3); } 50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.6); } }
        .glow-button { animation: pulse-glow 2s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-2xl w-full bg-white/5 backdrop-blur-lg border border-white/10 rounded-3xl p-12 text-center animate-fade">
            <!-- Success Icon -->
            <div class="w-24 h-24 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-8 shadow-lg">
                <i class="ph-fill ph-check text-white text-5xl"></i>
            </div>
            
            <!-- Title -->
            <h1 class="text-4xl font-bold text-white mb-4">Your Download is Ready!</h1>
            
            <!-- Subtitle -->
            <p class="text-lg text-slate-400 mb-8">
                Thank you for subscribing. Your {{ $productName ?? 'eCommerce Starter Kit' }} is ready for download.
            </p>
            
             <!-- What's Inside -->
             @php
                 $features = [
                     'e-commerce-starter-kit' => [
                         'Complete 7-Step Launch Checklist',
                         'Payment Gateway Setup Guide',
                         'SEO & Marketing Optimizations',
                     ],
                     'default' => [
                         'Step-by-Step Implementation Guide',
                         'Ready-to-Use Templates & Resources',
                         'Pro Tips & Best Practices',
                     ],
                 ];
                 $productKey = isset($premiumProduct) && $premiumProduct ? $premiumProduct->slug : 'default';
                 $items = $features[$productKey] ?? $features['default'];
             @endphp
             <div class="bg-white/5 rounded-2xl p-6 mb-8 text-left">
                 <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                     <i class="ph-fill ph-package text-emerald-500"></i>
                     What's Inside
                 </h3>
                 <ul class="space-y-3">
                     @foreach($items as $i => $item)
                     <li class="flex items-center gap-3 text-slate-300">
                         <span class="w-6 h-6 bg-emerald-500/20 text-emerald-500 rounded-full flex items-center justify-center text-sm flex-shrink-0">{{ $i + 1 }}</span>
                         {{ $item }}
                     </li>
                     @endforeach
                 </ul>
             </div>

            @if(isset($productPrice))
            <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-2xl p-4 mb-8">
                <p class="text-amber-400 text-sm font-medium">Upgrade to Premium</p>
                <p class="text-white text-xl font-bold mt-1">{{ $productName ?? 'Full Platform' }} — {{ $productPrice }}</p>
            </div>
            @endif
            
            <!-- Download Button -->
            <a href="{{ $downloadUrl }}" 
               class="glow-button inline-flex items-center gap-3 bg-emerald-500 hover:bg-emerald-600 text-white text-lg font-semibold px-10 py-5 rounded-2xl transition-all transform hover:scale-105">
                <i class="ph-fill ph-download-simple text-2xl"></i>
                Download Now
            </a>
            
            <!-- Auto-redirect message -->
            <p class="text-slate-500 text-sm mt-6">
                Redirecting to sales page in <span id="countdown">15</span> seconds...
            </p>
            
            <!-- Skip Link -->
            <a href="{{ $salesPageUrl }}" class="text-slate-500 text-sm hover:text-white mt-4 inline-block">
                Skip to offer →
            </a>
            
            <!-- Email Info -->
            <div class="mt-10 pt-6 border-t border-white/10">
                <p class="text-slate-500 text-sm">
                    <i class="ph-fill ph-envelope mr-2"></i>
                    Also check your email for a copy of your download
                </p>
            </div>
        </div>
    </div>
    
    <!-- Auto-redirect Script -->
    <script>
        let seconds = 15;
        const countdown = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            seconds--;
            countdown.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = '{{ $salesPageUrl }}';
            }
        }, 1000);
    </script>
</body>
</html>