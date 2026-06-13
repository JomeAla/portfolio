@extends('layouts.app')

@section('title', 'Affiliate Settings')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-white">Settings</h1>
            <p class="text-slate-300 mt-1">Manage your account and payment details</p>
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
                <a href="/affiliate/payouts" class="py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium text-sm transition-colors">
                    <i class="fas fa-credit-card mr-2"></i>Payouts
                </a>
                <a href="/affiliate/settings" class="py-4 px-1 border-b-2 border-blue-600 text-blue-600 font-medium text-sm">
                    <i class="fas fa-cog mr-2"></i>Settings
                </a>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-6">Profile Information</h2>
            
            @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">{{ session('error') }}</div>
            @endif

            <form action="{{ route('affiliate.bank.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                        <input type="text" value="{{ $affiliate['name'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                        <input type="email" value="{{ $affiliate['email'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50" readonly>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-6 mt-6">
                    <h3 class="text-md font-medium text-slate-900 mb-4">Payment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Bank Name</label>
                            <input type="text" name="bank_name" placeholder="e.g., First Bank" value="{{ $affiliate['bank_name'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Account Number</label>
                            <input type="text" name="bank_account_number" placeholder="Your account number" value="{{ $affiliate['bank_account_number'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Account Name</label>
                            <input type="text" name="bank_account_name" placeholder="Name on account" value="{{ $affiliate['bank_account_name'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 mt-4">Minimum payout: ₦{{ number_format($affiliate['min_payout'] ?? 5000) }}</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-8 py-3 rounded-xl">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection