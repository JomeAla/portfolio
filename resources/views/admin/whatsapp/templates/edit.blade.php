@extends('layouts.admin')

@section('title', 'Edit ' . $template->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/templates" class="text-green-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Templates</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Edit Template</h1>
    <p class="text-gray-500 mb-6">{{ $template->name }}</p>

    <form method="POST" action="/admin/whatsapp/templates/{{ $template->id }}" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                <input type="text" name="name" value="{{ $template->name }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    @foreach(['marketing','utility','authentication'] as $c)
                    <option value="{{ $c }}" {{ $template->category == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message Type</label>
                <select name="message_type" id="msgType" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" onchange="toggleFields()">
                    @foreach(['text','interactive','media','flow'] as $t)
                    <option value="{{ $t }}" {{ $template->message_type == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <textarea name="body" required rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">{{ $template->body }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <input type="text" name="footer" maxlength="60" value="{{ $template->footer ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Footer">
            </div>
            <div>
                <input type="text" name="header_value" maxlength="60" value="{{ $template->header_value ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Header">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Media URL</label>
                <input type="url" name="media_url" value="{{ $template->media_url ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Header Type (for media)</label>
                <select name="header_type" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    @foreach(['text','image','document','video'] as $h)
                    <option value="{{ $h }}" {{ $template->header_type == $h ? 'selected' : '' }}>{{ ucfirst($h) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buttons JSON</label>
            <textarea name="buttons" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none font-mono text-sm">{{ json_encode($template->buttons, JSON_PRETTY_PRINT) ?: '' }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sections JSON</label>
            <textarea name="sections" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none font-mono text-sm">{{ json_encode($template->sections, JSON_PRETTY_PRINT) ?: '' }}</textarea>
        </div>

        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-save mr-2"></i>Update Template
        </button>
    </form>
</div>

<script>
function toggleFields() {}
</script>
@endsection
