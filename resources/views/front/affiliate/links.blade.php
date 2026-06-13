@extends('layouts.app')

@section('title', 'Affiliate Links')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-white">Your Affiliate Links</h1>
            <p class="text-slate-300 mt-1">Copy and share these links to earn commissions</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white border-b border-slate-200 sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex gap-8 -mb-px">
                <a href="/affiliate/dashboard" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
                    <i class="fas fa-th-large mr-2"></i>Dashboard
                </a>
                <a href="/affiliate/links" class="py-4 px-1 border-b-2 border-blue-600 text-blue-600 font-medium text-sm">
                    <i class="fas fa-link mr-2"></i>Links
                </a>
                <a href="/affiliate/payouts" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
                    <i class="fas fa-credit-card mr-2"></i>Payouts
                </a>
                <a href="/affiliate/settings" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Main Referral Link -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Your Main Referral Link</h2>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                        <i class="fas fa-link text-slate-400"></i>
                        <input type="text" value="https://joala.com.ng/ref/{{ $affiliate['referral_code'] }}" 
                            class="flex-1 bg-transparent text-slate-700 text-sm outline-none" readonly id="mainLink">
                    </div>
                </div>
                <button onclick="copyLink('mainLink')" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-xl">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <p class="text-sm text-slate-500 mt-3">Share this link to earn {{ $affiliate['commission_rate'] ?? 20 }}% commission on every sale</p>
        </div>

        <!-- Products -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Product Links</h2>
            @if(count($products ?? []) > 0)
            <div class="space-y-4">
                @foreach($products as $product)
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 border border-slate-200 rounded-xl">
                    <div class="flex items-center gap-4">
                        @if($product['image'])
                        <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" class="w-12 h-12 rounded-lg object-cover">
                        @else
                        <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-slate-400"></i>
                        </div>
                        @endif
                        <div>
                            <h3 class="font-medium text-slate-900">{{ $product['title'] }}</h3>
                            <p class="text-sm text-slate-500">₦{{ number_format($product['sale_price'] ?? $product['price']) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" value="https://joala.com.ng/store/{{ $product['slug'] }}?ref={{ $affiliate['referral_code'] }}" 
                            class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm" readonly id="product-{{ $product['id'] }}">
                        <button onclick="copyLink('product-{{ $product['id'] }}')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-sm">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-500 text-center py-8">No products available yet.</p>
            @endif
        </div>
    </div>
</div>

<script>
function copyLink(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}
</script>
@endsection