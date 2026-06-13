@extends('layouts.admin')

@section('title', 'Sales Funnels')

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Sales Funnels</h1>
            <p class="text-slate-600 mt-2">Build high-converting sales funnels</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/marketing/templates" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                <i class="fas fa-copy mr-2"></i>Templates
            </a>
            <form method="POST" action="/admin/marketing/funnels/health-all" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-heartbeat mr-2"></i>Check Health
                </button>
            </form>
            <a href="/admin/marketing/funnels/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>New Funnel
            </a>
        </div>
    </div>
</div>

<!-- Search Box -->
<form method="GET" action="/admin/marketing/funnels" class="mb-6">
    <div class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search funnels..." 
            class="px-4 py-2 border rounded-lg w-64">
        <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-lg">Search</button>
        @if(request('search'))
        <a href="/admin/marketing/funnels" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">Clear</a>
        @endif
    </div>
</form>

@if($funnels->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($funnels as $funnel)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-1 rounded {{ $funnel->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $funnel->is_active ? 'Active' : 'Inactive' }}
                </span>
                @if($funnel->health_score !== null)
                <span class="text-xs px-2 py-1 rounded 
                    {{ $funnel->health_score >= 80 ? 'bg-green-100 text-green-700' : '' }}
                    {{ $funnel->health_score >= 60 && $funnel->health_score < 80 ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $funnel->health_score >= 40 && $funnel->health_score < 60 ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $funnel->health_score < 40 ? 'bg-red-100 text-red-700' : '' }}">
                    <i class="fas fa-heartbeat mr-1"></i>{{ $funnel->health_score }}%
                </span>
                @else
                <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}/health" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800" title="Calculate Health">
                        <i class="fas fa-heartbeat"></i> Check
                    </button>
                </form>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="/funnel-overview?id={{ $funnel->id }}" target="_blank" class="text-purple-600 hover:text-purple-800" title="Funnel Overview">
                    <i class="fas fa-project-diagram"></i>
                </a>
                <a href="/admin/marketing/funnels/{{ $funnel->id }}/edit" class="text-blue-600 hover:text-blue-700" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="/admin/marketing/funnels/{{ $funnel->id }}/analytics" class="text-purple-600 hover:text-purple-700 ml-2" title="Analytics">
                    <i class="fas fa-chart-bar"></i>
                </a>
                <a href="/admin/marketing/funnels/{{ $funnel->id }}/leads" class="text-green-600 hover:text-green-700 ml-2" title="Leads">
                    <i class="fas fa-users"></i>
                </a>
                <a href="/admin/marketing/funnels/{{ $funnel->id }}/ab-test" class="text-orange-600 hover:text-orange-700 ml-2" title="A/B Test">
                    <i class="fas fa-flask"></i>
                </a>
                <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}/clone" class="inline">
                    @csrf
                    <button type="submit" class="text-green-600 hover:text-green-700" title="Clone">
                        <i class="fas fa-copy"></i>
                    </button>
                </form>
                <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}" class="inline" onsubmit="return confirm('Delete this funnel?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-700" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <h3 class="text-lg font-bold text-slate-800 mb-2">{{ $funnel->name }}</h3>
        <p class="text-sm text-slate-500 mb-4">{{ $funnel->description ?? 'No description' }}</p>
        
        <div class="flex items-center gap-4 text-sm text-slate-600">
            <div>
                <i class="fas fa-layer-group mr-1"></i>{{ $funnel->stages->count() }} stages
            </div>
            <div>
                <i class="fas fa-chart-line mr-1"></i>{{ $funnel->conversion_rate ?? 0 }}% conversion
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400">Type: {{ ucfirst($funnel->funnel_type) }}</span>
                <a href="/funnel-overview?id={{ $funnel->id }}" target="_blank" class="text-xs text-purple-600 hover:text-purple-800">
                    <i class="fas fa-project-diagram mr-1"></i>Funnel
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-lg shadow p-12 text-center">
    <i class="fas fa-funnel-dollar text-6xl text-slate-300 mb-4"></i>
    <h3 class="text-xl font-bold text-slate-800 mb-2">No funnels yet</h3>
    <p class="text-slate-500 mb-6">Create your first sales funnel to automate your customer journey.</p>
    <a href="/admin/marketing/funnels/create" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
        Create Your First Funnel
    </a>
</div>
@endif

<!-- Pagination -->
@if($funnels->hasPages())
<div class="mt-8 flex items-center justify-between">
    <div class="text-sm text-slate-600">
        Showing {{ $funnels->firstItem() ?? 0 }} to {{ $funnels->lastItem() ?? 0 }} of {{ $funnels->total() }} funnels
    </div>
    <div class="flex items-center gap-2">
        @if($funnels->onFirstPage())
        <span class="px-3 py-1 rounded bg-gray-100 text-gray-400 cursor-not-allowed">
            <i class="fas fa-chevron-left"></i> Previous
        </span>
        @else
        <a href="{{ $funnels->previousPageUrl() }}" class="px-3 py-1 rounded bg-slate-700 text-white hover:bg-slate-800">
            <i class="fas fa-chevron-left"></i> Previous
        </a>
        @endif
        
        @for($i = 1; $i <= $funnels->lastPage(); $i++)
            @if($i == $funnels->currentPage())
            <span class="px-3 py-1 rounded bg-blue-600 text-white">{{ $i }}</span>
            @elseif($i == 1 || $i == $funnels->lastPage() || ($i >= $funnels->currentPage() - 1 && $i <= $funnels->currentPage() + 1))
            <a href="{{ $funnels->url($i) }}" class="px-3 py-1 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-50">{{ $i }}</a>
            @elseif($i == $funnels->currentPage() - 2 || $i == $funnels->currentPage() + 2)
            <span class="px-2 text-slate-400">...</span>
            @endif
        @endfor
        
        @if($funnels->hasMorePages())
        <a href="{{ $funnels->nextPageUrl() }}" class="px-3 py-1 rounded bg-slate-700 text-white hover:bg-slate-800">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        @else
        <span class="px-3 py-1 rounded bg-gray-100 text-gray-400 cursor-not-allowed">
            Next <i class="fas fa-chevron-right"></i>
        </span>
        @endif
    </div>
</div>
@endif

<div class="mt-8 bg-blue-50 rounded-lg p-6">
    <h3 class="font-bold text-blue-800 mb-2">Pro Tips for High-Converting Funnels</h3>
    <ul class="text-sm text-blue-700 space-y-2">
        <li><i class="fas fa-check mr-2"></i>Start with a compelling lead magnet or landing page</li>
        <li><i class="fas fa-check mr-2"></i>Add 2-3 value emails before asking for the sale</li>
        <li><i class="fas fa-check mr-2"></i>Use scarcity and urgency in your sales page</li>
        <li><i class="fas fa-check mr-2"></i>Add one-time offer upsell after purchase</li>
        <li><i class="fas fa-check mr-2"></i>Follow up with thank you and upsell sequences</li>
    </ul>
</div>
@endsection