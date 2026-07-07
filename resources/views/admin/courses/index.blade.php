@extends('layouts.admin')

@section('title', 'Courses')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Courses</h1>
        <p class="text-gray-600 mt-2">Manage your online courses and lessons</p>
    </div>
    <a href="/admin/courses/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>Add Course
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($courses as $course)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden hover:shadow-md transition-shadow">
        @if($course['thumbnail'])
        <img src="{{ $course['thumbnail'] }}" alt="{{ $course['title'] }}" class="w-full h-40 object-cover">
        @else
        <div class="w-full h-40 bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
            <i class="fas fa-graduation-cap text-4xl text-white"></i>
        </div>
        @endif
        
        <div class="p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="px-2 py-1 text-xs font-medium rounded-full 
                    {{ $course['is_published'] ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $course['is_published'] ? 'Published' : 'Draft' }}
                </span>
                <span class="text-xs text-gray-500">{{ $course['difficulty'] }}</span>
            </div>
            
            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $course['title'] }}</h3>
            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $course['description'] ?? 'No description' }}</p>
            
            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                <span><i class="fas fa-book mr-1"></i> {{ $course['lessons_count'] }} lessons</span>
                <span><i class="fas fa-users mr-1"></i> {{ $course['students_count'] }} students</span>
            </div>
            @if(!empty($course['required_tier_name']))
            <div class="mb-3">
                <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700">
                    <i class="fas fa-crown mr-1"></i>{{ $course['required_tier_name'] }}+
                </span>
            </div>
            @endif
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="font-bold text-blue-600">
                    @if(($course['is_free'] ?? false) || $course['price'] == 0)
                        Free
                    @else
                        ₦{{ number_format($course['price']) }}
                    @endif
                </span>
                <div class="flex gap-2">
                    <a href="/admin/courses/{{ $course['id'] }}/edit" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="/admin/courses/{{ $course['id'] }}" class="text-gray-400 hover:text-green-600">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-12">
        <div class="text-gray-400 text-5xl mb-4"><i class="fas fa-graduation-cap"></i></div>
        <p class="text-gray-500 text-lg">No courses yet</p>
        <a href="/admin/courses/create" class="text-blue-600 hover:underline mt-2 inline-block">Create your first course</a>
    </div>
    @endforelse
</div>
@endsection