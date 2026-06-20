@extends('front.customer.layout')

@section('customer-content')
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
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Dashboard</p>
            <h1 class="text-3xl font-bold text-slate-900 mt-1">Welcome back, {{ explode(' ', $customer['name'] ?? 'Customer')[0] }}!</h1>
        </div>
        <a href="/store" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl font-medium transition-all hover:scale-105">
            <i class="fas fa-store"></i> Browse Store
        </a>
    </div>
</div>

<!-- Hero Stats -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 text-white group hover:scale-[1.02] transition-transform duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                <i class="fas fa-shopping-bag text-xl"></i>
            </div>
            <p class="text-blue-100 text-sm font-medium mb-1">Total Orders</p>
            <p class="text-4xl font-bold">{{ $stats['total_orders'] ?? 0 }}</p>
        </div>
    </div>
    
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl p-6 text-white group hover:scale-[1.02] transition-transform duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                <i class="fas fa-naira-sign text-xl"></i>
            </div>
            <p class="text-emerald-100 text-sm font-medium mb-1">Total Spent</p>
            <p class="text-4xl font-bold">₦{{ number_format($stats['total_spent'] ?? 0) }}</p>
        </div>
    </div>
    
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 to-violet-700 rounded-2xl p-6 text-white group hover:scale-[1.02] transition-transform duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                <i class="fas fa-download text-xl"></i>
            </div>
            <p class="text-violet-100 text-sm font-medium mb-1">Downloads</p>
            <p class="text-4xl font-bold">{{ $downloadCount ?? 0 }}</p>
        </div>
    </div>
    
    <div class="relative overflow-hidden bg-gradient-to-br from-amber-600 to-amber-700 rounded-2xl p-6 text-white group hover:scale-[1.02] transition-transform duration-300">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                <i class="fas fa-users text-xl"></i>
            </div>
            <p class="text-amber-100 text-sm font-medium mb-1">Referrals</p>
            <p class="text-4xl font-bold">{{ $referralCount ?? 0 }}</p>
        </div>
    </div>
</div>

<!-- Main Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Quick Actions -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-slate-900">Quick Actions</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="/customer/orders" class="group flex flex-col items-center p-5 rounded-xl border-2 border-slate-100 hover:border-blue-500 hover:bg-blue-50/50 transition-all">
                        <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                            <i class="fas fa-shopping-bag text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Orders</span>
                    </a>
                    <a href="/customer/downloads" class="group flex flex-col items-center p-5 rounded-xl border-2 border-slate-100 hover:border-violet-500 hover:bg-violet-50/50 transition-all">
                        <div class="w-14 h-14 bg-violet-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-violet-500 group-hover:text-white transition-colors">
                            <i class="fas fa-cloud-download-alt text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Downloads</span>
                    </a>
                    <a href="/customer/referrals" class="group flex flex-col items-center p-5 rounded-xl border-2 border-slate-100 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all">
                        <div class="w-14 h-14 bg-emerald-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            <i class="fas fa-share-alt text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Referrals</span>
                    </a>
                    <a href="/customer/affiliate" class="group flex flex-col items-center p-5 rounded-xl border-2 border-slate-100 hover:border-amber-500 hover:bg-amber-50/50 transition-all">
                        <div class="w-14 h-14 bg-amber-50 rounded-xl flex items-center justify-center mb-3 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                            <i class="fas fa-hand-holding-usd text-xl"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Earn More</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-slate-900">Recent Orders</h2>
                <a href="/customer/orders" class="text-sm text-blue-600 hover:underline font-medium">View All</a>
            </div>
            <div class="p-6">
                @if(count($recentOrders ?? []) > 0)
                <div class="space-y-4">
                    @foreach($recentOrders as $order)
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center">
                                <i class="fas fa-box text-slate-400"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $order['order_number'] }}</p>
                                <p class="text-sm text-slate-500">{{ date('M d, Y', strtotime($order['created_at'])) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">₦{{ number_format($order['final_amount']) }}</p>
                            <span class="text-xs px-2 py-1 rounded-full {{ $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($order['payment_status']) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shopping-bag text-2xl text-slate-400"></i>
                    </div>
                    <p class="text-slate-600 mb-4">No orders yet</p>
                    <a href="/store" class="inline-flex items-center gap-2 text-blue-600 hover:underline font-medium">
                        Browse Products <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Account Summary -->
    <div>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden sticky top-6">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-900">Your Account</h2>
            </div>
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-violet-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl font-bold text-white">{{ strtoupper(substr($customer['name'] ?? 'C', 0, 1)) }}</span>
                    </div>
                    <h3 class="font-semibold text-slate-900">{{ $customer['name'] ?? 'Customer' }}</h3>
                    <p class="text-sm text-slate-500">{{ $customer['email'] ?? '' }}</p>
                </div>
                
                <div class="space-y-3">
                    <a href="/customer/settings" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                        <span class="text-slate-600"><i class="fas fa-cog w-5 mr-2"></i>Account Settings</span>
                        <i class="fas fa-chevron-right text-slate-400 text-sm"></i>
                    </a>
                    <a href="/customer/subscriptions" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                        <span class="text-slate-600"><i class="fas fa-credit-card w-5 mr-2"></i>Subscriptions</span>
                        <i class="fas fa-chevron-right text-slate-400 text-sm"></i>
                    </a>
                    <a href="/customer/achievements" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                        <span class="text-slate-600"><i class="fas fa-trophy w-5 mr-2"></i>Achievements</span>
                        <i class="fas fa-chevron-right text-slate-400 text-sm"></i>
                    </a>
                    <a href="/customer/refund" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors">
                        <span class="text-slate-600"><i class="fas fa-undo w-5 mr-2"></i>Request Refund</span>
                        <i class="fas fa-chevron-right text-slate-400 text-sm"></i>
                    </a>
                    <a href="/customer/logout" class="flex items-center justify-between p-3 rounded-xl hover:bg-red-50 transition-colors">
                        <span class="text-red-600"><i class="fas fa-sign-out-alt w-5 mr-2"></i>Sign Out</span>
                        <i class="fas fa-chevron-right text-red-400 text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection