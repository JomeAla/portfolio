@extends('layouts.admin')

@section('title', 'Funnel Templates')

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Funnel Templates</h1>
            <p class="text-slate-600 mt-2">Use pre-built funnel templates to save time</p>
        </div>
        <a href="/admin/marketing/funnels" class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Funnels
        </a>
    </div>
</div>

@php
$templateCategories = ['lead_magnet' => 'Lead Magnet', 'tripwire' => 'Tripwire', 'webinar' => 'Webinar', 'launch' => 'Product Launch', 'affiliate' => 'Affiliate'];
$prebuiltTemplates = [
    ['id' => 'tmpl_1', 'name' => 'Lead Magnet Funnel', 'description' => 'Capture leads with a free resource, then nurture them with an email sequence to build trust and drive conversions.', 'template_category' => 'lead_magnet', 'stages' => 4, 'estimated_conversion_rate' => '15-25%'],
    ['id' => 'tmpl_2', 'name' => 'Tripwire Funnel', 'description' => 'Drive traffic to a low-cost entry product, then upsell to higher-value offers throughout the funnel.', 'template_category' => 'tripwire', 'stages' => 3, 'estimated_conversion_rate' => '20-35%'],
    ['id' => 'tmpl_3', 'name' => 'Webinar Funnel', 'description' => 'Host a webinar to demonstrate expertise and pitch a high-ticket offer to attendees.', 'template_category' => 'webinar', 'stages' => 5, 'estimated_conversion_rate' => '10-20%'],
    ['id' => 'tmpl_4', 'name' => 'Product Launch Funnel', 'description' => 'Build anticipation and launch a new product with a sequenced pre-launch and launch campaign.', 'template_category' => 'launch', 'stages' => 6, 'estimated_conversion_rate' => '8-15%'],
    ['id' => 'tmpl_5', 'name' => 'Affiliate Funnel', 'description' => 'Promote affiliate products with dedicated landing pages and email follow-up sequences.', 'template_category' => 'affiliate', 'stages' => 3, 'estimated_conversion_rate' => '12-22%'],
];
@endphp

<!-- Category Filter -->
<div class="mb-6">
    <div class="flex gap-2 flex-wrap">
        <a href="/admin/marketing/templates" class="px-4 py-2 rounded-lg {{ !request('category') ? 'bg-blue-600 text-white' : 'bg-white border border-slate-300' }}">
            All Templates
        </a>
        @foreach($templateCategories as $key => $label)
        <a href="/admin/marketing/templates?category={{ $key }}" class="px-4 py-2 rounded-lg {{ request('category') == $key ? 'bg-blue-600 text-white' : 'bg-white border border-slate-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

@if($templates->count() > 0)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    @foreach($templates as $template)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700">
                    {{ $templateCategories[$template['template_category'] ?? 'custom'] ?? 'Custom' }}
                </span>
            </div>
            <div class="text-slate-400">
                <i class="fas fa-copy"></i>
            </div>
        </div>
        
        <h3 class="text-lg font-bold text-slate-800 mb-2">{{ $template['name'] }}</h3>
        <p class="text-sm text-slate-500 mb-4">{{ $template['description'] ?? 'No description' }}</p>
        
        <div class="flex items-center gap-4 text-sm text-slate-600 mb-4">
            <div>
                <i class="fas fa-layer-group mr-1"></i>{{ $template['stages'] ?? count($template['stages'] ?? []) }} stages
            </div>
            @if(isset($template['product']) && $template['product'])
            <div>
                <i class="fas fa-box mr-1"></i>{{ $template['product'] }}
            </div>
            @endif
        </div>
        
        <form method="POST" action="/admin/marketing/templates/{{ $template['id'] }}/use">
            @csrf
            <div class="flex gap-2">
                <input type="text" name="name" value="{{ $template['name'] }}" placeholder="Funnel name" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                    Use Template
                </button>
            </div>
        </form>
    </div>
    @endforeach
</div>
@endif

<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-800 mb-4">Pre-built Funnel Templates</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($prebuiltTemplates as $pt)
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-lg shadow-lg p-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -mr-12 -mt-12"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs px-2 py-1 rounded bg-green-400/20 text-green-300">
                        {{ $templateCategories[$pt['template_category']] }}
                    </span>
                    <span class="text-xs text-green-400 font-medium">{{ $pt['estimated_conversion_rate'] }} avg. conv.</span>
                </div>
                
                <h3 class="text-lg font-bold mb-2">{{ $pt['name'] }}</h3>
                <p class="text-sm text-slate-300 mb-4">{{ $pt['description'] }}</p>
                
                <div class="flex items-center gap-4 text-sm text-slate-400 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-layer-group mr-2"></i>{{ $pt['stages'] }} stages
                    </div>
                </div>
                
                <a href="/admin/marketing/funnels/create?template={{ $pt['template_category'] }}" class="block w-full bg-white text-slate-900 text-center px-4 py-2 rounded-lg hover:bg-slate-100 font-medium text-sm transition-colors">
                    <i class="fas fa-plus mr-2"></i>Use This Template
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

@if($templates->count() === 0)
<div class="bg-white rounded-lg shadow p-12 text-center">
    <i class="fas fa-copy text-6xl text-slate-300 mb-4"></i>
    <h3 class="text-xl font-bold text-slate-800 mb-2">No saved templates yet</h3>
    <p class="text-slate-500 mb-6">Save a funnel as a template to use it here, or use one of our pre-built templates above.</p>
    <a href="/admin/marketing/funnels" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
        Go to Funnels
    </a>
</div>
@endif

<div class="mt-8 bg-blue-50 rounded-lg p-6">
    <h3 class="font-bold text-blue-800 mb-2">How to Create Templates</h3>
    <ul class="text-sm text-blue-700 space-y-2">
        <li><i class="fas fa-check mr-2"></i>Create a funnel with all the stages and settings you need</li>
        <li><i class="fas fa-check mr-2"></i>Go to the funnel edit page and click "Save as Template"</li>
        <li><i class="fas fa-check mr-2"></i>Choose a category and give it a name</li>
        <li><i class="fas fa-check mr-2"></i>Use your template anytime to create a new funnel instantly</li>
    </ul>
</div>
@endsection