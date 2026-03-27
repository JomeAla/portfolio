@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<section class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="text-xl font-bold text-slate-900">{{ $settings['site_name'] ?? 'JoAla' }}</a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Header -->
            <div class="bg-slate-900 text-white p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold">INVOICE</h1>
                        <p class="text-slate-400">{{ $invoice->invoice_number }}</p>
                    </div>
                    @php
                    $statusClass = match($invoice->status) {
                        'paid' => 'bg-green-500',
                        'partial' => 'bg-yellow-500',
                        'expired' => 'bg-red-500',
                        'cancelled' => 'bg-slate-500',
                        default => 'bg-blue-500'
                    };
                    @endphp
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusClass }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
            </div>

            <!-- Details -->
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-slate-500 uppercase mb-1">From</p>
                        <p class="font-semibold text-slate-900">{{ $settings['site_name'] ?? 'JoAla' }}</p>
                        <p class="text-sm text-slate-600">{{ $settings['contact_email'] ?? 'support@joala.com.ng' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 uppercase mb-1">Bill To</p>
                        <p class="font-semibold text-slate-900">{{ $invoice->client_name }}</p>
                        <p class="text-sm text-slate-600">{{ $invoice->client_email }}</p>
                        @if($invoice->client_phone)
                        <p class="text-sm text-slate-600">{{ $invoice->client_phone }}</p>
                        @endif
                    </div>
                </div>

                <div class="border-t border-b border-slate-200 py-4 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600">Description</span>
                        <span class="text-slate-600">Amount</span>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <span class="text-slate-700">{{ $invoice->description ?? 'Payment for services' }}</span>
                    <span class="text-xl font-bold text-slate-900">₦{{ number_format($invoice->amount, 2) }}</span>
                </div>

                @if($invoice->amount_paid > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex justify-between">
                        <span class="text-green-700">Amount Paid</span>
                        <span class="font-semibold text-green-700">₦{{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    @if($invoice->getBalanceDue() > 0)
                    <div class="flex justify-between mt-2">
                        <span class="text-red-700">Balance Due</span>
                        <span class="font-semibold text-red-700">₦{{ number_format($invoice->getBalanceDue(), 2) }}</span>
                    </div>
                    @endif
                </div>
                @endif

                @if($invoice->isExpired() && !$invoice->isPaid())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-700 font-medium">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        This invoice has expired. Please contact us for a new invoice.
                    </p>
                </div>
                @elseif(!$invoice->isPaid())
                <div class="mb-6">
                    <button id="payButton" onclick="initiatePayment()" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-xl transition-colors">
                        <i class="fas fa-credit-card mr-2"></i>
                        Pay ₦{{ number_format($invoice->getBalanceDue(), 2) }}
                    </button>
                    <p class="text-center text-sm text-slate-500 mt-2">
                        Secure payment via Paystack
                    </p>
                </div>
                @else
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                    <p class="text-green-700 font-semibold text-lg">Payment Complete!</p>
                    <p class="text-green-600 text-sm">Thank you for your payment.</p>
                </div>
                @endif

                <div class="text-center text-sm text-slate-500 pt-4">
                    <p>Issued: {{ $invoice->created_at->format('M d, Y') }}</p>
                    <p>Expires: {{ $invoice->expires_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Home
            </a>
        </div>
    </div>
</section>

@if(!$invoice->isPaid() && !$invoice->isExpired())
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    const paystackPublicKey = '{{ $paystackPublicKey }}';
    const invoiceNumber = '{{ $invoice->invoice_number }}';

    function initiatePayment() {
        const button = document.getElementById('payButton');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

        fetch('/invoices/' + invoiceNumber + '/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.authorization_url) {
                window.location.href = data.authorization_url;
            } else {
                alert(data.error || 'Payment initialization failed');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Pay ₦{{ number_format($invoice->getBalanceDue(), 2) }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Payment initialization failed');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Pay ₦{{ number_format($invoice->getBalanceDue(), 2) }}';
        });
    }
</script>
@endif
@endsection
