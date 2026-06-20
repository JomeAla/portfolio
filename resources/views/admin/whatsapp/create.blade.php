@extends('layouts.admin')

@section('title', 'New WhatsApp Broadcast')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp" class="text-green-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Back to Broadcasts
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">New WhatsApp Broadcast</h1>
    <p class="text-gray-500 mb-6">Send text messages, templates, or interactive experiences.</p>

    <form method="POST" action="/admin/whatsapp" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Broadcast Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="e.g., Weekend Promo">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message Type *</label>
                <select name="message_type" id="msgType" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" onchange="toggleMessageType()">
                    <option value="text">Plain Text</option>
                    <option value="template">Template (Interactive / Media / Flow)</option>
                </select>
            </div>
        </div>

        <div id="textFields">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                <textarea name="message" rows="6" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Type your message...">{{ old('message') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Placeholders: {{ '{{name}}' }}, {{ '{{first_name}}' }}, {{ '{{site_name}}' }}</p>
            </div>
        </div>

        <div id="templateField" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Choose Template *</label>
            <select name="template_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                <option value="">Select a template...</option>
                @foreach($templates as $t)
                <option value="{{ $t->id }}">{{ $t->name }} ({{ ucfirst($t->message_type) }} - {{ $t->button_count }} buttons)</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Templates support: buttons, lists, media, flows. <a href="/admin/whatsapp/templates" class="text-blue-600">Manage Templates</a></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
                <select name="scope" id="scopeSelect" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    <option value="all">All Opted-In Contacts ({{ $contactCount }})</option>
                    <option value="segment">Specific Segment</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                <select name="send_type" id="sendType" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" onchange="toggleSchedule()">
                    <option value="draft">Save as Draft</option>
                    <option value="schedule">Schedule for Later</option>
                </select>
            </div>
        </div>

        <div id="segmentField" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Segment</label>
            <select name="segment_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                <option value="">Select Segment</option>
                @foreach($segments as $segment)
                <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="scheduleFields" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date & Time</label>
            <input type="datetime-local" name="schedule" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-paper-plane mr-2"></i>Create Broadcast
        </button>
    </form>
</div>

<script>
function toggleMessageType() {
    const val = document.getElementById('msgType').value;
    document.getElementById('textFields').classList.toggle('hidden', val !== 'text');
    document.getElementById('templateField').classList.toggle('hidden', val !== 'template');
}
function toggleSchedule() {
    document.getElementById('scheduleFields').classList.toggle('hidden', document.getElementById('sendType').value !== 'schedule');
}
document.getElementById('scopeSelect')?.addEventListener('change', function() {
    document.getElementById('segmentField').classList.toggle('hidden', this.value !== 'segment');
});
</script>
@endsection
