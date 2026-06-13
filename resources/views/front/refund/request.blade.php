@extends('layouts.front')

@section('title', 'Request Refund')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-slate-800 rounded-2xl shadow-2xl p-8">
            <h1 class="text-3xl font-bold text-white mb-6">Request a Refund</h1>
            
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
            
            <p class="text-slate-300 mb-6">
                Please fill out the form below to request a refund. We'll review your request within 2-3 business days.
            </p>
            
            <form method="POST" action="{{ route('refund.request.submit') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Order ID (optional)</label>
                    <input type="text" name="order_id" value="{{ $orderId ?? '' }}" 
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 123">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Email Address *</label>
                    <input type="email" name="email" required
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="your@email.com">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Reason for Refund *</label>
                    <textarea name="reason" required rows="4"
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Please describe why you're requesting a refund..."></textarea>
                </div>
                
                <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                    Submit Refund Request
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <a href="/" class="text-slate-400 hover:text-white">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection