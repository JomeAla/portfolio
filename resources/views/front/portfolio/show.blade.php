@extends('layouts.app')

@section('title', $project->title . ' — Case Study')
@section('meta_description', $project->description)
@section('og_title', $project->title . ' — Case Study')
@section('og_description', $project->description)
@section('og_image', $project->thumbnail ? asset($project->thumbnail) : asset('joala-og-image.png'))

@section('content')
<section class="min-h-screen bg-slate-50">
    {{-- Hero --}}
    <div class="relative bg-slate-900 overflow-hidden">
        @if($project->thumbnail)
        <div class="absolute inset-0 opacity-40">
            <img src="{{ asset($project->thumbnail) }}" alt="{{ $project->title }} — featured project image" class="w-full h-full object-cover">
        </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-2 text-blue-300 hover:text-white transition-colors mb-8">
                <i class="fas fa-arrow-left"></i> Back to Portfolio
            </a>
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-sm font-medium">{{ ucfirst($project->category) }}</span>
                @if($project->industry)
                <span class="px-3 py-1 bg-slate-700/50 text-slate-400 rounded-full text-sm">{{ $project->industry }}</span>
                @endif
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight">{{ $project->title }}</h1>
            @if($project->description)
            <p class="text-xl text-slate-300 mt-4 max-w-2xl">{{ $project->description }}</p>
            @endif
            <div class="flex flex-wrap gap-6 mt-8">
                @if($project->client_name)
                <div>
                    <p class="text-sm text-slate-400">Client</p>
                    <p class="font-semibold text-white">{{ $project->client_name }}</p>
                </div>
                @endif
                @if($project->duration)
                <div>
                    <p class="text-sm text-slate-400">Duration</p>
                    <p class="font-semibold text-white">{{ $project->duration }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {{-- Problem & Solution --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
            @if($project->problem_solved)
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-3">The Problem</h2>
                <p class="text-slate-600 leading-relaxed">{{ $project->problem_solved }}</p>
            </div>
            @endif
            @if($project->solution)
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4">
                    <i class="fas fa-lightbulb text-emerald-600 text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-3">The Solution</h2>
                <p class="text-slate-600 leading-relaxed">{{ $project->solution }}</p>
            </div>
            @endif
        </div>

        {{-- Tech Stack --}}
        @if($project->technologies)
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 mb-16">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Technology Stack</h2>
            <div class="flex flex-wrap gap-3">
                @foreach(is_array($project->technologies) ? $project->technologies : json_decode($project->technologies, true) as $tech)
                @php
                    $icons = [
                        'Laravel' => 'fab fa-laravel text-red-500',
                        'React' => 'fab fa-react text-blue-500',
                        'Vue' => 'fab fa-vuejs text-emerald-500',
                        'WordPress' => 'fab fa-wordpress text-blue-600',
                        'Shopify' => 'fab fa-shopify text-emerald-600',
                        'PHP' => 'fab fa-php text-indigo-500',
                        'JavaScript' => 'fab fa-js text-yellow-500',
                        'Python' => 'fab fa-python text-blue-600',
                        'HTML' => 'fab fa-html5 text-orange-500',
                        'CSS' => 'fab fa-css3 text-blue-500',
                        'Tailwind' => 'fab fa-css3 text-teal-500',
                        'MySQL' => 'fas fa-database text-orange-600',
                        'Node' => 'fab fa-node text-emerald-600',
                        'Git' => 'fab fa-git-alt text-orange-600',
                    ];
                    $icon = 'fas fa-code text-slate-500';
                    foreach ($icons as $key => $i) {
                        if (str_contains(strtolower($tech), strtolower($key))) { $icon = $i; break; }
                    }
                @endphp
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium">
                    <i class="{{ $icon }}"></i>
                    {{ trim($tech) }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Gallery --}}
        @if($project->images)
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Project Gallery</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach(json_decode($project->images) as $index => $image)
                <div class="aspect-video bg-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <img src="{{ asset($image) }}" alt="{{ $project->title }} — screenshot {{ $index + 1 }}" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Links & CTA --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-8 md:p-12 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">Interested in a Similar Project?</h2>
            <p class="text-blue-100 mb-8 max-w-lg mx-auto">Let's discuss your idea and build something great together.</p>
            <div class="flex flex-wrap justify-center gap-4">
                @if($project->live_url)
                <a href="{{ $project->live_url }}" target="_blank" class="inline-flex items-center gap-2 bg-white text-blue-600 font-semibold px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors">
                    <i class="fas fa-external-link-alt"></i> View Live Project
                </a>
                @endif
                @if($project->github_url)
                <a href="{{ $project->github_url }}" target="_blank" class="inline-flex items-center gap-2 bg-slate-800 text-white font-semibold px-6 py-3 rounded-xl hover:bg-slate-900 transition-colors">
                    <i class="fab fa-github"></i> View Source Code
                </a>
                @endif
                <a href="{{ route('brief.create') }}" class="inline-flex items-center gap-2 bg-emerald-500 text-white font-semibold px-6 py-3 rounded-xl hover:bg-emerald-600 transition-colors">
                    <i class="fas fa-paper-plane"></i> Start a Project Like This
                </a>
            </div>
        </div>
    </div>
</section>
@endsection