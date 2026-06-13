@extends('layouts.admin')

@section('title', 'Campaigns')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Campaigns</h1>
        <p class="text-slate-600 mt-2">Group multiple sequences into campaigns</p>
    </div>
    <a href="/admin/marketing/campaigns/create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>New Campaign
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($campaigns as $campaign)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <h3 class="font-bold text-slate-800">{{ $campaign->name }}</h3>
            <span class="px-2 py-1 text-xs rounded {{ $campaign->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700' }}">
                {{ ucfirst($campaign->status) }}
            </span>
        </div>
        <p class="text-sm text-slate-600 mb-4">{{ $campaign->description ?? 'No description' }}</p>
        <div class="text-xs text-slate-500 mb-4">
            <i class="fas fa-users mr-1"></i> {{ $campaign->campaign_leads_count }} leads enrolled
        </div>
        @if(!empty($campaign->sequence_ids))
        <div class="flex flex-wrap gap-1 mb-4">
            @foreach($campaign->sequence_ids as $seqId)
                @php $seq = $sequences->firstWhere('id', $seqId) @endphp
                @if($seq)
                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">{{ $seq->name }}</span>
                @endif
            @endforeach
        </div>
        @endif
        <div class="flex gap-2">
            <a href="/admin/marketing/campaigns/{{ $campaign->id }}/edit" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
            <form method="POST" action="/admin/marketing/campaigns/{{ $campaign->id }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this campaign?')">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <p class="text-slate-500 col-span-3">No campaigns yet. Create your first campaign!</p>
    @endforelse
</div>
@endsection