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

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
            <textarea name="content" rows="12" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm">{{ is_array($page->content) ? json_encode($page->content, JSON_PRETTY_PRINT) : $page->content }}</textarea>
            <p class="text-sm text-gray-500 mt-1">Enter page content (JSON or plain text)</p>
        </div>

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
