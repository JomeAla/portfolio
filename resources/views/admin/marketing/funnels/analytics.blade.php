@extends('layouts.admin')

@section('title', 'Funnel Analytics - ' . $funnel->name)

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <a href="/admin/marketing/funnels/{{ $funnel->id }}/edit" class="text-blue-600 hover:text-blue-800 text-sm">Back to Funnel</a>
            <h1 class="text-3xl font-bold text-slate-800 mt-2">{{ $funnel->name }} - Analytics</h1>
            <p class="text-slate-600">Track your funnel performance</p>
        </div>
        <div class="flex gap-2">
            <a href="/admin/marketing/funnels/{{ $funnel->id }}/leads" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">View Leads</a>
            <a href="/admin/marketing/funnels/{{ $funnel->id }}/export" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Export CSV</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-slate-500 text-sm">Total Visitors</p>
        <p class="text-3xl font-bold text-slate-800">{{ $totalLeads }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-slate-500 text-sm">Conversions</p>
        <p class="text-3xl font-bold text-green-600">{{ $converted }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-slate-500 text-sm">Total Revenue</p>
        <p class="text-3xl font-bold text-green-600">N{{ number_format($totalRevenue) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-slate-500 text-sm">Conversion Rate</p>
        @php $rate = $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 1) : 0; @endphp
        <p class="text-3xl font-bold text-purple-600">{{ $rate }}%</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-600 text-sm font-medium">Hot Leads</p>
        <p class="text-2xl font-bold text-red-700">{{ $hotLeads }}</p>
        <p class="text-xs text-red-500">Score {{ $hotThreshold }}+</p>
    </div>
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <p class="text-orange-600 text-sm font-medium">Warm Leads</p>
        <p class="text-2xl font-bold text-orange-700">{{ $warmLeads }}</p>
        <p class="text-xs text-orange-500">Score 50-{{ $hotThreshold - 1 }}</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-blue-600 text-sm font-medium">Cold Leads</p>
        <p class="text-2xl font-bold text-blue-700">{{ $coldLeads }}</p>
        <p class="text-xs text-blue-500">Score 0-49</p>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
        <p class="text-slate-600 text-sm font-medium">Avg Order Value</p>
        <p class="text-2xl font-bold text-slate-700">N{{ number_format($avgOrderValue) }}</p>
    </div>
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <p class="text-purple-600 text-sm font-medium">Upsells Accepted</p>
        <p class="text-2xl font-bold text-purple-700">{{ $upsellConversions }}</p>
        <p class="text-xs text-purple-500">N{{ number_format($upsellConversions * $upsellPrice) }} revenue</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold text-slate-800 mb-4">Stage-by-Stage Breakdown</h2>
    
    @if(count($stagesAnalytics) > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="text-left py-3 px-4 text-slate-600 font-medium">Stage</th>
                    <th class="text-center py-3 px-4 text-slate-600 font-medium">Type</th>
                    <th class="text-center py-3 px-4 text-slate-600 font-medium">Visitors</th>
                    <th class="text-center py-3 px-4 text-slate-600 font-medium">% of Total</th>
                    <th class="text-center py-3 px-4 text-slate-600 font-medium">Dropoff</th>
                    <th class="text-center py-3 px-4 text-slate-600 font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stagesAnalytics as $index => $stage)
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-sm font-bold text-slate-700">{{ $index + 1 }}</div>
                            <span class="font-medium text-slate-800">{{ $stage['name'] }}</span>
                        </div>
                    </td>
                    <td class="text-center py-3 px-4">
                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs capitalize">{{ str_replace('_', ' ', $stage['type']) }}</span>
                    </td>
                    <td class="text-center py-3 px-4 font-bold text-slate-800">{{ $stage['entered'] }}</td>
                    <td class="text-center py-3 px-4">
                        <span class="text-sm text-slate-600">{{ $stage['conversion_rate'] }}%</span>
                    </td>
                    <td class="text-center py-3 px-4">
                        @if($stage['dropoff'] > 0)
                        <span class="text-red-600 font-medium">{{ $stage['dropoff'] }}%</span>
                        @else
                        <span class="text-green-600">-</span>
                        @endif
                    </td>
                    <td class="text-center py-3 px-4">
                        <a href="/funnel/{{ $funnel->id }}/stage/{{ $stage['id'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">View Stage</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-slate-500">No stages found for this funnel.</p>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-slate-800 rounded-lg p-6 text-white">
        <h3 class="font-bold mb-4">Tracking Links</h3>
        <p class="text-sm text-slate-400 mb-4">Share these links to track visitors:</p>
        
        @foreach($funnel->stages as $stage)
        <div class="mb-2 flex items-center gap-2">
            <span class="text-slate-400 text-sm w-24">{{ $stage->name }}:</span>
            <code class="bg-slate-700 px-2 py-1 rounded text-sm flex-1 truncate">/funnel/{{ $funnel->id }}/stage/{{ $stage->id }}</code>
        </div>
        @endforeach
        
        <div class="mt-4 pt-4 border-t border-slate-700">
            <span class="text-slate-400 text-sm">Main Funnel URL:</span>
            <code class="bg-slate-700 px-2 py-1 rounded text-sm block mt-1">/f/{{ $funnel->id }}</code>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-bold text-slate-800 mb-4">Traffic Sources</h3>
        
        @if($topSources->count() > 0)
        <div class="space-y-3">
            @foreach($topSources as $source)
            <div class="flex items-center justify-between">
                <span class="text-slate-600 capitalize">{{ $source->source ?? 'Direct' }}</span>
                <span class="text-sm font-medium text-slate-800">{{ $source->count }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-500 text-sm">No source data available yet.</p>
        @endif
        
        <div class="mt-4 pt-4 border-t border-slate-200">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600">Avg Lead Score:</span>
                <span class="font-bold text-slate-800">{{ round($avgScore) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm mt-2">
                <span class="text-slate-600">Hot Threshold:</span>
                <span class="font-bold text-slate-800">{{ $hotThreshold }} points</span>
            </div>
        </div>
    </div>
</div>

@if($funnel->product)
<div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
    <div class="flex items-center justify-between">
        <div>
            <h4 class="font-bold text-green-800">Product Connected</h4>
            <p class="text-sm text-green-600">{{ $funnel->product->name }} - N{{ number_format($productPrice) }}</p>
        </div>
        @if($funnel->upsellProduct)
        <div class="text-right">
            <h4 class="font-bold text-green-800">Upsell Enabled</h4>
            <p class="text-sm text-green-600">{{ $funnel->upsellProduct->name }} - N{{ number_format($upsellPrice) }}</p>
        </div>
        @endif
    </div>
</div>
@endif
@endsection