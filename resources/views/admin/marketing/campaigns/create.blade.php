@extends('layouts.admin')

@section('title', isset($campaign) ? 'Edit Campaign' : 'Create Campaign')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">{{ isset($campaign) ? 'Edit Campaign' : 'Create Campaign' }}</h1>
        <p class="text-slate-600 mt-2">Group multiple email sequences into a campaign</p>
    </div>
</div>

<form method="POST" action="{{ isset($campaign) ? '/admin/marketing/campaigns/' . $campaign->id : '/admin/marketing/campaigns' }}" class="space-y-6">
    @csrf
    @if(isset($campaign))
    @method('PUT')
    @endif
    
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Campaign Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Campaign Name *</label>
                <input type="text" name="name" value="{{ $campaign->name ?? '' }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                <select name="status" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                    <option value="draft" {{ (isset($campaign) && $campaign->status == 'draft') ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ (isset($campaign) && $campaign->status == 'active') ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ (isset($campaign) && $campaign->status == 'paused') ? 'selected' : '' }}>Paused</option>
                    <option value="completed" {{ (isset($campaign) && $campaign->status == 'completed') ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2">{{ $campaign->description ?? '' }}</textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Start Date</label>
                <input type="datetime-local" name="start_date" value="{{ isset($campaign) && $campaign->start_date ? $campaign->start_date->format('Y-m-d\TH:i') : '' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
                <input type="datetime-local" name="end_date" value="{{ isset($campaign) && $campaign->end_date ? $campaign->end_date->format('Y-m-d\TH:i') : '' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Email Sequences</h2>
        <p class="text-sm text-slate-600 mb-4">Select sequences to include in this campaign</p>
        
        <div class="space-y-3">
            @forelse($sequences as $seq)
            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg hover:bg-slate-50">
                <input type="checkbox" name="sequence_ids[]" value="{{ $seq->id }}" 
                    {{ isset($campaign) && in_array($seq->id, $campaign->sequence_ids ?? []) ? 'checked' : '' }}
                    class="rounded text-indigo-600">
                <div>
                    <div class="font-medium text-slate-800">{{ $seq->name }}</div>
                    <div class="text-sm text-slate-500">{{ $seq->steps->count() }} steps</div>
                </div>
            </label>
            @empty
            <p class="text-slate-500">No sequences available. Create sequences first.</p>
            @endforelse
        </div>
    </div>
    
    <div class="flex gap-4">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
            {{ isset($campaign) ? 'Update Campaign' : 'Create Campaign' }}
        </button>
        <a href="/admin/marketing/campaigns" class="px-6 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</a>
    </div>
</form>
@endsection