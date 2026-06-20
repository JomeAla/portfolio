@extends('layouts.admin')

@section('title', 'Add WhatsApp Group')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/groups" class="text-green-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Back to Groups
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Add WhatsApp Group</h1>
    <p class="text-gray-500 mb-6">Enter the group details to target it in broadcasts.</p>

    <form method="POST" action="/admin/whatsapp/groups" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group Name *</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                    placeholder="e.g., VIP Customers Group">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group JID *</label>
                <input type="text" name="group_jid" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                    placeholder="e.g., 123456789@g.us">
                <p class="text-xs text-gray-500 mt-1">The WhatsApp group ID ending with <code>@g.us</code></p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                placeholder="Optional description for this group">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Approx. Member Count</label>
            <input type="number" name="member_count" min="0"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                placeholder="e.g., 50">
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Group
        </button>
    </form>
</div>
@endsection
