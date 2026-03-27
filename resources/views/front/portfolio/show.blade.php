@extends('layouts.app')

@section('title', $project->title)

@section('content')
<section class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('portfolio') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 mb-8">
            <i class="fas fa-arrow-left mr-2"></i> Back to Portfolio
        </a>
        
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200/50">
            @if($project->image)
            <div class="aspect-video bg-slate-200">
                <img src="{{ $project->image }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
            </div>
            @else
            <div class="aspect-video bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                <i class="fas fa-project-diagram text-5xl text-white/50"></i>
            </div>
            @endif
            
            <div class="p-8">
                <span class="text-sm text-blue-600 font-medium">{{ $project->category }}</span>
                <h1 class="text-3xl font-bold text-slate-900 mt-2 mb-4">{{ $project->title }}</h1>
                
                <div class="prose max-w-none text-slate-600">
                    {{ $project->description }}
                </div>
                
                @if($project->technologies)
                <div class="mt-8 pt-8 border-t border-slate-200">
                    <h3 class="font-semibold text-slate-900 mb-4">Technologies Used</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $project->technologies) as $tech)
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm">{{ trim($tech) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($project->link)
                <div class="mt-8">
                    <a href="{{ $project->link }}" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                        <i class="fas fa-external-link-alt"></i> View Live Project
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
