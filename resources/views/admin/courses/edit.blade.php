@extends('layouts.admin')

@section('title', 'Edit Course')

@section('content')
<div class="mb-6">
    <a href="/admin/courses" class="text-blue-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Courses
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 mb-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Course</h1>
    
    <form method="POST" action="/admin/courses/{{ $course['id'] }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Course Title *</label>
                <input type="text" name="title" required value="{{ $course['title'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="">Select Category</option>
                    <option value="programming" {{ $course['category'] == 'programming' ? 'selected' : '' }}>Programming</option>
                    <option value="design" {{ $course['category'] == 'design' ? 'selected' : '' }}>Design</option>
                    <option value="marketing" {{ $course['category'] == 'marketing' ? 'selected' : '' }}>Marketing</option>
                    <option value="business" {{ $course['category'] == 'business' ? 'selected' : '' }}>Business</option>
                    <option value="personal" {{ $course['category'] == 'personal' ? 'selected' : '' }}>Personal Development</option>
                    <option value="other" {{ $course['category'] == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $course['description'] }}</textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₦)</label>
                <input type="number" name="price" step="0.01" min="0" value="{{ $course['price'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Difficulty</label>
                <select name="difficulty" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="beginner" {{ $course['difficulty'] == 'beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="intermediate" {{ $course['difficulty'] == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="advanced" {{ $course['difficulty'] == 'advanced' ? 'selected' : '' }}>Advanced</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Instructor</label>
                <input type="text" name="instructor" value="{{ $course['instructor'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Thumbnail URL</label>
            <input type="url" name="thumbnail" value="{{ $course['thumbnail'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
        </div>
        
        <div class="flex items-center gap-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_free" value="1" {{ $course['is_free'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Free Course</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="is_published" value="1" {{ $course['is_published'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Published</span>
            </label>
        </div>
        
        <div class="flex gap-4 pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                Update Course
            </button>
            <a href="/admin/courses" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<!-- Video Lessons Section -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Video Lessons</h2>
    
    <!-- Add New Lesson Form -->
    <form method="POST" action="/admin/courses/lesson/add" class="mb-6 p-4 bg-gray-50 rounded-xl">
        @csrf
        <input type="hidden" name="course_id" value="{{ $course['id'] }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Title *</label>
                <input type="text" name="title" required placeholder="Lesson title" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                <input type="url" name="video_url" placeholder="YouTube/Vimeo URL" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
                <input type="number" name="lesson_order" value="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                    <i class="fas fa-plus mr-1"></i> Add Lesson
                </button>
            </div>
        </div>
        <div class="mt-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="2" placeholder="Lesson description (optional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
        </div>
    </form>
    
    <!-- Existing Lessons -->
    @if(isset($lessons) && count($lessons) > 0)
    <div class="space-y-3">
        @foreach($lessons as $lesson)
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-500">{{ $lesson->lesson_order }}</span>
                <div>
                    <p class="font-medium text-gray-900">{{ $lesson->title }}</p>
                    @if($lesson->video_url)
                    <p class="text-sm text-blue-600">{{ $lesson->video_url }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-1 rounded {{ $lesson->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $lesson->is_published ? 'Published' : 'Draft' }}
                </span>
                <form method="POST" action="/admin/courses/lesson/{{ $lesson->id }}/delete" onsubmit="return confirm('Delete this lesson?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-gray-500 text-center py-4">No lessons yet. Add your first video lesson above.</p>
    @endif
</div>
@endsection