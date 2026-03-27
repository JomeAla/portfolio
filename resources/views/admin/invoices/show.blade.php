@extends('layouts.admin')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="py-6">
    <div class="mb-6">
        <a href="{{ route('admin.invoices.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Invoices
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('payment_url'))
    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-4">
        <p class="font-semibold mb-2">Payment Link Generated:</p>
        <input type="text" value="{{ session('payment_url') }}" class="w-full bg-white border border-blue-300 rounded px-3 py-2 text-sm" readonly id="paymentLink">
        <button type="button" onclick="copyPaymentLink()" class="mt-2 text-sm text-blue-600 hover:underline">
            Copy Link
        </button>
    </div>
    <script>
        function copyPaymentLink() {
            var copyText = document.getElementById('paymentLink');
            copyText.select();
            navigator.clipboard.writeText(copyText.value);
            alert('Payment link copied!');
        }
    </script>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Invoice Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ $invoice->invoice_number }}</h1>
                        <p class="text-slate-500">Created {{ $invoice->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    @php
                    $statusClass = match($invoice->status) {
                        'paid' => 'bg-green-100 text-green-700',
                        'partial' => 'bg-yellow-100 text-yellow-700',
                        'expired' => 'bg-red-100 text-red-700',
                        'cancelled' => 'bg-slate-100 text-slate-700',
                        default => 'bg-blue-100 text-blue-700'
                    };
                    @endphp
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusClass }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-600 uppercase mb-2">Bill To</h3>
                        <p class="font-medium text-slate-900">{{ $invoice->client_name }}</p>
                        <p class="text-slate-600">{{ $invoice->client_email }}</p>
                        @if($invoice->client_phone)
                        <p class="text-slate-600">{{ $invoice->client_phone }}</p>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-600 uppercase mb-2">Payment Details</h3>
                        <p class="text-slate-600">Amount: <span class="font-semibold text-slate-900">₦{{ number_format($invoice->amount, 2) }}</span></p>
                        @if($invoice->amount_paid > 0)
                        <p class="text-slate-600">Paid: <span class="font-semibold text-green-600">₦{{ number_format($invoice->amount_paid, 2) }}</span></p>
                        <p class="text-slate-600">Balance: <span class="font-semibold text-red-600">₦{{ number_format($invoice->getBalanceDue(), 2) }}</span></p>
                        @endif
                    </div>
                </div>

                @if($invoice->description)
                <div class="border-t border-slate-200 pt-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase mb-2">Description</h3>
                    <p class="text-slate-700">{{ $invoice->description }}</p>
                </div>
                @endif

                @if($invoice->paid_at)
                <div class="border-t border-slate-200 pt-6 mt-6">
                    <p class="text-green-600 font-medium">
                        <i class="fas fa-check-circle mr-2"></i>
                        Paid on {{ $invoice->paid_at->format('M d, Y H:i') }}
                    </p>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Actions</h3>
                
                <div class="space-y-3">
                    @if(!$invoice->isPaid())
                    <form method="POST" action="{{ route('admin.invoices.send', $invoice->id) }}">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg">
                            <i class="fas fa-paper-plane mr-2"></i>Send to Client
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.invoices.payment-link', $invoice->id) }}">
                        @csrf
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium px-4 py-2 rounded-lg">
                            <i class="fas fa-link mr-2"></i>Generate Payment Link
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.invoices.mark-paid', $invoice->id) }}">
                        @csrf
                        <div class="flex gap-2">
                            <input type="number" name="amount" placeholder="Amount" step="0.01" 
                                class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg">
                                Mark Paid
                            </button>
                        </div>
                    </form>
                    @endif

                    <a href="{{ route('invoices.show', $invoice->invoice_number) }}" target="_blank" 
                        class="block w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-2 rounded-lg text-center">
                        <i class="fas fa-external-link-alt mr-2"></i>View Invoice Page
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Expiry</h3>
                @if($invoice->isExpired())
                <p class="text-red-600 font-medium">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Invoice Expired
                </p>
                @else
                <p class="text-slate-600">
                    Expires: {{ $invoice->expires_at->format('M d, Y H:i') }}
                    <br>
                    <span class="text-sm">({{ $invoice->expires_at->diffForHumans() }})</span>
                </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
