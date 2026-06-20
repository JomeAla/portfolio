@extends('layouts.admin')

@section('title', 'Conversation Flows')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Conversation Flows</h1>
        <p class="text-gray-600 mt-2">Multi-step automated WhatsApp conversations with branching</p>
    </div>
    <a href="/admin/whatsapp/conversations/create" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>New Conversation
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Total Flows</p>
        <p class="text-2xl font-bold text-slate-800">{{ count($conversations) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Active</p>
        <p class="text-2xl font-bold text-green-600">{{ $conversations->where('is_active', true)->count() }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Active Conversations</p>
        <p class="text-2xl font-bold text-blue-600">{{ $activeLogs }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Name</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Trigger</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Steps</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Active</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Created</th>
                <th class="px-6 py-4 text-right text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($conversations as $c)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $c->name }}</td>
                <td class="px-6 py-4 text-slate-600 text-sm">{{ str_replace('_', ' ', ucfirst($c->trigger_event)) }}</td>
                <td class="px-6 py-4 text-slate-600">{{ count($c->steps ?? []) }}</td>
                <td class="px-6 py-4">
                    <form method="POST" action="/admin/whatsapp/conversations/{{ $c->id }}/toggle" class="inline">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-xs font-medium rounded-full {{ $c->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $c->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-slate-500 text-sm">{{ $c->created_at->format('M j, Y') }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/whatsapp/conversations/{{ $c->id }}" class="text-blue-600 hover:text-blue-800 mr-2"><i class="fas fa-eye"></i></a>
                    <a href="/admin/whatsapp/conversations/{{ $c->id }}/edit" class="text-amber-600 hover:text-amber-800 mr-2"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="/admin/whatsapp/conversations/{{ $c->id }}/delete" class="inline" onsubmit="return confirm('Delete?')">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No conversation flows yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4 text-right">
    <a href="/admin/whatsapp/conversations/logs" class="text-indigo-600 hover:underline text-sm"><i class="fas fa-history mr-1"></i> View Conversation Logs</a>
</div>
@endsection
