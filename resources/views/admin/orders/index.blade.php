@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Orders</h1>
        <p class="text-gray-600 mt-2">Manage customer orders and downloads</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Order #</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Customer</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Product</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Amount</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Date</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($orders as $order)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="font-mono text-sm font-medium text-slate-900">{{ $order->order_number }}</span>
                </td>
                <td class="px-6 py-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $order->customer_name }}</p>
                        <p class="text-sm text-gray-500">{{ $order->customer_email }}</p>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-slate-900">{{ $order->product->title ?? 'N/A' }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="font-semibold text-slate-900">₦{{ number_format($order->final_amount, 2) }}</span>
                    @if($order->discount > 0)
                    <p class="text-xs text-emerald-600">-₦{{ number_format($order->discount, 2) }} discount</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($order->payment_status === 'success')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Paid</span>
                    @elseif($order->payment_status === 'pending')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Failed</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $order->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4">
                    <a href="/admin/orders/{{ $order->id }}" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    No orders yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $orders->links() }}
</div>
@endsection
