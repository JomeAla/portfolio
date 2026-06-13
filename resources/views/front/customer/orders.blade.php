@extends('front.customer.layout')

@section('customer-content')
<h1 class="text-3xl font-bold text-slate-800 mb-8">My Orders</h1>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if(count($orders ?? []) > 0)
    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Order #</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Date</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Amount</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($orders as $order)
            <tr>
                <td class="px-6 py-4 text-slate-800">{{ $order['order_number'] }}</td>
                <td class="px-6 py-4 text-slate-600">{{ date('M d, Y', strtotime($order['created_at'])) }}</td>
                <td class="px-6 py-4 text-slate-800">₦{{ number_format($order['final_amount']) }}</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-sm {{ $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($order['payment_status']) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <a href="/invoice/{{ $order['order_number'] }}" class="text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="p-8 text-center">
        <p class="text-slate-600">No orders yet.</p>
        <a href="/" class="text-blue-600 hover:underline mt-2 inline-block">Browse products</a>
    </div>
    @endif
</div>
@endsection