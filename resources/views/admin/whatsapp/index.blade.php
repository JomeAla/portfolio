@extends('layouts.admin')

@section('title', 'WhatsApp Broadcasts')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">WhatsApp Broadcasts</h1>
        <p class="text-gray-600 mt-2">Create and manage WhatsApp broadcast messages</p>
    </div>
    <a href="/admin/whatsapp/create" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>New Broadcast
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Total Broadcasts</p>
        <p class="text-2xl font-bold text-slate-800">{{ count($broadcasts) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Sent</p>
        <p class="text-2xl font-bold text-green-600">{{ $sentBroadcasts }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Total Contacts</p>
        <p class="text-2xl font-bold text-blue-600">{{ $contactCount }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Opted-In</p>
        <p class="text-2xl font-bold text-purple-600">{{ $optedInCount }}</p>
    </div>
</div>

<div class="mb-4 flex gap-2">
    <a href="/admin/whatsapp" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium">Broadcasts</a>
    <a href="/admin/whatsapp/contacts" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Contacts</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Name</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Recipients</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Sent</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Failed</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Created</th>
                <th class="px-6 py-4 text-right text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($broadcasts as $broadcast)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-900">{{ $broadcast->name }}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $broadcast->status == 'sent' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $broadcast->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                        {{ $broadcast->status == 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $broadcast->status == 'sending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $broadcast->status == 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($broadcast->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-slate-600">{{ $broadcast->total_recipients ?: '-' }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $broadcast->sent_count ?: '-' }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $broadcast->failed_count ?: '-' }}</td>
                <td class="px-6 py-4 text-slate-500 text-sm">{{ $broadcast->created_at->format('M j, Y') }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/whatsapp/{{ $broadcast->id }}" class="text-blue-600 hover:text-blue-800 mr-3">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if($broadcast->status == 'draft')
                    <form method="POST" action="/admin/whatsapp/{{ $broadcast->id }}/delete" class="inline" onsubmit="return confirm('Delete this broadcast?')">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    No broadcasts yet. Create your first WhatsApp broadcast!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
