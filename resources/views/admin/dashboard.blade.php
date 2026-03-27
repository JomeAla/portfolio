@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-2">Welcome back! Here's an overview of your portfolio.</p>
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
