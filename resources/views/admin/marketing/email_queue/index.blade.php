@extends('layouts.admin')

@section('title', 'Email Queue')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Email Queue</h1>
        <p class="text-slate-600 mt-2">Monitor and manage queued emails</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Recipient</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Step</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Scheduled</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sent</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($emails as $email)
            <tr>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-800">{{ $email->lead->email ?? '-' }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-600">{{ $email->subject }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm text-slate-500">Step {{ $email->sequence_step_id }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-500">{{ $email->scheduled_at ? $email->scheduled_at->format('M j, Y g:i A') : '-' }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-500">{{ $email->sent_at ? $email->sent_at->format('M j, Y g:i A') : '-' }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded 
                        {{ $email->status === 'sent' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $email->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $email->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($email->status ?? 'unknown') }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    @if($email->status === 'failed')
                        <form method="POST" action="/admin/marketing/email-queue/{{ $email->id }}/retry" class="inline">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-800">Retry</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                    No emails in queue.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $emails->links() }}
</div>
@endsection