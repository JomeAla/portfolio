@extends('layouts.admin')

@section('title', 'Ticket Details')

@section('content')
<div class="mb-8">
    <a href="/admin/support" class="text-blue-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Tickets
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
                <span class="px-3 py-1 text-sm font-medium rounded-full 
                    @if($ticket->status === 'open') bg-yellow-100 text-yellow-700
                    @elseif($ticket->status === 'pending') bg-blue-100 text-blue-700
                    @else bg-gray-100 text-gray-700 @endif">
                    {{ ucfirst($ticket->status) }}
                </span>
            </div>

            <div class="prose max-w-none">
                <p class="text-slate-700 whitespace-pre-wrap">{{ $ticket->message }}</p>
            </div>

            @if($ticket->admin_response)
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="font-semibold text-slate-900 mb-2">Your Response</h3>
                <p class="text-slate-700 whitespace-pre-wrap">{{ $ticket->admin_response }}</p>
                <p class="text-sm text-gray-500 mt-2">Responded on {{ $ticket->responded_at->format('M d, Y g:i A') }}</p>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 mt-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Respond to Ticket</h2>
            
            <form method="POST" action="/admin/support/{{ $ticket->id }}">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Response</label>
                    <textarea name="admin_response" rows="6" required 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                        placeholder="Write your response...">{{ $ticket->admin_response }}</textarea>
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    Send Response
                </button>
            </form>

            <form method="POST" action="/admin/support/{{ $ticket->id }}" class="inline ml-3" onsubmit="return confirm('Are you sure you want to delete this ticket?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    Delete Ticket
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Customer Info</h2>
            
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="text-slate-900">{{ $ticket->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <a href="mailto:{{ $ticket->email }}" class="text-blue-600 hover:underline">{{ $ticket->email }}</a>
                </div>
                @if($ticket->phone)
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <a href="tel:{{ $ticket->phone }}" class="text-blue-600 hover:underline">{{ $ticket->phone }}</a>
                </div>
                @endif
                <div>
                    <p class="text-sm text-gray-500">Submitted</p>
                    <p class="text-slate-900">{{ $ticket->created_at->format('M d, Y g:i A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Ticket ID</p>
                    <p class="font-mono text-slate-900">{{ $ticket->ticket_number }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
