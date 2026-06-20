@extends('layouts.admin')

@section('title', 'WhatsApp Groups')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">WhatsApp Groups</h1>
        <p class="text-gray-600 mt-2">Manage WhatsApp group chat targets for broadcasts</p>
    </div>
    <a href="/admin/whatsapp/groups/create" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>Add Group
    </a>
</div>

<div class="mb-4 flex gap-2">
    <a href="/admin/whatsapp" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Broadcasts</a>
    <a href="/admin/whatsapp/contacts" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Contacts</a>
    <a href="/admin/whatsapp/groups" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium">Groups</a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Name</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Group JID</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Members</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-right text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($groups as $g)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $g->name }}</td>
                <td class="px-6 py-4 text-slate-600 text-sm font-mono">{{ $g->group_jid }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $g->member_count ?: '-' }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $g->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $g->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/whatsapp/groups/{{ $g->id }}/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="/admin/whatsapp/groups/{{ $g->id }}/toggle" class="inline mr-3">
                        @csrf
                        <button type="submit" class="text-sm {{ $g->is_active ? 'text-yellow-600' : 'text-green-600' }}">
                            {{ $g->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form method="POST" action="/admin/whatsapp/groups/{{ $g->id }}/delete" class="inline" onsubmit="return confirm('Remove this group?')">
                        @csrf
                        <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                    No WhatsApp groups added yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
