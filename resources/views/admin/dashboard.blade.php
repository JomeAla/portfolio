@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-1">Welcome back!</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Leads</p>
                <p class="text-2xl font-bold text-gray-900" id="stat-leads">--</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Deals</p>
                <p class="text-2xl font-bold text-gray-900" id="stat-deals">--</p>
            </div>
            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-handshake text-emerald-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Orders (30d)</p>
                <p class="text-2xl font-bold text-gray-900" id="stat-orders">--</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-bag text-purple-600"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Revenue (30d)</p>
                <p class="text-2xl font-bold text-gray-900" id="stat-revenue">--</p>
            </div>
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-naira-sign text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Visitor Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Visitors Today</p>
                <p class="text-xl font-bold text-gray-900" id="visitors-daily">--</p>
            </div>
            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-indigo-600 text-sm"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Visitors This Week</p>
                <p class="text-xl font-bold text-gray-900" id="visitors-weekly">--</p>
            </div>
            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-week text-purple-600 text-sm"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Visitors This Month</p>
                <p class="text-xl font-bold text-gray-900" id="visitors-monthly">--</p>
            </div>
            <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-pink-600 text-sm"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Visitors This Year</p>
                <p class="text-xl font-bold text-gray-900" id="visitors-yearly">--</p>
            </div>
            <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar text-teal-600 text-sm"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="flex gap-2 mt-4">
    <div class="bg-white rounded border border-gray-100 p-1 flex-1">
        <canvas id="leadsChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded border border-gray-100 p-1 flex-1">
        <canvas id="revenueChart" height="200"></canvas>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="/admin/projects" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-briefcase text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Manage Projects</h3>
        <p class="text-blue-100 text-sm mt-1">Add or edit your portfolio</p>
    </a>
    
    <a href="/admin/services" class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-code text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Manage Services</h3>
        <p class="text-emerald-100 text-sm mt-1">Update your service offerings</p>
    </a>
    
    <a href="/admin/settings" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-cog text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Settings</h3>
        <p class="text-purple-100 text-sm mt-1">Customize your site</p>
    </a>
    
    <a href="/admin/testimonials" class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-quote-left text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Testimonials</h3>
        <p class="text-orange-100 text-sm mt-1">Manage client reviews</p>
    </a>
    
    <a href="/admin/briefs" class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-envelope text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Project Briefs</h3>
        <p class="text-cyan-100 text-sm mt-1">View client requests</p>
    </a>
    
    <a href="/admin/products" class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-shopping-bag text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Products</h3>
        <p class="text-pink-100 text-sm mt-1">Manage digital products</p>
    </a>

    <a href="/admin/coupons" class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-tag text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Coupons</h3>
        <p class="text-yellow-100 text-sm mt-1">Manage discount codes</p>
    </a>

    <a href="/admin/banners" class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-bullhorn text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Banners</h3>
        <p class="text-indigo-100 text-sm mt-1">Promotional banners</p>
    </a>

    <a href="/admin/support" class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-life-ring text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Support</h3>
        <p class="text-teal-100 text-sm mt-1">Manage support tickets</p>
    </a>

    <a href="/admin/orders" class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-shopping-cart text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Orders</h3>
        <p class="text-rose-100 text-sm mt-1">View customer orders</p>
    </a>

    <a href="/" target="_blank" class="bg-gradient-to-br from-slate-500 to-slate-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-external-link-alt text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">View Live Site</h3>
        <p class="text-slate-100 text-sm mt-1">Open your portfolio</p>
    </a>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/admin/stats')
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-leads').textContent = data.leads ?? 0;
            document.getElementById('stat-deals').textContent = data.deals ?? 0;
            document.getElementById('stat-orders').textContent = data.orders ?? 0;
            document.getElementById('stat-revenue').textContent = '₦' + (data.revenue ?? 0).toLocaleString();
            document.getElementById('visitors-daily').textContent = data.visitors_daily ?? 0;
            document.getElementById('visitors-weekly').textContent = data.visitors_weekly ?? 0;
            document.getElementById('visitors-monthly').textContent = data.visitors_monthly ?? 0;
            document.getElementById('visitors-yearly').textContent = data.visitors_yearly ?? 0;
        })
        .catch(() => {});

    fetch('/admin/chart-data')
        .then(r => r.json())
        .then(data => {
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 4 } },
                    x: { ticks: { font: { size: 10 }, maxTicksLimit: 6 } }
                }
            };

            new Chart(document.getElementById('leadsChart'), {
                type: 'bar',
                data: {
                    labels: data.leads_labels ?? [],
                    datasets: [{
                        data: data.leads_data ?? [],
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: chartOptions
            });

            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: data.revenue_labels ?? [],
                    datasets: [{
                        data: data.revenue_data ?? [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: { ...chartOptions, scales: { ...chartOptions.scales, y: { ...chartOptions.scales.y, ticks: { ...chartOptions.scales.y.ticks, callback: v => '₦' + v.toLocaleString() } } } }
            });
        })
        .catch(() => {});
});
</script>
@endsection
