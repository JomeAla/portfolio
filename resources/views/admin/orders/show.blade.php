@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="mb-8">
    <a href="/admin/orders" class="text-blue-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Orders
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Order {{ $order->order_number }}</h1>
                @if($order->payment_status === 'success')
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-emerald-100 text-emerald-700">Paid</span>
                @elseif($order->payment_status === 'pending')
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-700">Pending</span>
                @else
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-700">Failed</span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-6 mb-8">
                <div>
                    <p class="text-sm text-gray-500">Product</p>
                    <p class="font-semibold text-slate-900">{{ $order->product->title ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Order Date</p>
                    <p class="font-semibold text-slate-900">{{ $order->created_at->format('M d, Y g:i A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Original Price</p>
                    <p class="font-semibold text-slate-900">₦{{ number_format($order->amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Discount</p>
                    <p class="font-semibold text-emerald-600">-₦{{ number_format($order->discount, 2) }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Final Amount</p>
                    <p class="text-2xl font-bold text-slate-900">₦{{ number_format($order->final_amount, 2) }}</p>
                </div>
                @if($order->coupon_code)
                <div>
                    <p class="text-sm text-gray-500">Coupon Used</p>
                    <p class="font-semibold text-slate-900">{{ $order->coupon_code }}</p>
                </div>
                @endif
            </div>

            @if($order->payment_status === 'success')
            <div class="border-t pt-6">
                <h3 class="font-semibold text-slate-900 mb-4">Download Link</h3>
                <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Link expires:</p>
                        <p class="font-medium text-slate-900">{{ $order->download_expires_at->format('M d, Y g:i A') }}</p>
                    </div>
                    <form method="POST" action="/admin/orders/{{ $order->id }}/resend">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                            <i class="fas fa-envelope mr-2"></i> Resend Link
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Customer Information</h2>
            
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium text-slate-900">{{ $order->customer_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <a href="mailto:{{ $order->customer_email }}" class="text-blue-600 hover:underline">{{ $order->customer_email }}</a>
                </div>
                @if($order->customer_phone)
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <a href="tel:{{ $order->customer_phone }}" class="text-blue-600 hover:underline">{{ $order->customer_phone }}</a>
                </div>
                @endif
            </div>
        </div>

        @if($order->payment_reference)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6 mt-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Info</h2>
            
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Reference</p>
                    <p class="font-mono text-sm text-slate-900">{{ $order->payment_reference }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
