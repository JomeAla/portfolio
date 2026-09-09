@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Secure API — Webhook Events</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-4 py-3">When</th>
                    <th class="text-left px-4 py-3">Provider</th>
                    <th class="text-left px-4 py-3">Event</th>
                    <th class="text-left px-4 py-3">Event ID</th>
                    <th class="text-left px-4 py-3">Subject</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($events as $e)
                    <tr>
                        <td class="px-4 py-3">{{ $e->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($e->provider) }}</td>
                        <td class="px-4 py-3"><code>{{ $e->event_type }}</code></td>
                        <td class="px-4 py-3"><code>{{ \Illuminate\Support\Str::limit($e->event_id, 32) }}</code></td>
                        <td class="px-4 py-3">{{ $e->subject ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $e->status === 'processed' ? 'bg-emerald-100 text-emerald-800' : ($e->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $e->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-slate-500">No webhook events recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $events->links() }}</div>
    </div>

    <p class="text-sm text-slate-500 mt-4">
        Paystack webhook endpoint: <code>{{ url('/api/secure-api/webhook') }}</code> —
        verify <code>x-paystack-signature</code> (HMAC-SHA512) with your secret key.
    </p>
</div>
@endsection
