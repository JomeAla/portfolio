@extends('layouts.admin')

@section('title', 'Advanced Analytics')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800">Advanced Analytics</h1>
    <p class="text-slate-600 mt-2">Comprehensive overview of your platform performance</p>
</div>

<!-- Date Range Filter -->
<div class="bg-white rounded-lg shadow p-4 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <span class="text-slate-600">Time Period:</span>
        <select class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option>Last 7 Days</option>
            <option>Last 30 Days</option>
            <option>Last 90 Days</option>
            <option>This Year</option>
        </select>
    </div>
    <button class="text-blue-600 hover:text-blue-800 text-sm">
        <i class="fas fa-download mr-1"></i> Export Report
    </button>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <span class="text-blue-100 text-sm">Total Revenue</span>
            <i class="fas fa-naira-sign text-2xl opacity-50"></i>
        </div>
        <p class="text-3xl font-bold">₦{{ number_format($revenueData['total_revenue'] ?? 0) }}</p>
        <p class="text-blue-100 text-sm mt-2">
            <i class="fas fa-arrow-up mr-1"></i>
            {{ $revenueData['revenue_change'] ?? 0 }}% from last period
        </p>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <span class="text-green-100 text-sm">Total Orders</span>
            <i class="fas fa-shopping-cart text-2xl opacity-50"></i>
        </div>
        <p class="text-3xl font-bold">{{ $revenueData['total_orders'] ?? 0 }}</p>
        <p class="text-green-100 text-sm mt-2">
            <i class="fas fa-arrow-up mr-1"></i>
            {{ $revenueData['orders_change'] ?? 0 }}% from last period
        </p>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <span class="text-purple-100 text-sm">Total Customers</span>
            <i class="fas fa-users text-2xl opacity-50"></i>
        </div>
        <p class="text-3xl font-bold">{{ $customerData['total_customers'] ?? 0 }}</p>
        <p class="text-purple-100 text-sm mt-2">
            <i class="fas fa-arrow-up mr-1"></i>
            {{ $customerData['customers_change'] ?? 0 }}% from last period
        </p>
    </div>

    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between mb-4">
            <span class="text-orange-100 text-sm">Conversion Rate</span>
            <i class="fas fa-percentage text-2xl opacity-50"></i>
        </div>
        <p class="text-3xl font-bold">{{ $conversionData['conversion_rate'] ?? 0 }}%</p>
        <p class="text-orange-100 text-sm mt-2">
            <i class="fas fa-arrow-up mr-1"></i>
            {{ $conversionData['conversion_change'] ?? 0 }}% from last period
        </p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue Trend -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Revenue Trend</h3>
        <div class="h-64 flex items-center justify-center bg-slate-50 rounded-lg">
            <p class="text-slate-400">Revenue chart visualization</p>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-slate-800">₦{{ number_format($revenueData['today'] ?? 0) }}</p>
                <p class="text-sm text-slate-500">Today</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">₦{{ number_format($revenueData['this_week'] ?? 0) }}</p>
                <p class="text-sm text-slate-500">This Week</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800">₦{{ number_format($revenueData['this_month'] ?? 0) }}</p>
                <p class="text-sm text-slate-500">This Month</p>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Top Selling Products</h3>
        @if(count($productData['top_products'] ?? []) > 0)
        <div class="space-y-4">
            @foreach($productData['top_products'] as $index => $product)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center text-xs font-bold text-blue-600">{{ $index + 1 }}</span>
                    <span class="text-slate-700">{{ $product['title'] }}</span>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-slate-800">₦{{ number_format($product['revenue']) }}</p>
                    <p class="text-xs text-slate-500">{{ $product['orders'] }} orders</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-400 text-center py-8">No product data yet</p>
        @endif
    </div>
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Traffic Sources -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Traffic Sources</h3>
        @if(count($trafficData['sources'] ?? []) > 0)
        <div class="space-y-3">
            @foreach($trafficData['sources'] as $source => $visits)
            <div class="flex items-center justify-between">
                <span class="text-slate-700">{{ $source }}</span>
                <span class="font-semibold text-slate-800">{{ $visits }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-400 text-center py-8">No traffic data</p>
        @endif
        <div class="mt-4 pt-4 border-t border-slate-100">
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Total Visits</span>
                <span class="font-semibold text-slate-800">{{ $trafficData['total_visits'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Funnel Performance -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Funnel Performance</h3>
        <div class="space-y-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $funnelData['visitors'] ?? 0 }}</p>
                <p class="text-sm text-slate-500">Visitors</p>
            </div>
            <div class="h-2 bg-slate-100 rounded-full">
                <div class="h-2 bg-blue-500 rounded-full" style="width: 100%"></div>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-indigo-600">{{ $funnelData['leads'] ?? 0 }}</p>
                <p class="text-sm text-slate-500">Leads</p>
            </div>
            <div class="h-2 bg-slate-100 rounded-full">
                <div class="h-2 bg-indigo-500 rounded-full" style="width: {{ $funnelData['visitor_to_lead'] ?? 0 }}%"></div>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600">{{ $funnelData['customers'] ?? 0 }}</p>
                <p class="text-sm text-slate-500">Customers</p>
            </div>
            <div class="h-2 bg-slate-100 rounded-full">
                <div class="h-2 bg-green-500 rounded-full" style="width: {{ $funnelData['lead_to_customer'] ?? 0 }}%"></div>
            </div>
        </div>
    </div>

    <!-- Affiliate Stats -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Affiliate Performance</h3>
        <div class="space-y-4">
            <div class="flex justify-between">
                <span class="text-slate-600">Total Affiliates</span>
                <span class="font-bold text-slate-800">{{ $affiliateData['total_affiliates'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Active</span>
                <span class="font-bold text-green-600">{{ $affiliateData['active_affiliates'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Total Referrals</span>
                <span class="font-bold text-slate-800">{{ $affiliateData['total_referrals'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Commission Paid</span>
                <span class="font-bold text-slate-800">₦{{ number_format($affiliateData['commission_paid'] ?? 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Pending Payout</span>
                <span class="font-bold text-orange-600">₦{{ number_format($affiliateData['pending_payout'] ?? 0) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Third Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Recent Orders</h3>
        @if(count($orderData['recent'] ?? []) > 0)
        <div class="space-y-3">
            @foreach($orderData['recent'] as $order)
            <div class="flex items-center justify-between py-2 border-b border-slate-100">
                <div>
                    <p class="font-medium text-slate-800">{{ $order['customer_name'] }}</p>
                    <p class="text-xs text-slate-500">{{ $order['created_at'] }}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-slate-800">₦{{ number_format($order['final_amount']) }}</p>
                    <span class="text-xs px-2 py-1 rounded-full 
                        {{ $order['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $order['payment_status'] }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-400 text-center py-8">No orders yet</p>
        @endif
    </div>

    <!-- Geographic Data -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Geographic Distribution</h3>
        @if(count($geoData['locations'] ?? []) > 0)
        <div class="space-y-3">
            @foreach($geoData['locations'] as $location)
            <div class="flex items-center justify-between">
                <span class="text-slate-700">{{ $location['country'] }}</span>
                <div class="flex items-center gap-2">
                    <div class="w-24 bg-slate-100 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $location['percentage'] }}%"></div>
                    </div>
                    <span class="text-sm font-medium">{{ $location['orders'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-400 text-center py-8">No geographic data available</p>
        @endif
    </div>
</div>
@endsection