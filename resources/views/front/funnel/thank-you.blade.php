<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Joala Ventures</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .fade-in { animation: fadeIn 0.5s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .confetti { position: fixed; width: 10px; height: 10px; background: #f00; animation: fall 3s linear infinite; }
        @keyframes fall { to { transform: translateY(100vh) rotate(720deg); } }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-2xl p-8 text-center fade-in">
            <!-- Success Icon -->
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-4xl"></i>
            </div>
            
            <!-- Title -->
            <h1 class="text-3xl font-bold text-slate-800 mb-4">{{ $title }}</h1>
            
            <!-- Message -->
            <p class="text-lg text-slate-600 mb-6">{{ $message }}</p>
            
            <!-- Video (if set) -->
            @if($video)
            <div class="mb-6">
                <iframe src="{{ $video }}" class="w-full aspect-video rounded-lg" frameborder="0" allowfullscreen></iframe>
            </div>
            @endif
            
            <!-- Next Steps -->
            <div class="bg-slate-50 rounded-xl p-6 mb-6">
                <h3 class="font-bold text-slate-800 mb-3">What's Next?</h3>
                <ul class="text-left text-slate-600 space-y-2">
                    <li><i class="fas fa-envelope text-green-500 mr-2"></i> Check your email for order details</li>
                    <li><i class="fas fa-download text-blue-500 mr-2"></i> Download your product in the email</li>
                    <li><i class="fas fa-headset text-purple-500 mr-2"></i> Need help? Contact support@joala.com.ng</li>
                </ul>
            </div>
            
            <!-- Upsell (if enabled) -->
            @if($show_upsell && $upsell_product)
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-xl p-6 mb-6">
                <div class="text-sm text-orange-600 mb-2">SPECIAL OFFER!</div>
                <h3 class="font-bold text-xl text-slate-800 mb-2">Get {{ $upsell_product->title }}</h3>
                <p class="text-slate-600 mb-4">Bonus: Get {{ $upsell_product->sale_price ? number_format($upsell_product->sale_price) : number_format($upsell_product->price) }} OFF!</p>
                <a href="/store/{{ $upsell_product->slug }}?upsell=1&funnel={{ $funnel->id }}" class="inline-block bg-orange-500 text-white px-6 py-3 rounded-lg font-bold hover:bg-orange-600 transition">
                    Claim My Bonus <i class="fas fa-gift ml-2"></i>
                </a>
            </div>
            @endif
            
            <!-- Back to Home -->
            <a href="/" class="text-blue-600 hover:underline">Back to Home</a>
        </div>
    </div>
    
    <!-- Exit Intent Popup -->
    @if($exit_popup)
    <div id="exitPopup" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md mx-4 text-center">
            <button onclick="document.getElementById('exitPopup').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-gift text-orange-500 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Wait! Don't Leave!</h3>
            <p class="text-slate-600 mb-4">{{ $exit_offer ?? 'Get 10% off your next purchase' }}</p>
            <form method="GET" action="/newsletter" class="mb-4">
                <input type="email" placeholder="Enter your email" required class="w-full border border-slate-300 rounded-lg px-4 py-2 mb-2">
                <button type="submit" class="w-full bg-orange-500 text-white px-4 py-2 rounded-lg font-bold hover:bg-orange-600">
                    Get My {{ $exit_discount ?? 10 }}% Discount
                </button>
            </form>
            <button onclick="document.getElementById('exitPopup').classList.add('hidden')" class="text-slate-500 text-sm">No thanks</button>
        </div>
    </div>
    <script>
        let exitShown = false;
        document.addEventListener('mouseleave', function(e) {
            if (e.clientY <= 0 && !exitShown) {
                exitShown = true;
                document.getElementById('exitPopup').classList.remove('hidden');
                document.getElementById('exitPopup').classList.add('flex');
            }
        });
    </script>
    @endif
</body>
</html>