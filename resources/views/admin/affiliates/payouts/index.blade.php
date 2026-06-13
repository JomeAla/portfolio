@extends('layouts.admin')

@section('title', 'Affiliate Payouts')

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Affiliate Payouts</h1>
            <p class="text-slate-600 mt-2">Manage payout requests from affiliate partners</p>
        </div>
    </div>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Affiliate</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($payouts ?? [] as $payout)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">{{ $payout['id'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $payout['affiliate_name'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payout['email'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-semibold">&#8358;{{ number_format($payout['amount']) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ $payout['bank_name'] ?? 'N/A' }}<br>
                    <span class="text-gray-500">{{ $payout['bank_account_number'] ?? '' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        {{ $payout['status'] == 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $payout['status'] == 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $payout['status'] == 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $payout['status'] == 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($payout['status']) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $payout['created_at'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    @if($payout['status'] == 'pending' || $payout['status'] == 'approved')
                    <form action="{{ route('admin.affiliates.payouts.complete', $payout['id']) }}" method="POST" class="inline" onsubmit="return confirm('Mark payout as completed?')">
                        @csrf
                        <button type="submit" class="text-emerald-600 hover:text-emerald-900 mr-2"><i class="fas fa-check"></i> Complete</button>
                    </form>
                    <form action="{{ route('admin.affiliates.payouts.reject', $payout['id']) }}" method="POST" class="inline" onsubmit="return confirm('Reject this payout?')">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-times"></i> Reject</button>
                    </form>
                    @else
                    <span class="text-gray-400">Processed</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-12 text-center text-gray-500">No payout requests yet</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection