@extends('layouts.admin')

@section('title', 'Edit WhatsApp Group')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/groups" class="text-green-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Back to Groups
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Edit Group</h1>
    <p class="text-gray-500 mb-6">Update group details.</p>

    <form method="POST" action="/admin/whatsapp/groups/{{ $group->id }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group Name *</label>
                <input type="text" name="name" required value="{{ old('name', $group->name) }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group JID *</label>
                <input type="text" name="group_jid" required value="{{ old('group_jid', $group->group_jid) }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">{{ old('description', $group->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Approx. Member Count</label>
                <input type="number" name="member_count" min="0" value="{{ old('member_count', $group->member_count) }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Active</label>
                <select name="is_active"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    <option value="1" {{ $group->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$group->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-save mr-2"></i>Update Group
        </button>
    </form>
</div>
@endsection
