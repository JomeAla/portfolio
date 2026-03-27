@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<section class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-lg mx-auto px-4 text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-3xl text-green-600"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Payment Successful!</h1>
            <p class="text-slate-600 mb-6">Thank you for your payment.</p>

            <div class="bg-slate-50 rounded-xl p-4 mb-6 text-left">
                <div class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-600">Invoice</span>
                    <span class="font-semibold text-slate-900">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-200">
                    <span class="text-slate-600">Amount Paid</span>
                    <span class="font-semibold text-green-600">₦{{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
                @if($invoice->paid_at)
                <div class="flex justify-between py-2">
                    <span class="text-slate-600">Date</span>
                    <span class="font-semibold text-slate-900">{{ $invoice->paid_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>

            <p class="text-sm text-slate-500 mb-6">
                A confirmation email has been sent to {{ $invoice->client_email }}
            </p>

            <a href="{{ route('home') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl">
                Back to Home
            </a>
        </div>
    </div>
</section>
@endsection
