@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.analytics') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Revenue Attribution</h1>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-slate-500 text-sm">Total Revenue (90 days)</div>
            <div class="text-3xl font-bold text-green-600">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-slate-500 text-sm">Total Orders</div>
            <div class="text-3xl font-bold text-slate-800">{{ $totalOrders }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-slate-500 text-sm">Avg Order Value</div>
            <div class="text-3xl font-bold text-indigo-600">${{ number_format($avgOrderValue, 2) }}</div>
        </div>
    </div>

    <!-- Revenue by Source -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Revenue by Source</h2>
        @if(count($bySource) > 0)
        <div class="space-y-3">
            @foreach($bySource as $source => $revenue)
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded">
                <span class="font-medium text-slate-700">{{ $source ?: 'Direct' }}</span>
                <span class="text-lg font-bold text-green-600">${{ number_format($revenue, 2) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-500">No revenue data yet</p>
        @endif
    </div>

    <!-- Revenue by Campaign -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Revenue by Campaign</h2>
        @if(count($campaignRevenue) > 0)
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="py-3 text-left text-sm font-medium text-slate-500">Campaign</th>
                    <th class="py-3 text-right text-sm font-medium text-slate-500">Leads</th>
                    <th class="py-3 text-right text-sm font-medium text-slate-500">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaignRevenue as $campaign)
                <tr class="border-b border-slate-100">
                    <td class="py-3 font-medium text-slate-800">{{ $campaign['name'] }}</td>
                    <td class="py-3 text-right">{{ $campaign['leads'] }}</td>
                    <td class="py-3 text-right font-bold text-green-600">${{ number_format($campaign['revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-slate-500">No campaign revenue data</p>
        @endif
    </div>
</div>
@endsection