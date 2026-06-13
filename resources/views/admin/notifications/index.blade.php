@extends('layouts.admin')

@section('title', 'Customer Notifications')

@section('content')
<div class="pt-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Customer Notifications</h1>
            <p class="text-slate-600">View and send notifications to customers</p>
        </div>
    </div>

    <!-- Broadcast Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Send Broadcast Notification</h2>
        <form method="POST" action="{{ route('admin.notifications.send') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Scope</label>
                    <select name="scope" class="w-full px-4 py-2 border border-slate-300 rounded-lg" required>
                        <option value="">Select scope</option>
                        <option value="all">All Customers (registered)</option>
                        <option value="customers">Paid Customers Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                    <select name="type" class="w-full px-4 py-2 border border-slate-300 rounded-lg" required>
                        <option value="general">General</option>
                        <option value="order">Order</option>
                        <option value="promo">Promotion</option>
                        <option value="course">Course</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                    <input type="text" name="title" class="w-full px-4 py-2 border border-slate-300 rounded-lg" placeholder="Notification title" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                        Send Notification
                    </button>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Message</label>
                <textarea name="message" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg" placeholder="Enter your message..." required></textarea>
            </div>
        </form>
        @if(session('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
        @endif
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Customer</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Type</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Title</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Message</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-600">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($notifications as $notif)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $notif['customer_email'] }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-1 rounded-full 
                            @switch($notif['type'])
                                @case('order') bg-blue-100 text-blue-700 @break
                                @case('promo') bg-emerald-100 text-emerald-700 @break
                                @case('course') bg-violet-100 text-violet-700 @break
                                @default bg-slate-100 text-slate-700
                            @endswitch
                        ">{{ ucfirst($notif['type']) }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-900">{{ $notif['title'] }}</td>
                    <td class="px-6 py-4 text-slate-600 text-sm">{{ substr($notif['message'] ?? '', 0, 50) }}...</td>
                    <td class="px-6 py-4">
                        @if($notif['is_read'])
                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Read</span>
                        @else
                        <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">Unread</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-500 text-sm">{{ date('M d, Y g:i A', strtotime($notif['created_at'])) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">No notifications yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection