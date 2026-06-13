@extends('layouts.admin')

@section('title', 'Refund Requests')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800">Refund Requests</h1>
    <p class="text-slate-600 mt-2">Manage customer refund requests</p>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($refunds as $refund)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">{{ $refund['id'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap">#{{ $refund['order_id'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $refund['user_email'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap">&#8358;{{ number_format($refund['amount'] ?? 0) }}</td>
                <td class="px-6 py-4">{{ Str::limit($refund['reason'], 50) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $refund['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $refund['status'] == 'approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $refund['status'] == 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $refund['status'] == 'processed' ? 'bg-blue-100 text-blue-800' : '' }}">
                        {{ ucfirst($refund['status']) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $refund['created_at'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($refund['status'] == 'pending')
                    <form method="POST" action="/admin/refunds/{{ $refund['id'] }}/approve" class="inline">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-800 mr-3">Approve</button>
                    </form>
                    <form method="POST" action="/admin/refunds/{{ $refund['id'] }}/reject" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Reject</button>
                    </form>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No refund requests found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection