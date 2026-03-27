@extends('layouts.admin')

@section('title', 'Add Banner')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Add New Banner</h1>
    <p class="text-gray-600 mt-2">Create a promotional banner</p>
</div>

<form method="POST" action="/admin/banners" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Banner Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="e.g., Summer Sale!">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea name="message" rows="2" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Additional text"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Button Text</label>
                <input type="text" name="button_text" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="e.g., Shop Now">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Link</label>
                <input type="url" name="link" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="https://...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
                <input type="color" name="background_color" value="#3b82f6" 
                    class="w-full h-12 rounded-xl border border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Text Color</label>
                <input type="color" name="text_color" value="#ffffff" 
                    class="w-full h-12 rounded-xl border border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Show From</label>
                <input type="datetime-local" name="show_from" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Show Until</label>
                <input type="datetime-local" name="show_until" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                <input type="number" name="order" value="0" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Background Image</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
        </div>

        <div class="mt-6">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" checked
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Create Banner
        </button>
        <a href="/admin/banners" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
