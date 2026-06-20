@extends('layouts.admin')

@section('title', 'Conversation Logs')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/conversations" class="text-indigo-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Conversations</a>
</div>

<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Conversation Logs</h1>
        <p class="text-gray-600 mt-2">Track active and completed conversation flows</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Contact</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Conversation</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Step</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Last Response</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Last Step</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $log->contact?->lead?->name ?? 'Unknown' }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $log->conversation?->name ?? 'Deleted' }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $log->current_step }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $log->status == 'active' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $log->status == 'completed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $log->status == 'exited' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($log->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500 max-w-xs truncate">{{ $log->last_response ?: '-' }}</td>
                <td class="px-6 py-4 text-sm text-slate-500">{{ $log->last_step_at ? $log->last_step_at->diffForHumans() : '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No logs yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
