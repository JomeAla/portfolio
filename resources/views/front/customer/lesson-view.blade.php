@extends('front.customer.layout')

@section('customer-content')
<div class="mb-6">
    <a href="/customer/my-courses" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900">
        <i class="fas fa-arrow-left"></i> Back to My Courses
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Video Player / Content -->
    <div class="lg:col-span-2">
        <!-- Video Player -->
        @if(!empty($lesson['video_url']))
        <div class="bg-black rounded-2xl overflow-hidden mb-6 aspect-video">
            <iframe 
                src="{{ $lesson['video_url'] }}" 
                class="w-full h-full"
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen>
            </iframe>
        </div>
        @else
        <div class="bg-gradient-to-br from-blue-600 to-violet-600 rounded-2xl aspect-video flex items-center justify-center mb-6">
            <i class="fas fa-play-circle text-6xl text-white opacity-50"></i>
        </div>
        @endif
        
        <!-- Lesson Title -->
        <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ $lesson['title'] }}</h1>
        <p class="text-slate-600 mb-6">{{ $lesson['description'] ?? '' }}</p>
        
        <!-- Lesson Content -->
        @if(!empty($lesson['content']))
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Lesson Content</h2>
            <div class="prose max-w-none">
                {!! nl2br(e($lesson['content'])) !!}
            </div>
        </div>
        @endif
    </div>
    
    <!-- Sidebar - Lessons List -->
    <div>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden sticky top-6">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-900">Course Content</h3>
                <p class="text-sm text-slate-500">{{ count($lessons) }} lessons</p>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                @foreach($lessons as $index => $les)
                <a href="/courses/{{ $course['id'] }}/lesson/{{ $les['id'] }}" 
                   class="flex items-start gap-3 p-4 hover:bg-slate-50 transition-colors border-b border-slate-100 {{ $les['id'] == $lesson['id'] ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $les['id'] == $lesson['id'] ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-600' }}">
                        <span class="text-xs">{{ $index + 1 }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-900 text-sm {{ $les['id'] == $lesson['id'] ? 'text-blue-600' : '' }}">{{ $les['title'] }}</p>
                        <p class="text-xs text-slate-500">{{ $les['duration_minutes'] ?? 0 }} min</p>
                    </div>
                </a>
                @endforeach
            </div>
            
            <!-- Mark Complete Button -->
            <div class="p-4 border-t border-slate-100">
                <button onclick="markComplete({{ $lesson['id'] }})" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition-colors">
                    <i class="fas fa-check-circle mr-2"></i> Mark as Complete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function markComplete(lessonId) {
    fetch('/courses/lesson/complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: 'course_id={{ $course["id"] }}&lesson_id=' + lessonId
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        if (data.is_course_complete) {
            alert('Congratulations! You have completed this course!');
            window.location.href = '/customer/my-courses';
        } else if (data.next_lesson_id) {
            window.location.href = '/courses/{{ $course["id"] }}/lesson/' + data.next_lesson_id;
        } else {
            window.location.href = '/customer/my-courses';
        }
    })
    .catch(function(err) { alert('Error updating progress'); });
}
</script>
@endsection