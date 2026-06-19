@extends('layouts.app')

@section('title', 'Services')

@section('content')
<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900">Services</h1>
            <p class="text-lg text-slate-600 mt-4 max-w-2xl mx-auto">Comprehensive development services tailored to bring your ideas to life.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($services as $service)
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="{{ $service->icon ?: 'fas fa-code' }} text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $service->title }}</h3>
                <p class="text-slate-600 mb-6">{{ $service->description }}</p>
                
                @if($service->features)
                @php $features = is_array($service->features) ? $service->features : json_decode($service->features, true); @endphp
                @if($features && count($features) > 0)
                <ul class="space-y-2 mb-6">
                    @foreach($features as $feature)
                    <li class="flex items-center gap-2 text-sm text-slate-600">
                        <i class="fas fa-check text-emerald-500"></i>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                @endif
                @endif
                
                <a href="{{ route('brief.create') }}" class="inline-block mt-6 w-full text-center bg-slate-900 text-white font-semibold py-3 rounded-xl hover:bg-slate-800 transition-colors">
                    Get Started
                </a>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <p class="text-slate-500">No services available at the moment.</p>
            </div>
            @endforelse
        </div>
        

        
        <div class="text-center mt-16">
            <a href="{{ route('brief.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-xl transition-all hover:scale-105">
                Discuss Your Project <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endsection