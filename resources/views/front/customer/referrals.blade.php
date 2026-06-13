@extends('front.customer.layout')

@section('customer-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900">My Referrals</h1>
    <p class="text-slate-600">Share your code and earn credits on every purchase</p>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-xl mb-6">
    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
</div>
@endif

@if($referral ?? false)
<!-- Referral Code -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Your Referral Code</h2>
            <p class="text-sm text-slate-500 mt-1">Share this code with friends and earn <strong>₦{{ number_format($referral['credit_per_referral'] ?? 1000) }}</strong> per referral!</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-slate-50 border border-slate-300 rounded-xl px-6 py-3">
                <span class="text-2xl font-bold text-slate-900 font-mono" id="referralCode">{{ $referral['referral_code'] ?? '' }}</span>
            </div>
            <button onclick="copyCode()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-3 rounded-xl transition-colors">
                <i class="fas fa-copy mr-2"></i>Copy
            </button>
        </div>
    </div>
    <p class="text-sm text-slate-500 mt-4">
        <i class="fas fa-info-circle mr-1"></i> 
        Share link: <code class="bg-slate-100 px-2 py-1 rounded text-blue-600">https://joala.com.ng/?ref={{ $referral['referral_code'] ?? '' }}</code>
    </p>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 font-medium">Total Referrals</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">{{ $referral['total_referrals'] ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-xl text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 font-medium">Completed</p>
                <p class="text-3xl font-bold text-slate-900 mt-2">{{ $completedReferrals ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-xl text-emerald-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 font-medium">Total Earned</p>
                <p class="text-3xl font-bold text-emerald-600 mt-2">₦{{ number_format($referral['total_credits'] ?? $totalEarned ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-naira-sign text-xl text-amber-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Referral History -->
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-slate-900">Referral Activity</h2>
        @if($completedReferrals > 0)
        <span class="text-sm text-slate-500">{{ $completedReferrals }} completed, {{ $pendingReferrals }} pending</span>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Person</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Earned</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($referrals ?? [] as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap">{{ date('M d, Y', strtotime($r['created_at'])) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center">
                                <span class="text-xs font-medium text-slate-600">{{ strtoupper(substr($r['referred_email'] ?? '?', 0, 1)) }}</span>
                            </div>
                            <span class="text-sm text-slate-900">{{ $r['referred_email'] ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $r['order_number'] ?? $r['order_id'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-slate-900">₦{{ number_format($r['order_amount'] ?? 0) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($r['status'] === 'completed') bg-emerald-100 text-emerald-700
                            @elseif($r['status'] === 'pending') bg-amber-100 text-amber-700
                            @else bg-slate-100 text-slate-700 @endif">
                            {{ ucfirst($r['status']) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-emerald-600">₦{{ number_format($r['credit_earned'] ?? 0) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-share-alt text-2xl text-slate-400"></i>
                            </div>
                            <p class="text-slate-600 font-medium">No referrals yet</p>
                            <p class="text-sm text-slate-500 mt-1">Share your referral code to get started!</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-share-alt text-2xl text-slate-400"></i>
    </div>
    <p class="text-slate-600 mb-2">Referral system not available</p>
    <p class="text-sm text-slate-500">Please contact support</p>
</div>
@endif

<script>
function copyCode() {
    var code = document.getElementById('referralCode');
    if (code) {
        navigator.clipboard.writeText(code.textContent.trim());
        alert('Referral code copied!');
    }
}
</script>
@endsection