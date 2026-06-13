@extends('layouts.admin')

@section('title', 'Leads - ' . $funnel->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="/admin/marketing/funnels/{{ $funnel->id }}/edit" class="text-blue-600 hover:text-blue-800 text-sm">Back to Funnel</a>
            <h1 class="text-3xl font-bold text-slate-800 mt-2">{{ $funnel->name }} - Leads</h1>
            <p class="text-slate-600">View and manage your funnel leads</p>
        </div>
        <a href="/admin/marketing/funnels/{{ $funnel->id }}/export" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            Export CSV
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow mb-6">
    <form method="GET" class="p-4 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email..." class="border border-slate-300 rounded-lg px-4 py-2 w-64">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select name="status" class="border border-slate-300 rounded-lg px-4 py-2">
                <option value="">All Leads</option>
                <option value="hot" {{ request('status') === 'hot' ? 'selected' : '' }}>Hot (Score {{ $threshold }}+)</option>
                <option value="warm" {{ request('status') === 'warm' ? 'selected' : '' }}>Warm (Score 50-{{ $threshold - 1 }})</option>
                <option value="cold" {{ request('status') === 'cold' ? 'selected' : '' }}>Cold (Score 0-49)</option>
                <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Filter
        </button>
        @if(request()->anyFilled(['search', 'status']))
            <a href="/admin/marketing/funnels/{{ $funnel->id }}/leads" class="text-slate-600 hover:text-slate-800 px-4 py-2">
                Clear
            </a>
        @endif
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Email</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Score</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Status</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Pages</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Email Opens</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Last Activity</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Added</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-slate-600">Converted</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($leads as $lead)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4">
                    <span class="text-slate-800 font-medium">{{ $lead->email }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-lg font-bold @if($lead->score >= $threshold) text-red-600 @elseif($lead->score >= 50) text-orange-600 @else text-blue-600 @endif">
                        {{ $lead->score ?? 0 }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($lead->converted)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Converted
                        </span>
                    @elseif($lead->score >= $threshold)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Hot</span>
                    @elseif($lead->score >= 50)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Warm</span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Cold</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-slate-600">{{ $lead->pages_viewed ?? 0 }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $lead->email_opens ?? 0 }}</td>
                <td class="px-6 py-4 text-slate-600">
                    {{ $lead->last_activity?->diffForHumans() ?? '-' }}
                </td>
                <td class="px-6 py-4 text-slate-600">
                    {{ $lead->entered_at?->format('M d, Y') ?? '-' }}
                </td>
                <td class="px-6 py-4 text-slate-600">
                    {{ $lead->exited_at?->format('M d, Y') ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-slate-500">
                    No leads found. Share your funnel to start capturing leads.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($leads->hasPages())
<div class="mt-4">
    {{ $leads->links() }}
</div>
@endif
@endsection