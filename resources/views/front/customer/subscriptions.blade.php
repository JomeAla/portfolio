@extends('front.customer.layout')

@section('customer-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900">My Subscriptions</h1>
    <p class="text-slate-600">Manage your subscription plans</p>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-6 py-4 rounded-xl mb-6">
    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6">
    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
</div>
@endif

@if($activeSubscription ?? false)
<div class="bg-gradient-to-r from-blue-600 to-violet-600 rounded-2xl p-8 text-white mb-8">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Active Plan</p>
            <h2 class="text-2xl font-bold mt-1">{{ $activeSubscription->plan->name ?? 'Active Subscription' }}</h2>
            <div class="flex items-center gap-3 mt-3">
                <span class="px-3 py-1 rounded-full bg-white/20 text-sm font-medium">
                    @if($activeSubscription->isOnTrial()) Trial
                    @elseif($activeSubscription->isActive()) Active
                    @elseif($activeSubscription->isCancelled()) Cancelled
                    @endif
                </span>
                @if($activeSubscription->next_billing_date)
                <span class="text-blue-100 text-sm">Next billing: {{ $activeSubscription->next_billing_date->format('M d, Y') }}</span>
                @endif
            </div>
        </div>
        <div class="text-right">
            <p class="text-3xl font-bold">₦{{ number_format($activeSubscription->plan->price ?? 0) }}</p>
            <p class="text-blue-100 text-sm">{{ $activeSubscription->plan->interval_label ?? '/month' }}</p>
        </div>
    </div>
    <div class="mt-6 pt-6 border-t border-white/20">
        <form action="{{ route('subscription.cancel') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
            @csrf
            <button type="submit" class="px-6 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                Cancel Subscription
            </button>
        </form>
    </div>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    @forelse($plans ?? [] as $plan)
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition-shadow {{ $plan->is_featured ? 'ring-2 ring-blue-500' : '' }}">
        @if($plan->is_featured)
        <div class="bg-blue-600 text-white text-center text-xs font-bold uppercase tracking-wider py-2">Most Popular</div>
        @endif
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">{{ $plan->name }}</h3>
            @if($plan->description)
            <p class="text-sm text-slate-600 mt-2">{{ $plan->description }}</p>
            @endif
            <div class="mt-4">
                <span class="text-3xl font-bold text-slate-900">₦{{ number_format($plan->price) }}</span>
                <span class="text-slate-500 text-sm">{{ $plan->interval_label }}</span>
            </div>
            @if($plan->trial_days > 0)
            <p class="text-sm text-emerald-600 mt-1"><i class="fas fa-gift mr-1"></i> {{ $plan->trial_days }}-day free trial</p>
            @endif
            @if($plan->features)
            <ul class="mt-4 space-y-2">
                @foreach((array) $plan->features as $feature)
                <li class="flex items-start gap-2 text-sm text-slate-600">
                    <i class="fas fa-check text-emerald-500 mt-0.5"></i>
                    <span>{{ $feature }}</span>
                </li>
                @endforeach
            </ul>
            @endif
            <a href="/customer/subscribe/{{ $plan->id }}" class="mt-6 block w-full text-center bg-slate-900 hover:bg-slate-800 text-white font-medium py-3 rounded-xl transition-colors {{ $activeSubscription && $activeSubscription->plan_id == $plan->id ? 'opacity-50 cursor-not-allowed' : '' }}">
                @if($activeSubscription && $activeSubscription->plan_id == $plan->id) Current Plan
                @else Subscribe
                @endif
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl border border-slate-200 p-12 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-credit-card text-2xl text-slate-400"></i>
        </div>
        <p class="text-slate-600">No subscription plans available</p>
        <p class="text-sm text-slate-500 mt-1">Check back soon for our plans</p>
    </div>
    @endforelse
</div>

@if(isset($subscriptionHistory) && count($subscriptionHistory) > 0)
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-lg font-semibold text-slate-900">Subscription History</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Started</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Ended</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($subscriptionHistory as $sub)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $sub->plan->name ?? 'Unknown' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            @if($sub->status == 'active') bg-emerald-100 text-emerald-700
                            @elseif($sub->status == 'cancelled') bg-red-100 text-red-700
                            @elseif($sub->status == 'expired') bg-slate-100 text-slate-700
                            @else bg-amber-100 text-amber-700 @endif">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-600">{{ $sub->started_at ? $sub->started_at->format('M d, Y') : '-' }}</td>
                    <td class="px-6 py-4 text-slate-600">{{ $sub->current_period_end ? $sub->current_period_end->format('M d, Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection