@extends('layouts.admin')

@section('title', 'WhatsApp Contacts')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">WhatsApp Contacts</h1>
        <p class="text-gray-600 mt-2">Manage opted-in WhatsApp contacts</p>
    </div>
</div>

<div class="mb-4 flex gap-2">
    <a href="/admin/whatsapp" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Broadcasts</a>
    <a href="/admin/whatsapp/contacts" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium">Contacts</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6 lg:col-span-2">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">All Contacts</h2>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Phone</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Opted In</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Last Sent</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contacts as $contact)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $contact->lead?->name ?? 'Unknown' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $contact->phone }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $contact->opted_in ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $contact->opted_in ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-500">{{ $contact->last_sent_at ? $contact->last_sent_at->format('M j, Y') : 'Never' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="/admin/whatsapp/contacts/{{ $contact->id }}/toggle-optin" class="inline">
                            @csrf
                            <button type="submit" class="text-sm {{ $contact->opted_in ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                {{ $contact->opted_in ? 'Opt Out' : 'Opt In' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-500">No contacts yet. Import contacts to start broadcasting.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $contacts->links() }}
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Contact</h2>
        <form method="POST" action="/admin/whatsapp/contacts/import" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                <input type="text" name="phone" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                    placeholder="e.g., 2348012345678">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                    placeholder="Contact name">
            </div>
            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                <i class="fas fa-upload mr-2"></i>Import Contact
            </button>
        </form>
    </div>
</div>
@endsection
