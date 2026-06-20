@extends('layouts.admin')

@section('title', 'WhatsApp Templates')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Message Templates</h1>
        <p class="text-gray-600 mt-2">Create interactive, media, button, list, and flow templates</p>
    </div>
    <a href="/admin/whatsapp/templates/create" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>New Template
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Total Templates</p>
        <p class="text-2xl font-bold text-slate-800">{{ count($templates) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Text</p>
        <p class="text-2xl font-bold text-blue-600">{{ $templates->where('message_type','text')->count() }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Interactive</p>
        <p class="text-2xl font-bold text-purple-600">{{ $templates->where('message_type','interactive')->count() }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Media / Flow</p>
        <p class="text-2xl font-bold text-orange-600">{{ $templates->whereIn('message_type',['media','flow'])->count() }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Name</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Type</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Category</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Buttons</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Updated</th>
                <th class="px-6 py-4 text-right text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($templates as $t)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $t->name }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ $t->message_type == 'text' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $t->message_type == 'interactive' ? 'bg-purple-100 text-purple-700' : '' }}
                        {{ $t->message_type == 'media' ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $t->message_type == 'flow' ? 'bg-pink-100 text-pink-700' : '' }}">
                        {{ ucfirst($t->message_type) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-slate-600">{{ ucfirst($t->category) }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $t->button_count ?: '-' }}</td>
                <td class="px-6 py-4">
                    <form method="POST" action="/admin/whatsapp/templates/{{ $t->id }}/toggle" class="inline">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $t->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($t->status) }}
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 text-slate-500 text-sm">{{ $t->updated_at->diffForHumans() }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/whatsapp/templates/{{ $t->id }}" class="text-blue-600 hover:text-blue-800 mr-2" title="View"><i class="fas fa-eye"></i></a>
                    <a href="/admin/whatsapp/templates/{{ $t->id }}/edit" class="text-amber-600 hover:text-amber-800 mr-2" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="/admin/whatsapp/templates/{{ $t->id }}/preview" class="text-gray-400 hover:text-gray-600 mr-2" title="Preview JSON" target="_blank"><i class="fas fa-code"></i></a>
                    <form method="POST" action="/admin/whatsapp/templates/{{ $t->id }}/delete" class="inline" onsubmit="return confirm('Delete template?')">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No templates yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
