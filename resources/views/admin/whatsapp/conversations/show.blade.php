@extends('layouts.admin')

@section('title', $conv->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/conversations" class="text-indigo-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $conv->name }}</h1>
                    <p class="text-gray-500 mt-1">{{ $conv->description }}</p>
                    <p class="text-sm text-gray-400 mt-1">Trigger: {{ str_replace('_', ' ', ucfirst($conv->trigger_event)) }}</p>
                </div>
                <form method="POST" action="/admin/whatsapp/conversations/{{ $conv->id }}/toggle" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1 text-sm font-medium rounded-full {{ $conv->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $conv->is_active ? 'Active' : 'Inactive' }}
                    </button>
                </form>
            </div>

            <h3 class="text-sm font-medium text-gray-500 mb-3">Steps ({{ count($conv->steps ?? []) }})</h3>
            <div class="space-y-4">
                @foreach($conv->steps ?? [] as $i => $step)
                <div class="border border-gray-200 rounded-xl p-4">
                    <div class="flex justify-between items-start">
                        <span class="text-xs font-medium text-gray-400">Step {{ $step['step_order'] ?? $i + 1 }}</span>
                        @if(!empty($step['delay_minutes']))
                        <span class="text-xs text-gray-400">{{ $step['delay_minutes'] }}min delay</span>
                        @endif
                    </div>
                    @if(!empty($step['template_id']))
                    <p class="text-sm mt-1">Template ID: <strong>{{ $step['template_id'] }}</strong></p>
                    @endif
                    @if(!empty($step['message']))
                    <p class="text-sm mt-1 text-gray-700 whitespace-pre-wrap">{{ $step['message'] }}</p>
                    @endif
                    @if(!empty($step['conditions']))
                    <div class="mt-2 text-xs text-gray-500">
                        <span class="font-medium">Conditions:</span>
                        @foreach($step['conditions'] as $cond)
                        <div class="ml-2">{{ $cond['field'] ?? 'message' }} {{ $cond['operator'] ?? 'contains' }} "{{ $cond['value'] ?? '' }}" → Step {{ $cond['next_step'] ?? '?' }}</div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @if($i < count($conv->steps ?? []) - 1)
                <div class="text-center text-gray-300"><i class="fas fa-arrow-down"></i></div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Recent Logs</h3>
            @forelse($logs as $log)
            <div class="border-b border-gray-100 py-2 text-sm">
                <p class="text-gray-700">{{ $log->contact?->lead?->name ?? 'Unknown' }}</p>
                <p class="text-xs text-gray-400">Step {{ $log->current_step }} · {{ $log->status }}</p>
                <p class="text-xs text-gray-400">{{ $log->updated_at->diffForHumans() }}</p>
            </div>
            @empty
            <p class="text-sm text-gray-500">No activity yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
