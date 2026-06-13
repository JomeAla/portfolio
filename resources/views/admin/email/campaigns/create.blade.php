@extends('layouts.admin')

@section('title', 'Create Campaign')

@section('content')
<div class="mb-6">
    <a href="/admin/email/campaigns" class="text-blue-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Campaigns
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-3xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Email Campaign</h1>
    
    <form method="POST" action="/admin/email/campaigns" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Name *</label>
            <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" placeholder="e.g., Monthly Newsletter">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Subject *</label>
            <input type="text" name="subject" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" placeholder="e.g., Your Monthly Updates Are Here!">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Body</label>
            <textarea name="body" rows="10" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" placeholder="Write your email content here..."></textarea>
            <p class="text-xs text-gray-500 mt-1">HTML is supported</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
                <select name="recipient_type" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="all_leads">All Leads ({{ $leadCount }})</option>
                    <option value="newsletter">Newsletter Subscribers</option>
                    <option value="customers">Customers</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                <select name="send_type" id="sendType" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" onchange="toggleSchedule()">
                    <option value="now">Send Now</option>
                    <option value="schedule">Schedule for Later</option>
                </select>
            </div>
        </div>
        
        <div id="scheduleFields" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                    <input type="date" name="schedule_date" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                    <input type="time" name="schedule_time" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                </div>
            </div>
        </div>
        
        <div class="flex gap-4 pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                Create Campaign
            </button>
            <button type="submit" name="save_draft" value="1" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
                Save as Draft
            </button>
        </div>
    </form>
</div>

<script>
function toggleSchedule() {
    const sendType = document.getElementById('sendType').value;
    const scheduleFields = document.getElementById('scheduleFields');
    if (sendType === 'schedule') {
        scheduleFields.classList.remove('hidden');
    } else {
        scheduleFields.classList.add('hidden');
    }
}
</script>
@endsection