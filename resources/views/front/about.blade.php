@extends('layouts.app')

@section('title', 'About')

@section('content')
<section class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <div class="mb-6">
                @php $aboutPhoto = \App\Models\Setting::get('about_photo'); @endphp
                <img src="{{ $aboutPhoto ? asset('storage/' . $aboutPhoto) : asset('uploads/about-me.jpg') }}" alt="Jome Alawuru" class="w-56 h-56 mx-auto rounded-full object-cover object-top shadow-lg border-4 border-white mt-4">
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-slate-900">About Me</h1>
            <p class="text-lg text-slate-600 mt-4">Get to know more about me and my work.</p>
        </div>
        
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/50">
            <div class="prose max-w-none">
                <p class="text-lg text-slate-600 mb-6">
                    Hello! I'm Jome Alawuru, a passionate software developer based in Nigeria. 
                    I specialize in building modern, responsive, and user-friendly web applications.
                </p>
            
            <h2 class="text-2xl font-bold text-slate-900 mb-4">My Expertise</h2>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                <li class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-check text-emerald-500"></i> Web Development (Laravel, React, Vue)
                </li>
                <li class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-check text-emerald-500"></i> Mobile App Development
                </li>
                <li class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-check text-emerald-500"></i> UI/UX Design
                </li>
                <li class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-check text-emerald-500"></i> API Development
                </li>
                <li class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-check text-emerald-500"></i> Database Design
                </li>
                <li class="flex items-center gap-2 text-slate-600">
                    <i class="fas fa-check text-emerald-500"></i> Business Automation
                </li>
            </ul>
                
                <h2 class="text-2xl font-bold text-slate-900 mb-4">Contact</h2>
                <div class="flex flex-col gap-3 text-slate-600">
                    <a href="tel:+2349065257784" class="flex items-center gap-2 hover:text-blue-600">
                        <i class="fas fa-phone"></i> +2349065257784
                    </a>
                    <a href="mailto:jomealawuru@hotmail.com" class="flex items-center gap-2 hover:text-blue-600">
                        <i class="fas fa-envelope"></i> jomealawuru@hotmail.com
                    </a>
                    <a href="https://twitter.com/jomswoks" target="_blank" class="flex items-center gap-2 hover:text-blue-600">
                        <i class="fab fa-twitter"></i> @jomswoks
                    </a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-12">
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-xl transition-all hover:scale-105">
                Get in Touch <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
@endsection
