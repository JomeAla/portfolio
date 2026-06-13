@extends('layouts.admin')

@section('title', $course['title'])

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="/admin/courses" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Back to Courses</a>
        <h1 class="text-3xl font-bold text-slate-800 mt-2">{{ $course['title'] }}</h1>
        <p class="text-slate-600">{{ $course['description'] ?? 'No description' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="/admin/courses/{{ $course['id'] }}/edit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-edit mr-1"></i> Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-slate-500 text-sm">Enrolled Students</p>
        <p class="text-3xl font-bold text-slate-800">{{ $enrollment['total'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-slate-500 text-sm">Total Lessons</p>
        <p class="text-3xl font-bold text-slate-800">{{ count($lessons) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-slate-500 text-sm">Price</p>
        <p class="text-3xl font-bold text-slate-800">
            @if($course['is_free'] || $course['price'] == 0)
                Free
            @else
                ₦{{ number_format($course['price']) }}
            @endif
        </p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-slate-800">Lessons</h2>
        <span class="px-3 py-1 text-sm rounded-full {{ $course['is_published'] ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
            {{ $course['is_published'] ? 'Published' : 'Draft' }}
        </span>
    </div>
    
    @if(count($lessons) > 0)
    <div class="space-y-3">
        @foreach($lessons as $index => $lesson)
        <div class="flex items-center gap-4 p-4 border border-slate-200 rounded-lg hover:bg-slate-50">
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold">
                {{ $index + 1 }}
            </div>
            <div class="flex-1">
                <h4 class="font-medium text-slate-800">{{ $lesson->title }}</h4>
                @if($lesson->description)
                <p class="text-sm text-slate-500">{{ Str::limit($lesson->description, 80) }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($lesson->is_published)
                <span class="text-xs text-green-600"><i class="fas fa-check-circle mr-1"></i>Published</span>
                @else
                <span class="text-xs text-gray-400">Draft</span>
                @endif
                @if($lesson->video_url)
                <span class="text-xs text-slate-400"><i class="fas fa-video mr-1"></i>Video</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-slate-500 text-center py-8">No lessons yet.</p>
    @endif
</div>
@endsection