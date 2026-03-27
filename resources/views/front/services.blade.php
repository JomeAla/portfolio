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
                    <i class="fas fa-code text-2xl text-blue-600"></i>
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
                
                @if($service->pricing)
                <div class="pt-4 border-t border-slate-100">
                    <span class="text-2xl font-bold text-slate-900">{{ $service->pricing }}</span>
                </div>
                @endif
                
                <a href="{{ route('brief.create') }}" class="inline-block mt-6 w-full text-center bg-slate-900 text-white font-semibold py-3 rounded-xl hover:bg-slate-800 transition-colors">
                    Get Started
                </a>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <p class="text-slate-500">Services coming soon.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Default Services if none in database -->
        @if($services->isEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-laptop-code text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Web Development</h3>
                <p class="text-slate-600 mb-6">Custom web applications built with modern technologies like Laravel, React, and Vue.</p>
                <ul class="space-y-2 mb-6">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Custom Web Apps</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>E-commerce Solutions</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>CMS Development</li>
                </ul>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-mobile-alt text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Mobile Development</h3>
                <p class="text-slate-600 mb-6">Native and cross-platform mobile applications for iOS and Android.</p>
                <ul class="space-y-2 mb-6">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>iOS Apps</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Android Apps</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Cross-platform</li>
                </ul>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-paint-brush text-2xl text-emerald-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">UI/UX Design</h3>
                <p class="text-slate-600 mb-6">User-centered design that creates engaging and intuitive experiences.</p>
                <ul class="space-y-2 mb-6">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Wireframes</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Prototypes</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>UI Design</li>
                </ul>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-plug text-2xl text-orange-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">API Development</h3>
                <p class="text-slate-600 mb-6">RESTful APIs and third-party integrations for seamless connectivity.</p>
                <ul class="space-y-2 mb-6">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>RESTful APIs</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Third-party Integrations</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>API Documentation</li>
                </ul>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="w-16 h-16 bg-cyan-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-robot text-2xl text-cyan-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Automation</h3>
                <p class="text-slate-600 mb-6">Automate repetitive tasks and streamline business processes.</p>
                <ul class="space-y-2 mb-6">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Workflow Automation</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Data Processing</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Integration</li>
                </ul>
            </div>
            
            <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50 hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-headset text-2xl text-pink-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-3">Consultation</h3>
                <p class="text-slate-600 mb-6">Technical advice and architecture planning for your projects.</p>
                <ul class="space-y-2 mb-6">
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Technical Planning</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Architecture Design</li>
                    <li class="flex items-center gap-2 text-sm text-slate-600"><i class="fas fa-check text-emerald-500"></i>Code Review</li>
                </ul>
            </div>
        </div>
        @endif
        
        <div class="text-center mt-16">
            <a href="{{ route('brief.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-xl transition-all hover:scale-105">
                Discuss Your Project <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endsection