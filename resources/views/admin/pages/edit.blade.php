@extends('layouts.admin')

@section('title', 'Edit Page - ' . $page->title)

@section('content')
<div class="mb-8">
    <a href="/admin/pages" class="text-blue-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Pages
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Page: {{ $page->title }}</h1>
    
    <form method="POST" action="/admin/pages/{{ $page->id }}">
        @csrf
        @method('PUT')
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
            <input type="text" name="title" value="{{ $page->title }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
        </div>

        @if($page->slug === 'home')
        <!-- Homepage Hero Settings -->
        <div class="mb-6 p-6 bg-blue-50 rounded-xl border border-blue-200">
            <h2 class="text-lg font-semibold text-blue-900 mb-4 flex items-center gap-2">
                <i class="fas fa-home"></i>
                Hero Section Content
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Badge Text</label>
                    <input type="text" name="hero_badge" value="{{ $page->content['hero']['badge'] ?? 'Available for projects' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hero Title</label>
                    <input type="text" name="hero_title" value="{{ $page->content['hero']['title'] ?? '' }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hero Subtitle</label>
                    <textarea name="hero_subtitle" rows="3" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $page->content['hero']['subtitle'] ?? '' }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CTA Button Text</label>
                        <input type="text" name="cta_text" value="{{ $page->content['cta']['text'] ?? '' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CTA Button Link</label>
                        <input type="text" name="cta_link" value="{{ $page->content['cta']['link'] ?? '' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                </div>

                <h3 class="text-md font-semibold text-gray-800 pt-4 border-t border-blue-200">Stats Section</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stat 1 - Value</label>
                        <input type="text" name="stat_projects" value="{{ $page->content['stats']['projects'] ?? '50+' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stat 1 - Label</label>
                        <input type="text" name="stat_projects_label" value="{{ $page->content['stats']['projects_label'] ?? 'Projects Completed' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                    <div></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stat 2 - Value</label>
                        <input type="text" name="stat_experience" value="{{ $page->content['stats']['experience'] ?? '5+' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stat 2 - Label</label>
                        <input type="text" name="stat_experience_label" value="{{ $page->content['stats']['experience_label'] ?? 'Years Experience' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                    <div></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stat 3 - Value</label>
                        <input type="text" name="stat_satisfaction" value="{{ $page->content['stats']['satisfaction'] ?? '100%' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stat 3 - Label</label>
                        <input type="text" name="stat_satisfaction_label" value="{{ $page->content['stats']['satisfaction_label'] ?? 'Client Satisfaction' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
            <textarea name="content" rows="12" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ is_array($page->content) ? json_encode($page->content, JSON_PRETTY_PRINT) : $page->content }}</textarea>
            <p class="text-sm text-gray-500 mt-1">Enter page content (JSON or plain text)</p>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                <input type="text" name="meta_title" value="{{ $page->meta_title }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="SEO Title">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                <input type="text" name="meta_description" value="{{ $page->meta_description }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="SEO Description">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
