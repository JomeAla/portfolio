@extends('layouts.admin')

@section('title', 'Newsletter')

@section('content')
<div class="pt-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Newsletter Subscribers</h1>
            <p class="text-slate-600">Manage and send newsletters to subscribers</p>
        </div>
        <a href="{{ route('admin.marketing.newsletter.export') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
            <i class="fas fa-download mr-2"></i>Export CSV
        </a>
    </div>

    <!-- Send Newsletter Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Send Newsletter</h2>
        <form method="POST" action="{{ route('admin.marketing.newsletter.send') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                    <input type="text" name="subject" class="w-full px-4 py-2 border border-slate-300 rounded-lg" placeholder="Newsletter subject" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Recipients</label>
                    <select name="scope" class="w-full px-4 py-2 border border-slate-300 rounded-lg" required>
                        <option value="confirmed">All Confirmed Subscribers</option>
                        <option value="all">All Subscribers</option>
                        <option value="leads">All Leads</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Send Newsletter
                    </button>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Message (HTML allowed)</label>
                <textarea name="body" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg" placeholder="Write your newsletter content here..." required></textarea>
            </div>
        </form>
        @if(session('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
        @endif
    </div>

    <!-- Subscribers List -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Source</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Subscribed</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($subscribers as $subscriber)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 text-slate-900">{{ $subscriber->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-slate-900">{{ $subscriber->email }}</td>
                    <td class="px-6 py-4">
                        @if($subscriber->confirmed)
                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Confirmed</span>
                        @else
                        <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-600 text-sm">{{ $subscriber->source ?? 'Website' }}</td>
                    <td class="px-6 py-4 text-slate-500 text-sm">{{ date('M d, Y', strtotime($subscriber->created_at)) }}</td>
                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('admin.marketing.newsletter.destroy', $subscriber->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Remove this subscriber?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">No newsletter subscribers yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($subscribers->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $subscribers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection