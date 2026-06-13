@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.email-templates') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Create Email Template</h1>
    </div>

    <form method="POST" action="{{ route('admin.marketing.email-templates.store') }}" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Template Name</label>
                    <input type="text" name="name" required 
                        class="w-full border border-slate-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
                    <select name="category" class="w-full border border-slate-300 rounded-lg px-4 py-2">
                        <option value="welcome">Welcome</option>
                        <option value="follow_up">Follow Up</option>
                        <option value="newsletter">Newsletter</option>
                        <option value="promotional">Promotional</option>
                        <option value="transactional">Transactional</option>
                        <option value="notification">Notification</option>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject Line</label>
                <input type="text" name="subject" required 
                    placeholder="Use @{{variable}} for personalization"
                    class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <input type="text" name="description" 
                    class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>

            <div class="flex items-center mt-4">
                <input type="checkbox" name="is_active" id="is_active" checked
                    class="rounded border-slate-300 text-indigo-600">
                <label for="is_active" class="ml-2 text-sm text-slate-700">Active</label>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <label class="block text-sm font-medium text-slate-700">Email Body (HTML)</label>
                <div class="text-xs text-slate-500">
                    Available: &#123;&#123;name&#125;&#125;, &#123;&#123;email&#125;&#125;, &#123;&#123;site_url&#125;&#125;, &#123;&#123;year&#125;&#125;, &#123;&#123;date&#125;&#125;, &#123;&#123;unsubscribe_url&#125;&#125;, &#123;&#123;subject&#125;&#125;, &#123;&#123;title&#125;&#125;, &#123;&#123;content&#125;&#125;, &#123;&#123;cta_url&#125;&#125;, &#123;&#123;cta_text&#125;&#125;, &#123;&#123;offer&#125;&#125;, &#123;&#123;expiry_date&#125;&#125;
                </div>
            </div>
            <textarea name="body" rows="20" required 
                class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm"
                placeholder="<html><body>...</body></html>"></textarea>
            <p class="text-xs text-slate-500 mt-2">
                Use variables like &#123;&#123;name&#125;&#125;, &#123;&#123;email&#125;&#125;, &#123;&#123;unsubscribe_url&#125;&#125;
            </p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                Create Template
            </button>
            <a href="{{ route('admin.marketing.email-templates') }}" 
                class="px-6 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection