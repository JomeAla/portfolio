@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.email-templates') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Edit Email Template</h1>
    </div>

    <form method="POST" action="{{ route('admin.marketing.email-templates.update', $template) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Template Name</label>
                    <input type="text" name="name" value="{{ $template->name }}" required 
                        class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select name="category" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        @foreach(['welcome' => 'Welcome', 'newsletter' => 'Newsletter', 'promotional' => 'Promotional', 'transactional' => 'Transactional', 'follow_up' => 'Follow Up', 'announcement' => 'Announcement', 'event' => 'Event', 'survey' => 'Survey'] as $value => $label)
                            <option value="{{ $value }}" {{ $template->category === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject Line</label>
                <input type="text" name="subject" value="{{ $template->subject }}" required 
                    class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <input type="text" name="description" value="{{ $template->description }}" 
                    class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-slate-700">Email Body (HTML)</label>
                <a href="{{ route('admin.marketing.email-templates.preview', $template) }}" target="_blank"
                    class="text-sm text-indigo-600 hover:text-indigo-800">
                    <i class="fas fa-eye mr-1"></i>Preview
                </a>
            </div>
            <textarea name="body" rows="20" required 
                class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm">{{ $template->body }}</textarea>
            <p class="text-xs text-slate-500 mt-2">
                Variables: {{ implode(', ', ['first_name', 'last_name', 'email', 'company', 'site_name', 'site_url', 'current_date', 'unsubscribe_url']) }}
            </p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                Update Template
            </button>
            <a href="{{ route('admin.marketing.email-templates') }}" 
                class="px-6 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection