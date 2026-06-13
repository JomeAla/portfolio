@extends('layouts.admin')

@section('title', 'Cart Abandonment')

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Cart Abandonment</h1>
            <p class="text-slate-600 mt-2">Recover lost sales with automated email sequences</p>
        </div>
        <form action="{{ route('admin.marketing.cart-abandonment.process') }}" method="POST">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700">
                <i class="fas fa-play mr-2"></i>Run Abandonment Check
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
@endif

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Abandoned</div>
        <div class="text-2xl font-bold text-slate-800">{{ $stats['total_abandoned'] ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">In Sequence</div>
        <div class="text-2xl font-bold text-blue-600">{{ $stats['in_sequence'] ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Recovered</div>
        <div class="text-2xl font-bold text-emerald-600">{{ $stats['recovered'] ?? 0 }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Recovery Rate</div>
        <div class="text-2xl font-bold text-amber-600">{{ $stats['recovery_rate'] ?? '0%' }}</div>
    </div>
</div>

<!-- Abandoned Orders -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-slate-800">Abandoned Carts</h3>
    </div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Abandoned At</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($orders ?? [] as $order)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $order['order_number'] ?? $order['id'] ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $order['customer_email'] ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">&#8358;{{ number_format($order['final_amount'] ?? $order['total'] ?? 0) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $order['cart_abandoned_at'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        {{ isset($order['cart_recovered_at']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ isset($order['cart_recovered_at']) ? 'Recovered' : 'Abandoned' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No abandoned carts found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection