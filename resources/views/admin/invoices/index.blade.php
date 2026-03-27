@extends('layouts.admin')

@section('title', 'Invoices')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Invoices</h1>
        <a href="{{ route('admin.invoices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg">
            <i class="fas fa-plus mr-2"></i>Create Invoice
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Invoice #</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <span class="font-medium text-slate-900">{{ $invoice->invoice_number }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <p class="font-medium text-slate-900">{{ $invoice->client_name }}</p>
                            <p class="text-sm text-slate-500">{{ $invoice->client_email }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-semibold text-slate-900">₦{{ number_format($invoice->amount, 2) }}</span>
                        @if($invoice->amount_paid > 0)
                        <p class="text-xs text-slate-500">Paid: ₦{{ number_format($invoice->amount_paid, 2) }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @php
                        $statusClass = match($invoice->status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'partial' => 'bg-yellow-100 text-yellow-700',
                            'expired' => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-slate-100 text-slate-700',
                            default => 'bg-blue-100 text-blue-700'
                        };
                        @endphp
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        @if($invoice->isExpired())
                        <span class="text-red-600">Expired</span>
                        @else
                        {{ $invoice->expires_at->format('M d, H:i') }}
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="text-blue-600 hover:text-blue-800 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form action="{{ route('admin.invoices.destroy', $invoice->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                        No invoices yet. <a href="{{ route('admin.invoices.create') }}" class="text-blue-600 hover:underline">Create your first invoice</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
