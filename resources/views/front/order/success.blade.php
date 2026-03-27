@extends('layouts.app')

@section('title', 'Order Successful')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12">
    <div class="container mx-auto px-4 text-center">
        <div class="bg-white rounded-2xl shadow-sm p-8 max-w-lg mx-auto">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-4xl text-emerald-600"></i>
            </div>
            
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Payment Successful!</h1>
            <p class="text-gray-600 mb-6">Thank you for your purchase.</p>

            @if($order)
            <div class="bg-gray-50 rounded-xl p-4 text-left mb-6">
                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                <p><strong>Product:</strong> {{ $order->product->title }}</p>
                <p><strong>Amount Paid:</strong> ₦{{ number_format($order->final_amount) }}</p>
            </div>

            <div class="border-t pt-4">
                <p class="text-sm text-gray-600 mb-4">Your download link has been sent to your email address.</p>
                
                @if($order->canDownload())
                <a href="{{ route('order.download', $order->download_token) }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl">
                    <i class="fas fa-download mr-2"></i> Download Now
                </a>
                @else
                <p class="text-yellow-600 text-sm">Download link will be sent to your email.</p>
                @endif
            </div>
            @endif

            <a href="{{ route('store') }}" class="block mt-6 text-blue-600 hover:underline">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
@endsection
