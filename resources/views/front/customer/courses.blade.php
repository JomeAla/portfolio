@extends('front.customer.layout')

@section('customer-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900">My Courses</h1>
    <p class="text-slate-600">Access your enrolled courses</p>
</div>

@if(count($courses ?? []) > 0)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($courses as $enrollment)
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition-shadow">
        @if(!empty($enrollment['thumbnail']))
        <img src="{{ $enrollment['thumbnail'] }}" alt="{{ $enrollment['title'] }}" class="w-full h-40 object-cover">
        @else
        <div class="w-full h-40 bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center">
            <i class="fas fa-graduation-cap text-4xl text-white"></i>
        </div>
        @endif
        <div class="p-5">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($enrollment['difficulty'] ?? 'Beginner') }}</span>
                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">{{ $enrollment['lessons_count'] ?? 0 }} lessons</span>
            </div>
            <h3 class="font-semibold text-slate-900 mb-2">{{ $enrollment['title'] }}</h3>
            <p class="text-sm text-slate-600 mb-4">{{ substr($enrollment['description'] ?? '', 0, 100) }}...</p>
            
            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-600">Progress</span>
                    <span class="font-medium text-slate-900">{{ $enrollment['progress'] ?? 0 }}%</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-violet-600 rounded-full" style="width: {{ $enrollment['progress'] ?? 0 }}%"></div>
                </div>
            </div>
            
            <a href="/courses/{{ $enrollment['id'] }}" class="block text-center bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 rounded-xl transition-colors">
                Continue Learning
            </a>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-graduation-cap text-2xl text-slate-400"></i>
    </div>
    <p class="text-slate-600 mb-2">No courses yet</p>
    <p class="text-sm text-slate-500 mb-6">When you purchase courses, they'll appear here</p>
    <a href="/" class="inline-flex items-center gap-2 text-blue-600 font-medium">
        Browse Courses <i class="fas fa-arrow-right"></i>
    </a>
</div>
@endif
@endsection