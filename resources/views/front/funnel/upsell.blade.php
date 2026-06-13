@extends('layouts.app')

@section('title', 'Special Offer - ' . ($funnel->upsellProduct->title ?? 'Exclusive Deal'))

@section('content')
<style>
:root {
    --primary: #2179e6;
    --accent: #f97316;
    --dark: #0f172a;
}
</style>

<div class="min-h-screen bg-gradient-to-br from-amber-50 to-orange-100 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            
            <!-- Timer Banner -->
            @if($funnel->upsell_timer > 0)
            <div class="bg-red-600 text-white text-center py-3 rounded-t-xl font-bold">
                <i class="fas fa-clock mr-2"></i>
                Special offer expires in <span id="timer">{{ $funnel->upsell_timer }}</span> minutes!
            </div>
            @endif

            <!-- Upsell Card -->
            <div class="bg-white rounded-b-xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-8 text-center">
                    <div class="inline-block bg-white text-amber-600 px-4 py-1 rounded-full text-sm font-bold mb-4">
                        🎉 SPECIAL OFFER
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Wait! Don't Go Yet</h1>
                    <p class="text-white text-lg">Get {{ $funnel->upsell_discount ?? 10 }}% OFF when you add this to your order</p>
                </div>

                <div class="p-8">
                    @if($funnel->upsellProduct)
                    <div class="flex gap-6 mb-8">
                        <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center">
                            @if($funnel->upsellProduct->image)
                            <img src="{{ $funnel->upsellProduct->image }}" alt="{{ $funnel->upsellProduct->title }}" class="w-full h-full object-cover rounded-lg">
                            @else
                            <i class="fas fa-gift text-4xl text-gray-400"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-slate-900 mb-2">{{ $funnel->upsellProduct->title }}</h3>
                            <p class="text-slate-600 mb-4">{{ $funnel->upsellProduct->short_description ?? $funnel->upsellProduct->description }}</p>
                            
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-bold text-slate-400 line-through">&#8358;{{ number_format($funnel->upsellProduct->price) }}</span>
                                @php
                                $salePrice = $funnel->upsellProduct->sale_price ?? ($funnel->upsellProduct->price * (100 - ($funnel->upsell_discount ?? 10)) / 100);
                                @endphp
                                <span class="text-3xl font-bold text-emerald-600">&#8358;{{ number_format($salePrice) }}</span>
                                <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-sm font-bold">{{ $funnel->upsell_discount ?? 10 }}% OFF</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Benefits -->
                    <div class="bg-emerald-50 rounded-lg p-6 mb-8">
                        <h4 class="font-bold text-emerald-800 mb-3"><i class="fas fa-check-circle mr-2"></i>What's Included:</h4>
                        <ul class="space-y-2 text-slate-700">
                            <li><i class="fas fa-check text-emerald-500 mr-2"></i>Instant access to all files</li>
                            <li><i class="fas fa-check text-emerald-500 mr-2"></i>Lifetime updates included</li>
                            <li><i class="fas fa-check text-emerald-500 mr-2"></i>Priority support access</li>
                        </ul>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <form method="POST" action="{{ route('funnel.upsell.accept', $funnel->id) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 text-white py-4 px-6 rounded-lg font-bold text-lg hover:from-emerald-600 hover:to-emerald-700 transition-all shadow-lg hover:shadow-xl">
                                <i class="fas fa-plus-circle mr-2"></i>
                                YES! Add to My Order
                            </button>
                        </form>
                        
                        <a href="{{ route('funnel.thankyou', $funnel->id) }}" class="flex-1">
                            <button class="w-full bg-gray-100 text-gray-600 py-4 px-6 rounded-lg font-bold text-lg hover:bg-gray-200 transition-all">
                                No Thanks, Continue
                            </button>
                        </a>
                    </div>

                    <p class="text-center text-sm text-slate-500 mt-4">
                        <i class="fas fa-shield-alt mr-1"></i>
                        30-day money-back guarantee • Secure checkout
                    </p>
                </div>
            </div>

            <!-- Trust Badges -->
            <div class="flex justify-center gap-8 mt-8 text-slate-500">
                <div class="text-center">
                    <i class="fas fa-lock text-2xl mb-2"></i>
                    <p class="text-sm">Secure<br>Payment</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-undo text-2xl mb-2"></i>
                    <p class="text-sm">30-Day<br>Guarantee</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-headset text-2xl mb-2"></i>
                    <p class="text-sm">24/7<br>Support</p>
                </div>
            </div>
        </div>
    </div>
</div>

@if($funnel->upsell_timer > 0)
<script>
let timeLeft = {{ $funnel->upsell_timer }};
const timerEl = document.getElementById('timer');

setInterval(() => {
    timeLeft--;
    if (timeLeft <= 0) {
        timerEl.parentElement.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Offer expired!';
    } else {
        timerEl.textContent = timeLeft;
    }
}, 60000);
</script>
@endif

@endsection