@extends('layouts.app')

@section('title', $project->title)

@section('content')
<section class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('portfolio') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 mb-8">
            <i class="fas fa-arrow-left mr-2"></i> Back to Portfolio
        </a>
        
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200/50">
            @if($project->thumbnail)
            <div class="aspect-video bg-slate-200">
                <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
            </div>
            @else
            <div class="aspect-video bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                <i class="fas fa-project-diagram text-5xl text-white/50"></i>
            </div>
            @endif
            
            <div class="p-8">
                <div class="flex items-center gap-4 mb-4">
                    <span class="text-sm text-blue-600 font-medium">{{ ucfirst($project->category) }}</span>
                    @if($project->industry)
                    <span class="text-sm text-slate-400">|</span>
                    <span class="text-sm text-slate-500">{{ $project->industry }}</span>
                    @endif
                </div>
                
                <h1 class="text-3xl font-bold text-slate-900 mb-4">{{ $project->title }}</h1>
                
                <div class="prose max-w-none text-slate-600 mb-8">
                    {{ $project->description }}
                </div>

                @if($project->problem_solved)
                <div class="mb-8">
                    <h3 class="font-semibold text-slate-900 mb-2">Problem Solved</h3>
                    <p class="text-slate-600">{{ $project->problem_solved }}</p>
                </div>
                @endif

                @if($project->solution)
                <div class="mb-8">
                    <h3 class="font-semibold text-slate-900 mb-2">Solution</h3>
                    <p class="text-slate-600">{{ $project->solution }}</p>
                </div>
                @endif
                
                @if($project->technologies)
                <div class="mt-8 pt-8 border-t border-slate-200">
                    <h3 class="font-semibold text-slate-900 mb-4">Technologies Used</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(is_array($project->technologies) ? $project->technologies : json_decode($project->technologies, true) as $tech)
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm">{{ trim($tech) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($project->client_name || $project->duration)
                <div class="mt-8 pt-8 border-t border-slate-200">
                    <div class="grid grid-cols-2 gap-4">
                        @if($project->client_name)
                        <div>
                            <h3 class="font-semibold text-slate-900 mb-1">Client</h3>
                            <p class="text-slate-600">{{ $project->client_name }}</p>
                        </div>
                        @endif
                        @if($project->duration)
                        <div>
                            <h3 class="font-semibold text-slate-900 mb-1">Duration</h3>
                            <p class="text-slate-600">{{ $project->duration }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="mt-8 pt-8 border-t border-slate-200 flex flex-wrap gap-4">
                    @if($project->live_url)
                    <a href="{{ $project->live_url }}" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                        <i class="fas fa-external-link-alt"></i> View Live Project
                    </a>
                    @endif
                    
                    @if($project->github_url)
                    <a href="{{ $project->github_url }}" target="_blank" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                        <i class="fab fa-github"></i> View Source Code
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @if($project->images)
        <div class="mt-8">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Project Gallery</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach(json_decode($project->images) as $image)
                <div class="aspect-video bg-slate-200 rounded-xl overflow-hidden">
                    <img src="{{ asset('storage/' . $image) }}" alt="Project image" class="w-full h-full object-cover">
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
