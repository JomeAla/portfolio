@extends('layouts.admin')

@section('title', 'A/B Test: ' . $funnel->name)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="/admin/marketing/funnels/{{ $funnel->id }}/edit" class="text-blue-600 hover:text-blue-800">&larr; Back to Funnel</a>
        <h1 class="text-3xl font-bold text-slate-800 mt-2">A/B Test Configuration</h1>
        <p class="text-slate-600">{{ $funnel->name }}</p>
    </div>
    <div class="flex gap-2">
        @if($funnel->ab_testing_enabled)
        <span class="bg-green-100 text-green-700 px-4 py-2 rounded-lg">Test Active</span>
        @elseif($funnel->ab_winner)
        <span class="bg-purple-100 text-purple-700 px-4 py-2 rounded-lg">Winner: {{ strtoupper($funnel->ab_winner) }}</span>
        @else
        <span class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg">Not Active</span>
        @endif
    </div>
</div>

@if($funnel->ab_testing_enabled || $funnel->ab_winner)
<!-- Active Test Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    @foreach($stats as $variant => $data)
    <div class="bg-white rounded-lg shadow p-6 {{ $funnel->ab_winner === $variant ? 'border-4 border-green-500' : '' }}">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-slate-800">{{ $data['name'] }}</h3>
            @if($funnel->ab_winner === $variant)
            <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">Winner</span>
            @endif
        </div>
        
        <div class="text-3xl font-bold text-blue-600 mb-2">{{ $data['conversion_rate'] }}%</div>
        <p class="text-slate-500 text-sm mb-4">Conversion Rate</p>
        
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-slate-500">Visitors:</span>
                <span class="font-bold text-slate-800">{{ $data['visitors'] }}</span>
            </div>
            <div>
                <span class="text-slate-500">Conversions:</span>
                <span class="font-bold text-slate-800">{{ $data['conversions'] }}</span>
            </div>
        </div>
        
        @if(!empty($data['url']))
        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-xs text-slate-500">URL: {{ $data['url'] }}</p>
        </div>
        @endif
    </div>
    @endforeach
</div>

<!-- Statistical Significance -->
@if(isset($significance))
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-bold text-slate-800 mb-4">Statistical Analysis</h3>
    
    @if($significance['significant'])
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2">
            <i class="fas fa-check-circle text-green-600"></i>
            <span class="font-bold text-green-800">Statistically Significant!</span>
        </div>
        <p class="text-green-700 mt-2">
            Variant {{ strtoupper($significance['winner']) }} is the winner with {{ $significance['confidence_level'] }}% confidence.
            Z-score: {{ $significance['z_score'] }}
        </p>
    </div>
    <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}/ab-test/winner">
        @csrf
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            Declare {{ strtoupper($significance['winner']) }} as Winner
        </button>
    </form>
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <div class="flex items-center gap-2">
            <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            <span class="font-bold text-yellow-800">Not Yet Significant</span>
        </div>
        <p class="text-yellow-700 mt-2">{{ $significance['reason'] }}</p>
        @if(isset($significance['variant_a_visitors']))
        <p class="text-sm text-yellow-600 mt-1">
            Sample sizes: A={{ $significance['variant_a_visitors'] }}, B={{ $significance['variant_b_visitors'] }}
            (Minimum: {{ $significance['min_required'] }})
        </p>
        @endif
    </div>
    @endif
    
    <div class="flex gap-2 mt-4">
        <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}/ab-test/stop">
            @csrf
            <input type="hidden" name="winner" value="{{ $funnel->ab_winner }}">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                Stop Test
            </button>
        </form>
        
        <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}/ab-test/reset">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700" onclick="return confirm('Reset all A/B test data?');">
                Reset Test
            </button>
        </form>
    </div>
</div>
@endif

<!-- Traffic Split Update -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-bold text-slate-800 mb-4">Traffic Split</h3>
    
    <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}/ab-test/traffic">
        @csrf
        <div class="grid grid-cols-{{ count($stats) }} gap-4">
            @foreach($stats as $variant => $data)
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">{{ $data['name'] }} (%)</label>
                <input type="number" name="split[{{ $variant }}]" value="{{ $funnel->ab_traffic_split[$variant] ?? 50 }}" min="0" max="100" class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
            @endforeach
        </div>
        <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Update Traffic Split
        </button>
    </form>
</div>

@else
<!-- Configure New A/B Test -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold text-slate-800 mb-4">Start New A/B Test</h3>
    
    <form method="POST" action="/admin/marketing/funnels/{{ $funnel->id }}/ab-test">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Minimum Sample Size</label>
            <input type="number" name="min_sample_size" value="100" min="50" class="w-full border border-slate-300 rounded-lg px-4 py-2">
            <p class="text-sm text-slate-500 mt-1">Minimum visitors per variant before declaring a winner</p>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Confidence Level (%)</label>
            <select name="confidence_level" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                <option value="90">90%</option>
                <option value="95" selected>95% (Recommended)</option>
                <option value="99">99%</option>
            </select>
            <p class="text-sm text-slate-500 mt-1">Statistical confidence required to declare winner</p>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Variant A</label>
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="variants[a][name]" value="Variant A" placeholder="Name" class="border border-slate-300 rounded-lg px-4 py-2">
                <input type="text" name="variants[a][url]" value="" placeholder="Landing Page URL" class="border border-slate-300 rounded-lg px-4 py-2">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Variant B</label>
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="variants[b][name]" value="Variant B" placeholder="Name" class="border border-slate-300 rounded-lg px-4 py-2">
                <input type="text" name="variants[b][url]" value="" placeholder="Landing Page URL" class="border border-slate-300 rounded-lg px-4 py-2">
            </div>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            Start A/B Test
        </button>
    </form>
</div>
@endif

<div class="mt-8 bg-blue-50 rounded-lg p-6">
    <h3 class="font-bold text-blue-800 mb-2">How A/B Testing Works</h3>
    <ul class="text-sm text-blue-700 space-y-2">
        <li><i class="fas fa-check mr-2"></i>Visitors are randomly assigned to Variant A or B</li>
        <li><i class="fas fa-check mr-2"></i>Conversions are tracked separately for each variant</li>
        <li><i class="fas fa-check mr-2"></i>After minimum sample size, statistical significance is calculated</li>
        <li><i class="fas fa-check mr-2"></i>When significant, you can declare the winner</li>
        <li><i class="fas fa-check mr-2"></i>All traffic then goes to the winning variant</li>
    </ul>
</div>
@endsection