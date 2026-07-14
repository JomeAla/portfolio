@extends('layouts.app')

@section('title', 'Services')
@section('meta_description', 'Custom web development, WordPress, Shopify, mobile apps & business automation services in Nigeria. Laravel, React & Vue specialist. Get a quote.')
@section('og_title', 'Web Development Services in Nigeria')
@section('og_description', 'Custom web development, WordPress, Shopify, mobile apps & business automation services in Nigeria. Laravel, React & Vue specialist. Get a quote.')

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

        <!-- FAQ Section -->
        <div class="mt-20 max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900">Frequently Asked Questions</h2>
                <p class="text-lg text-slate-600 mt-4">Common questions about working with me</p>
            </div>
            
            <div class="space-y-4">
                <details class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50 group">
                    <summary class="flex items-center justify-between cursor-pointer text-lg font-semibold text-slate-900">
                        How long does it take to build a custom web application?
                        <i class="fas fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-slate-600 leading-relaxed">Timeline depends on complexity. A simple landing page takes 3-5 days. A custom web application typically takes 2-8 weeks. During our initial consultation, I'll provide a detailed timeline based on your specific requirements.</p>
                </details>
                
                <details class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50 group">
                    <summary class="flex items-center justify-between cursor-pointer text-lg font-semibold text-slate-900">
                        How much does web development cost in Nigeria?
                        <i class="fas fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-slate-600 leading-relaxed">Costs vary widely based on scope. Basic websites start from ₦150,000, while complex web applications range from ₦500,000 to ₦5,000,000+. I provide transparent pricing after understanding your project needs through a free consultation.</p>
                </details>
                
                <details class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50 group">
                    <summary class="flex items-center justify-between cursor-pointer text-lg font-semibold text-slate-900">
                        What technologies do you use?
                        <i class="fas fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-slate-600 leading-relaxed">I specialize in Laravel for backend development, React and Vue for frontend, WordPress and Shopify for CMS/e-commerce. For mobile, I use Flutter and React Native. I choose the best tech stack based on your project requirements.</p>
                </details>
                
                <details class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50 group">
                    <summary class="flex items-center justify-between cursor-pointer text-lg font-semibold text-slate-900">
                        Do you provide post-launch support?
                        <i class="fas fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-slate-600 leading-relaxed">Yes. I offer maintenance packages that include bug fixes, security updates, performance monitoring, and feature enhancements. We can discuss a support plan that fits your budget and needs after project delivery.</p>
                </details>
                
                <details class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50 group">
                    <summary class="flex items-center justify-between cursor-pointer text-lg font-semibold text-slate-900">
                        Can you work with existing code or systems?
                        <i class="fas fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <p class="mt-4 text-slate-600 leading-relaxed">Absolutely. I frequently take over existing projects, add features to legacy systems, and integrate third-party APIs. I'll review your current codebase and provide recommendations for improvements or integrations.</p>
                </details>
            </div>
        </div>

        <div class="text-center mt-16">
            <a href="{{ route('brief.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-xl transition-all hover:scale-105">
                Discuss Your Project <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endsection