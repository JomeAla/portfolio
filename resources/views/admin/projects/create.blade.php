@extends('layouts.admin')

@section('title', 'Add Project')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Add New Project</h1>
    <p class="text-gray-600 mt-2">Add a new project to your portfolio</p>
</div>

<form method="POST" action="/admin/projects" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Basic Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Project Title *</label>
                <input type="text" name="title" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="E-commerce Platform">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea name="description" rows="4" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Brief description of the project..."></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Problem Solved</label>
                <textarea name="problem_solved" rows="3" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="What problem does this project solve?"></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Solution</label>
                <textarea name="solution" rows="3" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="How was the problem solved?"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                <select name="category" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="">Select Category</option>
                    <option value="web">Web Application</option>
                    <option value="mobile">Mobile App</option>
                    <option value="api">API Integration</option>
                    <option value="automation">Automation</option>
                    <option value="design">UI/UX Design</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                <input type="text" name="industry" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="E.g., E-commerce, Healthcare, Finance">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Technologies</label>
                <input type="text" name="technologies" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Laravel, React, MySQL (comma separated)">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Client Name</label>
                <input type="text" name="client_name" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Client name (optional)">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                <input type="text" name="duration" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="E.g., 3 months">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Media & Images</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Thumbnail Image</label>
                <input type="file" name="thumbnail" accept="image/*"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                <p class="text-xs text-gray-500 mt-1">Main project image (recommended: 800x600)</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gallery Images</label>
                <input type="file" name="images[]" accept="image/*" multiple
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                <p class="text-xs text-gray-500 mt-1">Select multiple screenshots</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Links</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">GitHub Repository</label>
                <input type="url" name="github_url" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="https://github.com/username/project">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Live Project URL</label>
                <input type="url" name="live_url" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="https://project-demo.com">
            </div>
        </div>
        
        <div class="mt-6">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_featured" value="1" 
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Feature this project on homepage</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Create Project
        </button>
        <a href="/admin/projects" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
