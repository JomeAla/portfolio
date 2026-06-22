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
                    <option value="template">Template Based</option>
                </select>
            </div>
        </div>

        <div id="textFields">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                <textarea name="message" rows="6" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="Type your message...">{{ old('message') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Placeholders: @{{name}}, @{{first_name}}, @{{site_name}}</p>
            </div>
        </div>

        <div id="templateField" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Choose Template *</label>
            <select name="template_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                <option value="">Select a template...</option>
                @foreach($templates as $t)
                <option value="{{ $t->id }}">{{ $t->name }} - {{ $t->message_type }}</option>
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
                    <option value="group">WhatsApp Group</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                <select name="send_type" id="sendType" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" onchange="toggleSchedule()">
                    <option value="draft">Save as Draft</option>
                    <option value="now">Create &amp; Send Now</option>
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

        <div id="groupField" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Target Group</label>
            <select name="group_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                <option value="">Select Group</option>
                @foreach($groups as $g)
                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->group_jid }})</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Message will be sent to the entire WhatsApp group. <a href="/admin/whatsapp/groups" class="text-blue-600">Manage Groups</a></p>
        </div>

        <div id="scheduleFields" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date and Time</label>
            <input type="datetime-local" name="schedule" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
        </div>

                <button type="submit" id="createBtn" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i><span id="createBtnText">Save as Draft</span>
                </button>
    </form>
</div>

<script>
function toggleMessageType() {
    var val = document.getElementById('msgType').value;
    document.getElementById('textFields').style.display = val === 'text' ? 'block' : 'none';
    document.getElementById('templateField').style.display = val === 'template' ? 'block' : 'none';
}
function toggleSchedule() {
    var val = document.getElementById('sendType').value;
    var show = val === 'schedule';
    document.getElementById('scheduleFields').style.display = show ? 'block' : 'none';
    var input = document.querySelector('[name="schedule"]');
    if (input) input.disabled = !show;
    var btnText = document.getElementById('createBtnText');
    if (btnText) {
        if (val === 'now') btnText.textContent = 'Create & Send Now';
        else if (val === 'draft') btnText.textContent = 'Save as Draft';
        else btnText.textContent = 'Schedule';
    }
}
document.getElementById('scopeSelect').addEventListener('change', function() {
    document.getElementById('segmentField').style.display = this.value === 'segment' ? 'block' : 'none';
    document.getElementById('groupField').style.display = this.value === 'group' ? 'block' : 'none';
});
</script>
@endsection