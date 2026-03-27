@extends('layouts.app')

@section('title', 'Portfolio')

@section('content')
<section class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900">My Portfolio</h1>
            <p class="text-lg text-slate-600 mt-4 max-w-2xl mx-auto">Showcasing my recent projects and work.</p>
        </div>
        
        @if($projects->isEmpty())
        <div class="text-center py-20">
            <div class="w-24 h-24 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-folder-open text-4xl text-slate-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 mb-2">No Projects Yet</h3>
            <p class="text-slate-600">Projects will be added soon. Check back later!</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($projects as $project)
            <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                @if($project->thumbnail)
                <div class="aspect-video bg-slate-200">
                    <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
                </div>
                @else
                <div class="aspect-video bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-project-diagram text-4xl text-white/50"></i>
                </div>
                @endif
                <div class="p-6">
                    <span class="text-sm text-blue-600 font-medium">{{ $project->category }}</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-2 mb-3">{{ $project->title }}</h3>
                    <p class="text-slate-600 text-sm mb-4">{{ Str::limit($project->description, 100) }}</p>
                    @if($project->technologies)
                    <div class="flex flex-wrap gap-1 mb-3">
                        @foreach(is_array($project->technologies) ? array_slice($project->technologies, 0, 3) : array_slice(json_decode($project->technologies, true) ?? [], 0, 3) as $tech)
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-xs rounded">{{ trim($tech) }}</span>
                        @endforeach
                    </div>
                    @endif
                    <a href="{{ route('portfolio.show', $project->slug) }}" class="text-blue-600 font-medium hover:text-blue-700">
                        View Details <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="mt-12">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
