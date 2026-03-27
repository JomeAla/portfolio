@extends('layouts.admin')

@section('title', 'Support Tickets')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Support Tickets</h1>
        <p class="text-gray-600 mt-2">Manage customer support requests</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Ticket #</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Customer</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Subject</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Date</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="font-mono text-sm text-slate-900">{{ $ticket->ticket_number }}</span>
                </td>
                <td class="px-6 py-4">
                    <div>
                        <span class="font-medium text-slate-900">{{ $ticket->name }}</span>
                        <p class="text-sm text-gray-500">{{ $ticket->email }}</p>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-slate-900">{{ $ticket->subject }}</span>
                </td>
                <td class="px-6 py-4">
                    @php
                        $statusColors = [
                            'open' => 'bg-yellow-100 text-yellow-700',
                            'pending' => 'bg-blue-100 text-blue-700',
                            'closed' => 'bg-gray-100 text-gray-700',
                        ];
                    @endphp
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $ticket->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4">
                    <a href="/admin/support/{{ $ticket->id }}" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    No support tickets yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $tickets->links() }}
</div>
@endsection
