@extends('layouts.admin')

@section('title', 'Create Template')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/templates" class="text-green-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Templates</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Create Message Template</h1>
    <p class="text-gray-500 mb-6">Build text, interactive, media, or flow templates for WhatsApp.</p>

    <form method="POST" action="/admin/whatsapp/templates" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="e.g., Welcome Offer">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    <option value="marketing">Marketing</option>
                    <option value="utility">Utility</option>
                    <option value="authentication">Authentication</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message Type</label>
                <select name="message_type" id="msgType" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" onchange="toggleFields()">
                    <option value="text">Text</option>
                    <option value="interactive">Interactive (Buttons / List)</option>
                    <option value="media">Media (Image / Document / Video)</option>
                    <option value="flow">Flow (Interactive Form)</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Body Text *</label>
            <textarea name="body" required rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Message body with @{{name}}, @{{first_name}}, @{{site_name}} placeholders"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Footer (optional, max 60 chars)</label>
                <input type="text" name="footer" maxlength="60" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Reply STOP to opt out">
            </div>
            <div id="headerField">
                <label class="block text-sm font-medium text-gray-700 mb-2">Header Text (optional, max 60 chars)</label>
                <input type="text" name="header_value" maxlength="60" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Special Offer 🔥">
            </div>
        </div>

        <div id="mediaField" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Media Type</label>
                <select name="header_type" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    <option value="image">Image</option>
                    <option value="document">Document</option>
                    <option value="video">Video</option>
                    <option value="audio">Audio</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Media URL</label>
                <input type="url" name="media_url" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="https://example.com/image.jpg">
            </div>
        </div>

        <div id="buttonsField" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Buttons (JSON)</label>
            <textarea name="buttons" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none font-mono text-sm" placeholder='[{"type":"quick_reply","title":"Yes","id":"yes"},{"type":"quick_reply","title":"No","id":"no"}]'></textarea>
            <p class="text-xs text-gray-500 mt-1">Types: quick_reply, cta_url (add "url"), cta_phone (add "phone")</p>
        </div>

        <div id="sectionsField" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">List Sections (JSON for list messages)</label>
            <textarea name="sections" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none font-mono text-sm" placeholder='[{"title":"Options","rows":[{"id":"opt1","title":"Option 1","description":"Desc"}]}]'></textarea>
        </div>

        <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
            <p class="font-medium mb-1">Available Placeholders:</p>
            <code class="text-xs">@{{name}}, @{{first_name}}, @{{phone}}, @{{email}}, @{{site_name}}, @{{site_url}}, @{{year}}</code>
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-save mr-2"></i>Create Template
        </button>
    </form>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('msgType').value;
    document.getElementById('mediaField').classList.toggle('hidden', type !== 'media');
    document.getElementById('buttonsField').classList.toggle('hidden', type !== 'interactive');
    document.getElementById('sectionsField').classList.toggle('hidden', type !== 'interactive');
    document.getElementById('headerField').classList.toggle('hidden', type === 'flow');
}
</script>
@endsection
