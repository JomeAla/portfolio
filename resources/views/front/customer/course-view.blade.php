@extends('front.customer.layout')

@section('customer-content')
<!-- Course Header -->
<div class="mb-8">
    <a href="/customer/courses" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 mb-4">
        <i class="fas fa-arrow-left"></i> Back to My Courses
    </a>
    
    @if(!empty($course['thumbnail']))
    <img src="{{ $course['thumbnail'] }}" alt="{{ $course['title'] }}" class="w-full h-64 object-cover rounded-2xl mb-6">
    @else
    <div class="w-full h-64 bg-gradient-to-br from-blue-600 to-violet-600 rounded-2xl mb-6 flex items-center justify-center">
        <i class="fas fa-graduation-cap text-5xl text-white"></i>
    </div>
    @endif
    
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ $course['title'] }}</h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="text-sm px-3 py-1 rounded-full bg-blue-100 text-blue-700">{{ ucfirst($course['difficulty'] ?? 'Beginner') }}</span>
                <span class="text-sm text-slate-500">{{ $course['instructor'] ?? '' }}</span>
            </div>
        </div>
        <div class="text-right">
            <div class="text-2xl font-bold text-slate-900">{{ $enrollment['progress'] ?? 0 }}%</div>
            <p class="text-sm text-slate-500">Complete</p>
        </div>
    </div>
    
    <!-- Progress Bar -->
    <div class="mt-4">
        <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-blue-500 to-violet-600 rounded-full transition-all" style="width: {{ $enrollment['progress'] ?? 0 }}%"></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Course Content -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Course Content</h2>
            </div>
            <div class="p-6">
                @if(!empty($course['description']))
                <div class="mb-6">
                    <h3 class="font-semibold text-slate-900 mb-2">About this course</h3>
                    <p class="text-slate-600">{{ $course['description'] }}</p>
                </div>
                @endif
                
                @if(count($lessons ?? []) > 0)
                <div class="space-y-3">
                    @foreach($lessons as $lesson)
                    @php $isAccessible = $loop->first || $lesson['is_free_preview'] || $lesson['is_completed'] || ($lessons[$loop->index - 1]['is_completed'] ?? $loop->first); @endphp
                    <a href="/courses/{{ $course['id'] }}/lesson/{{ $lesson['id'] }}" class="flex items-center gap-3 p-4 rounded-xl transition-colors {{ $lesson['is_completed'] ? 'bg-emerald-50 hover:bg-emerald-100' : 'bg-slate-50 hover:bg-slate-100' }}">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                            {{ $lesson['is_completed'] ? 'bg-emerald-500 text-white' : ($isAccessible ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-400') }}">
                            @if($lesson['is_completed'])
                            <i class="fas fa-check text-xs"></i>
                            @elseif($isAccessible)
                            <i class="fas fa-play text-xs"></i>
                            @else
                            <i class="fas fa-lock text-xs"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $lesson['title'] }}</p>
                            @if($lesson['description'])
                            <p class="text-sm text-slate-500 truncate">{{ $lesson['description'] }}</p>
                            @endif
                        </div>
                        @if(isset($lesson['duration_minutes']))
                        <span class="text-sm text-slate-500 flex-shrink-0">{{ $lesson['duration_minutes'] }} min</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-slate-500 text-center py-8">No lessons available yet for this course.</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 sticky top-24">
            <h3 class="font-semibold text-slate-900 mb-4">Update Progress</h3>
            <p class="text-sm text-slate-600 mb-4">Mark your progress as you complete lessons.</p>
            
            <div class="space-y-3">
                <div class="text-sm text-slate-600">
                    <div class="flex justify-between mb-1">
                        <span>Lessons completed</span>
                        <span class="font-medium">{{ $completedLessons ?? 0 }} / {{ $totalLessons ?? 0 }}</span>
                    </div>
                </div>
                @if(count($lessons ?? []) > 0)
                <div class="space-y-1">
                    @php $lastUncompleted = null; @endphp
                    @foreach($lessons as $l)
                    @php
                        $isDisabled = !$loop->first && !$l['is_free_preview'] && !$l['is_completed'] && $lastUncompleted !== null;
                        if (!$l['is_completed'] && $lastUncompleted === null) $lastUncompleted = $l['id'];
                    @endphp
                    @if(!$isDisabled)
                    <a href="/courses/{{ $course['id'] }}/lesson/{{ $l['id'] }}" 
                       class="flex items-center gap-2 py-2 px-3 rounded-lg text-sm transition-colors hover:bg-slate-100">
                        <i class="fas {{ $l['is_completed'] ? 'fa-check-circle text-emerald-500' : 'fa-circle text-slate-300' }}"></i>
                        <span class="truncate text-slate-600">{{ $l['title'] }}</span>
                    </a>
                    @endif
                    @endforeach
                </div>
                @endif
                <a href="/courses/{{ $course['id'] }}/lesson/{{ $nextLessonId ?? ($lessons[0]['id'] ?? '#') }}" 
                   class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-xl transition-colors">
                    <i class="fas fa-play mr-2"></i> Continue Learning
                </a>
            </div>
@endsection