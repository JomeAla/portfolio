@extends('layouts.app')

@section('title', 'Affiliate Payouts')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-white">Payouts</h1>
            <p class="text-slate-300 mt-1">View your earnings and request payouts</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white border-b border-slate-200 sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex gap-8 -mb-px">
                <a href="/affiliate/dashboard" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
                    <i class="fas fa-th-large mr-2"></i>Dashboard
                </a>
                <a href="/affiliate/links" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
                    <i class="fas fa-link mr-2"></i>Links
                </a>
                <a href="/affiliate/payouts" class="py-4 px-1 border-b-2 border-blue-600 text-blue-600 font-medium text-sm">
                    <i class="fas fa-credit-card mr-2"></i>Payouts
                </a>
                <a href="/affiliate/settings" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <p class="text-sm text-slate-500">Total Earned</p>
                <p class="text-2xl font-bold text-slate-900">₦{{ number_format($affiliate['total_earned'] ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <p class="text-sm text-slate-500">Total Paid</p>
                <p class="text-2xl font-bold text-slate-900">₦{{ number_format($affiliate['total_paid'] ?? 0) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <p class="text-sm text-slate-500">Available Balance</p>
                <p class="text-2xl font-bold text-emerald-600">₦{{ number_format(($affiliate['total_earned'] ?? 0) - ($affiliate['total_paid'] ?? 0)) }}</p>
            </div>
        </div>

        <!-- Payout History -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Payout History</h2>
            </div>
            @if(count($payouts ?? []) > 0)
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500">Method</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($payouts as $payout)
                    <tr>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $payout['created_at'] }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">₦{{ number_format($payout['amount']) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $payout['method'] ?? 'Bank Transfer' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                {{ $payout['status'] == 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ ucfirst($payout['status'] ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="p-12 text-center">
                <p class="text-slate-500">No payouts yet. Keep referring to earn!</p>
            </div>
            @endif
        </div>

        <!-- Request Payout Button -->
        <div class="mt-8 text-center">
            @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4">{{ session('error') }}</div>
            @endif
            <form action="{{ route('affiliate.payout.request') }}" method="POST" onsubmit="return confirm('Request payout for your available balance?')" style="display:inline">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-8 py-3 rounded-xl">
                    <i class="fas fa-credit-card mr-2"></i>Request Payout
                </button>
            </form>
            @php $available = ($affiliate['total_earned'] ?? 0) - ($affiliate['total_paid'] ?? 0); @endphp
            @if($available < ($affiliate['min_payout'] ?? 5000))
            <p class="text-sm text-slate-500 mt-2">Minimum payout: ₦{{ number_format($affiliate['min_payout'] ?? 5000) }}</p>
            @endif
        </div>
    </div>
</div>
@endsection