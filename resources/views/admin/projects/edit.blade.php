@extends('layouts.admin')

@section('title', 'Edit Project')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Edit Project</h1>
    <p class="text-gray-600 mt-2">Update your project details</p>
</div>

<form method="POST" action="/admin/projects/{{ $project->id }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Basic Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Project Title *</label>
                <input type="text" name="title" value="{{ $project->title }}" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea name="description" rows="4" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $project->description }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Problem Solved</label>
                <textarea name="problem_solved" rows="3" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $project->problem_solved }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Solution</label>
                <textarea name="solution" rows="3" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $project->solution }}</textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="web" {{ $project->category == 'web' ? 'selected' : '' }}>Web Application</option>
                    <option value="mobile" {{ $project->category == 'mobile' ? 'selected' : '' }}>Mobile App</option>
                    <option value="api" {{ $project->category == 'api' ? 'selected' : '' }}>API Integration</option>
                    <option value="automation" {{ $project->category == 'automation' ? 'selected' : '' }}>Automation</option>
                    <option value="design" {{ $project->category == 'design' ? 'selected' : '' }}>UI/UX Design</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                <input type="text" name="industry" value="{{ $project->industry }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Technologies</label>
                <input type="text" name="technologies" value="{{ is_array($project->technologies) ? implode(', ', $project->technologies) : '' }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Laravel, React, MySQL">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                <input type="text" name="client_name" value="{{ $project->client_name }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                <input type="text" name="duration" value="{{ $project->duration }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Media & Images</h2>
        
        @if($project->thumbnail)
        <div class="mb-4">
            <img src="{{ asset('storage/' . $project->thumbnail) }}" alt="Thumbnail" class="w-48 h-32 object-cover rounded-lg">
            <p class="text-sm text-gray-500 mt-1">Current thumbnail</p>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Change Thumbnail</label>
                <input type="file" name="thumbnail" accept="image/*"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Add Gallery Images</label>
                <input type="file" name="images[]" accept="image/*" multiple
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
        </div>

        @if($project->images)
        <div class="mt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Current Gallery</p>
            <div class="flex flex-wrap gap-2">
                @foreach(json_decode($project->images) as $image)
                <img src="{{ asset('storage/' . $image) }}" alt="Gallery" class="w-24 h-24 object-cover rounded-lg">
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Links</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">GitHub Repository</label>
                <input type="url" name="github_url" value="{{ $project->github_url }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="https://github.com/username/project">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Live Project URL</label>
                <input type="url" name="live_url" value="{{ $project->live_url }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="https://project-demo.com">
            </div>
        </div>
        
        <div class="mt-6">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_featured" value="1" {{ $project->is_featured ? 'checked' : '' }}
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Feature this project on homepage</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Update Project
        </button>
        <a href="/admin/projects" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
