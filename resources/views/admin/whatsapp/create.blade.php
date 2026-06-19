@extends('layouts.admin')

@section('title', 'New WhatsApp Broadcast')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp" class="text-green-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Broadcasts
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">New WhatsApp Broadcast</h1>
    <p class="text-gray-500 mb-6">Create a message to broadcast to your WhatsApp contacts.</p>

    <form method="POST" action="/admin/whatsapp" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Broadcast Name *</label>
            <input type="text" name="name" required
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                placeholder="e.g., Weekend Promo Blast">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
            <textarea name="message" required rows="8"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                placeholder="Type your WhatsApp message here..."></textarea>
            <p class="text-xs text-gray-500 mt-1">
                Available placeholders: <code>{{ '{{name}}' }}</code>, <code>{{ '{{first_name}}' }}</code>, <code>{{ '{{site_name}}' }}</code>
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
                <select name="scope" id="scopeSelect"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                    <option value="all">All Opted-In Contacts ({{ $contactCount }})</option>
                    <option value="segment">Specific Segment</option>
                    <option value="custom">Custom Import</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                <select name="send_type" id="sendType"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none"
                    onchange="toggleSchedule()">
                    <option value="draft">Save as Draft</option>
                    <option value="schedule">Schedule for Later</option>
                </select>
            </div>
        </div>

        <div id="segmentField" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Segment</label>
            <select name="segment_id"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                <option value="">Select Segment</option>
                @foreach($segments as $segment)
                <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="scheduleFields" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date & Time</label>
            <input type="datetime-local" name="schedule"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                <i class="fas fa-paper-plane mr-2"></i>Create Broadcast
            </button>
        </div>
    </form>
</div>

<script>
function toggleSchedule() {
    const val = document.getElementById('sendType').value;
    document.getElementById('scheduleFields').classList.toggle('hidden', val !== 'schedule');
}
document.getElementById('scopeSelect')?.addEventListener('change', function() {
    document.getElementById('segmentField').classList.toggle('hidden', this.value !== 'segment');
});
</script>
@endsection
