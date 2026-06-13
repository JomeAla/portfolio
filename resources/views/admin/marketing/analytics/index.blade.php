@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-800">Marketing Analytics</h1>
        <p class="text-slate-600 mt-2">Track performance across your marketing funnel</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Total Leads</p>
                    <p class="text-3xl font-bold text-slate-800">{{ $funnel['total_leads'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Revenue (Orders)</p>
                    <p class="text-3xl font-bold text-green-600">${{ number_format($funnel['revenue'], 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Email Open Rate</p>
                    <p class="text-3xl font-bold text-indigo-600">{{ $emailStats['open_rate'] }}%</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-envelope-open text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-sm">Email Click Rate</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $emailStats['click_rate'] }}%</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-mouse-pointer text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Lead Sources -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Lead Sources</h2>
            @if(count($sources) > 0)
            <div class="space-y-3">
                @foreach($sources as $source => $count)
                <div class="flex items-center justify-between">
                    <span class="text-slate-700">{{ $source ?: 'Direct' }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 bg-slate-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($count / $funnel['total_leads']) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-medium">{{ $count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-500">No lead source data</p>
            @endif
        </div>

        <!-- Email Performance -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Email Performance</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-4 rounded text-center">
                    <div class="text-2xl font-bold text-slate-800">{{ $emailStats['sent'] }}</div>
                    <div class="text-sm text-slate-500">Emails Sent</div>
                </div>
                <div class="bg-slate-50 p-4 rounded text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $emailStats['opened'] }}</div>
                    <div class="text-sm text-slate-500">Opened</div>
                </div>
                <div class="bg-slate-50 p-4 rounded text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $emailStats['clicked'] }}</div>
                    <div class="text-sm text-slate-500">Clicked</div>
                </div>
                <div class="bg-slate-50 p-4 rounded text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ $emailStats['delivered'] }}</div>
                    <div class="text-sm text-slate-500">Delivered</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.marketing.analytics.funnel') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-filter text-purple-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Marketing Funnel</h3>
                    <p class="text-sm text-slate-500">View conversion funnel</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.marketing.analytics.revenue') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Revenue Attribution</h3>
                    <p class="text-sm text-slate-500">Track lead-to-revenue</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.marketing.analytics.campaigns') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bullhorn text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Campaign ROI</h3>
                    <p class="text-sm text-slate-500">Track campaign performance</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection