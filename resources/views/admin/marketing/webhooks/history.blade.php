@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Webhook History</h1>
            <p class="text-slate-600 mt-1">Log of all webhook firings</p>
        </div>
        <a href="{{ route('admin.marketing.webhooks') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to Webhooks
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Firing History ({{ $history->total() }})</h3>
                <div class="flex gap-2">
                    <select onchange="window.location.href = '?status=' + this.value" class="text-sm border rounded px-2 py-1">
                        <option value="">All Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
            </div>
        </div>

        @if($history->isEmpty())
        <div class="p-8 text-center">
            <div class="text-slate-400 mb-2">
                <i class="fas fa-bolt text-4xl"></i>
            </div>
            <p class="text-slate-600">No webhook firings recorded yet</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">URL</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Lead</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Response</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time (ms)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($history as $record)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                            {{ $record->created_at->format('M d, H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">{{ $record->event_type }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 max-w-xs truncate" title="{{ $record->webhook_url }}">
                            {{ Str::limit($record->webhook_url, 50) }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            @if($record->lead)
                                <span class="text-slate-800">{{ $record->lead->email }}</span>
                            @elseif($record->lead_id)
                                #{{ $record->lead_id }}
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($record->response_code)
                                <span class="text-xs font-mono {{ $record->response_code >= 200 && $record->response_code < 300 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $record->response_code }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">
                            {{ $record->response_time_ms ? number_format($record->response_time_ms, 0) . 'ms' : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($record->status === 'success')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded flex items-center w-fit">
                                    <i class="fas fa-check-circle mr-1"></i> Success
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded flex items-center w-fit" title="{{ $record->error_message }}">
                                    <i class="fas fa-times-circle mr-1"></i> Failed
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="mt-4">
        {{ $history->appends(request()->query())->links() }}
    </div>
</div>
@endsection