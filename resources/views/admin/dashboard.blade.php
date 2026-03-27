@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-2">Welcome back! Here's an overview of your portfolio.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Projects</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['projects'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-briefcase text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Services</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['services'] }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-code text-emerald-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Testimonials</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['testimonials'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-quote-left text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border-slate-200/50">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">New Briefs</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ $stats['new_briefs'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-envelope text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Briefs -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50">
    <div class="p-6 border-b border-slate-200/50">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Recent Project Briefs</h2>
            <a href="{{ route('admin.briefs') }}" class="text-blue-600 hover:text-blue-700 font-medium">View All</a>
        </div>
    </div>
    
    <div class="divide-y divide-slate-200/50">
        @forelse($recentBriefs as $brief)
        <div class="p-6 hover:bg-slate-50/50 transition-colors">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h3 class="font-medium text-slate-900">{{ $brief->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1 line-clamp-1">{{ $brief->description }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ $brief->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                        @if($brief->status === 'new') bg-blue-100 text-blue-700
                        @elseif($brief->status === 'contacted') bg-yellow-100 text-yellow-700
                        @elseif($brief->status === 'in_progress') bg-purple-100 text-purple-700
                        @else bg-green-100 text-green-700 @endif">
                        {{ ucfirst($brief->status) }}
                    </span>
                    <a href="{{ route('admin.briefs.show', $brief) }}" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="p-6 text-center text-gray-500">
            No project briefs yet.
        </div>
        @endforelse
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
    <a href="{{ route('admin.projects.create') }}" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-plus-circle text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Add New Project</h3>
        <p class="text-blue-100 text-sm mt-1">Showcase your latest work</p>
    </a>
    
    <a href="{{ route('admin.settings') }}" class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-palette text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">Customize Design</h3>
        <p class="text-emerald-100 text-sm mt-1">Update colors, logo, fonts</p>
    </a>
    
    <a href="{{ route('brief.create') }}" target="_blank" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white hover:shadow-lg transition-all hover:-translate-y-1">
        <i class="fas fa-external-link-alt text-2xl mb-3"></i>
        <h3 class="font-semibold text-lg">View Live Site</h3>
        <p class="text-purple-100 text-sm mt-1">Open your portfolio website</p>
    </a>
</div>
@endsection