@extends('layouts.app')

@section('title', 'Affiliate Dashboard')

@section('content')
<div class="min-h-screen bg-slate-50">
    <!-- Dashboard Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Welcome back, {{ $affiliate->name ?? 'Partner' }}!</h1>
                    <p class="text-slate-300 mt-2">Here's your affiliate performance overview</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-sm">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                        Active Partner
                    </span>
                    <a href="/affiliate/logout" class="ml-4 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-500/20 border border-red-500/30 text-red-400 text-sm hover:bg-red-500/30">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white border-b border-slate-200 sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex gap-8 -mb-px">
                <a href="/affiliate/dashboard" class="py-4 px-1 border-b-2 border-blue-600 text-blue-600 font-medium text-sm">
                    <i class="fas fa-th-large mr-2"></i>Dashboard
                </a>
                <a href="/affiliate/links" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
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
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Earned -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Total Earned</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">₦{{ number_format($stats['total_earned'] ?? 0) }}</p>
                    </div>
                    <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-naira-sign text-xl text-emerald-600"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-emerald-600 font-medium">
                        <i class="fas fa-arrow-up mr-1"></i>{{ $affiliateStats['clicks_this_month'] ?? 0 }}
                    </span>
                    <span class="text-slate-500 ml-2">clicks this month</span>
                </div>
            </div>

            <!-- Pending Commissions -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Pending Commissions</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">₦{{ number_format($affiliateStats['pending'] ?? 0) }}</p>
                    </div>
                    <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-clock text-xl text-amber-600"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-slate-500">{{ $affiliateStats['total_conversions'] ?? 0 }} conversions</span>
                </div>
            </div>

            <!-- Total Referrals -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">Total Conversions</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">{{ number_format($affiliateStats['total_conversions'] ?? 0) }}</p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-users text-xl text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-blue-600 font-medium">
                        <i class="fas fa-shopping-cart mr-1"></i>{{ $affiliateStats['total_orders'] ?? 0 }}
                    </span>
                    <span class="text-slate-500 ml-2">orders</span>
                </div>
            </div>
        </div>

        <!-- Referral Link Section -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Your Referral Link</h2>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                        <i class="fas fa-link text-slate-400"></i>
                        <input type="text" value="https://joala.com.ng/ref/{{ $affiliate['referral_code'] }}" 
                            class="flex-1 bg-transparent text-slate-700 text-sm outline-none" readonly id="referralLink">
                    </div>
                </div>
                <button onclick="copyReferralLink()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-xl transition-colors">
                    <i class="fas fa-copy"></i>
                    <span id="copyText">Copy Link</span>
                </button>
            </div>
            <p class="text-sm text-slate-500 mt-3">Share this link to earn {{ $affiliate['commission_rate'] ?? 20 }}% commission on every sale</p>
        </div>

        <!-- Products to Promote -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Products You Can Promote</h2>
            @if(count($products ?? []) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                <div class="border border-slate-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                    <div class="flex gap-4">
                        @if($product['image'])
                        <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" class="w-20 h-20 rounded-lg object-cover">
                        @else
                        <div class="w-20 h-20 bg-slate-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-slate-400 text-2xl"></i>
                        </div>
                        @endif
                        <div class="flex-1">
                            <h3 class="font-medium text-slate-900">{{ $product['title'] }}</h3>
                            <p class="text-lg font-bold text-emerald-600 mt-1">₦{{ number_format($product['sale_price'] ?? $product['price']) }}</p>
                            <p class="text-sm text-blue-600 mt-1">Commission: {{ $product['affiliate_commission'] ?? $affiliate['commission_rate'] ?? 20 }}%</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button onclick="copyLink('{{ $product['slug'] }}')" class="w-full text-center text-sm text-slate-600 hover:text-blue-600 bg-slate-50 py-2 rounded-lg">
                            <i class="fas fa-copy mr-1"></i> Copy Product Link
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-500 text-center py-8">No products available for promotion yet. Check back soon!</p>
            @endif
        </div>

        <!-- Funnels to Promote -->
        @if(count($funnels ?? []) > 0)
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Sales Funnels You Can Promote</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($funnels as $funnel)
                <div class="border border-slate-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                    <h3 class="font-medium text-slate-900">{{ $funnel['name'] }}</h3>
                    <p class="text-sm text-slate-500 mt-1">Commission: {{ $funnel['affiliate_commission'] ?? $affiliate['commission_rate'] ?? 20 }}%</p>
                    <div class="mt-4">
                        <button onclick="copyLink('funnel/{{ $funnel['slug'] }}')" class="w-full text-center text-sm text-slate-600 hover:text-blue-600 bg-slate-50 py-2 rounded-lg">
                            <i class="fas fa-copy mr-1"></i> Copy Funnel Link
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Referrals Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Recent Referrals & Commissions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Referral</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($commissions ?? [] as $commission)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $commission['created_at'] ? date('M d, Y', strtotime($commission['created_at'])) : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-slate-600">{{ strtoupper(substr($commission['referral_name'] ?? 'U', 0, 1)) }}</span>
                                    </div>
                                    <span class="text-sm font-medium text-slate-900">{{ $commission['referral_name'] ?? 'Guest User' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $commission['product_name'] ?? 'Product' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'paid' => 'bg-emerald-100 text-emerald-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusClass = $statusClasses[$commission['status'] ?? 'pending'] ?? 'bg-slate-100 text-slate-700';
                                @endphp
                                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                    {{ ucfirst($commission['status'] ?? 'Pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                ₦{{ number_format($commission['amount'] ?? 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-user-plus text-2xl text-slate-400"></i>
                                    </div>
                                    <p class="text-slate-600 font-medium">No referrals yet</p>
                                    <p class="text-sm text-slate-500 mt-1">Share your referral link to get started</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const linkInput = document.getElementById('referralLink');
    const copyText = document.getElementById('copyText');
    
    navigator.clipboard.writeText(linkInput.value).then(function() {
        copyText.textContent = 'Copied!';
        setTimeout(function() {
            copyText.textContent = 'Copy Link';
        }, 2000);
    });
}

function copyLink(slug) {
    const baseUrl = 'https://joala.com.ng';
    const affiliateCode = '{{ $affiliate['referral_code'] ?? '' }}';
    const fullUrl = baseUrl + '/' + slug + '?ref=' + affiliateCode;
    
    navigator.clipboard.writeText(fullUrl).then(function() {
        alert('Link copied! Share this to earn commissions.');
    });
}
</script>
@endsection