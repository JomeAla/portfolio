@extends('layouts.admin')

@section('title', 'WhatsApp Flows')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">WhatsApp Flows</h1>
        <p class="text-gray-600 mt-2">Interactive forms and experiences embedded in WhatsApp conversations</p>
    </div>
    <a href="/admin/whatsapp/flows/create" class="bg-pink-600 hover:bg-pink-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>New Flow
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Name</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Flow ID</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Created</th>
                <th class="px-6 py-4 text-right text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($flows as $flow)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $flow->name }}</td>
                <td class="px-6 py-4 text-slate-500 text-sm">{{ $flow->flow_id ?: '-' }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $flow->status == 'deployed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $flow->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}">
                        {{ ucfirst($flow->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-slate-500 text-sm">{{ $flow->created_at->format('M j, Y') }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/whatsapp/flows/{{ $flow->id }}" class="text-blue-600 hover:text-blue-800 mr-2"><i class="fas fa-eye"></i></a>
                    <a href="/admin/whatsapp/flows/{{ $flow->id }}/edit" class="text-amber-600 hover:text-amber-800 mr-2"><i class="fas fa-edit"></i></a>
                    @if($flow->status == 'draft')
                    <form method="POST" action="/admin/whatsapp/flows/{{ $flow->id }}/deploy" class="inline mr-2">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-800"><i class="fas fa-rocket"></i></button>
                    </form>
                    @endif
                    <form method="POST" action="/admin/whatsapp/flows/{{ $flow->id }}/delete" class="inline" onsubmit="return confirm('Delete?')">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No flows yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
