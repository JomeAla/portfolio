@extends('front.customer.layout')

@section('customer-content')
<h1 class="text-3xl font-bold text-slate-800 mb-8">Request a Refund</h1>

<div class="bg-white rounded-xl shadow-sm p-8">
    @if(session('success'))
    <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
        {{ session('error') }}
    </div>
    @endif
    
    <p class="text-slate-600 mb-6">Fill out the form below to request a refund for your order. We'll review your request within 24-48 hours.</p>
    
    <form method="POST" action="/customer/refund">
        @csrf
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Order Number</label>
            <select name="order_id" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                <option value="">Select an order</option>
                @foreach($orders as $order)
                <option value="{{ $order['id'] }}">
                    {{ $order['order_number'] }} - ₦{{ number_format($order['final_amount']) }} ({{ date('M d, Y', strtotime($order['created_at'])) }})
                </option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Reason for Refund</label>
            <select name="reason" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500">
                <option value="">Select a reason</option>
                <option value="product_not_received">Product not received</option>
                <option value="product_not_as_described">Product not as described</option>
                <option value="duplicate_charge">Duplicate charge</option>
                <option value="accidental_purchase">Accidental purchase</option>
                <option value="other">Other</option>
            </select>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Additional Details</label>
            <textarea name="details" rows="4" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500" placeholder="Please describe your issue in detail..."></textarea>
        </div>
        
        <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700">Submit Refund Request</button>
    </form>
</div>
@endsection