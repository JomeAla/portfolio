@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.analytics') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Campaign Performance & ROI</h1>
    </div>

    @if(count($campaignData) > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Campaign</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase">Leads</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase">Sent</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase">Open Rate</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase">Click Rate</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Revenue</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">ROI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($campaignData as $campaign)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $campaign['name'] }}</td>
                    <td class="px-4 py-3 text-center">{{ $campaign['leads'] }}</td>
                    <td class="px-4 py-3 text-center">{{ $campaign['sent'] }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 text-xs rounded {{ $campaign['open_rate'] > 30 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $campaign['open_rate'] }}%
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 text-xs rounded {{ $campaign['click_rate'] > 10 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $campaign['click_rate'] }}%
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-green-600">${{ number_format($campaign['revenue'], 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="px-2 py-1 text-xs rounded font-bold {{ $campaign['roi'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $campaign['roi'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <div class="text-slate-400 mb-4">
            <i class="fas fa-chart-bar text-5xl"></i>
        </div>
        <p class="text-slate-600">No campaign data available yet</p>
        <p class="text-sm text-slate-500 mt-2">Create campaigns to see performance metrics</p>
    </div>
    @endif
</div>
@endsection