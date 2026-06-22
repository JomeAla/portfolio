@extends('layouts.admin')

@section('title', $broadcast->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp" class="text-green-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Broadcasts
    </a>
</div>

@if(!$apiConfigured)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
    <div class="flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-amber-600 mt-1"></i>
        <div>
            <p class="font-semibold text-amber-800">WhatsApp API Not Configured</p>
            <p class="text-sm text-amber-700 mt-1">
                The WhatsApp API endpoint is not set. Messages will be simulated (logged only) and not actually delivered.
                <a href="/admin/settings" class="underline font-medium hover:text-amber-900">Configure API settings</a>
            </p>
        </div>
    </div>
</div>
@else
<div class="mb-6">
    <button id="test-api-btn" class="text-sm px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
        <i class="fas fa-plug mr-1"></i> Test API Connection
    </button>
    <span id="test-api-result" class="ml-3 text-sm hidden"></span>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $broadcast->name }}</h1>
                    <p class="text-gray-500 mt-1">
                        Created {{ $broadcast->created_at->format('M j, Y g:i A') }}
                    </p>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    {{ $broadcast->status == 'sent' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $broadcast->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                    {{ $broadcast->status == 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $broadcast->status == 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ ucfirst($broadcast->status) }}
                </span>
            </div>

            @if($broadcast->scheduled_at)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-clock mr-2"></i>
                    Scheduled for {{ $broadcast->scheduled_at->format('M j, Y g:i A') }}
                </p>
            </div>
            @endif

            @if($broadcast->group_jid)
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-purple-700">
                    <i class="fas fa-users mr-2"></i>
                    Sent to Group: <span class="font-mono">{{ $broadcast->group_jid }}</span>
                </p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-3">Message Preview</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 whitespace-pre-wrap text-gray-800 leading-relaxed">
                    {{ $broadcast->message }}
                </div>
            </div>

            @if($broadcast->log && count($broadcast->log) > 0)
            <div class="mt-8">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Delivery Log</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 max-h-64 overflow-y-auto">
                    @foreach($broadcast->log as $entry)
                    <div class="text-xs font-mono {{ isset($entry['error']) ? 'text-red-600' : 'text-green-600' }} mb-1">
                        {{ $entry['phone'] ?? 'N/A' }}: {{ $entry['error'] ?? 'Delivered' }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Delivery Stats</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Total Recipients</p>
                    <p class="text-xl font-bold text-slate-800">{{ $broadcast->total_recipients ?: '-' }}</p>
                </div>
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500">Delivered</p>
                    <p class="text-xl font-bold text-green-600">{{ $broadcast->sent_count ?: '-' }}</p>
                </div>
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500">Failed</p>
                    <p class="text-xl font-bold text-red-600">{{ $broadcast->failed_count ?: '-' }}</p>
                </div>
            </div>
        </div>

        @if($broadcast->status == 'draft')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6 mt-6">
            <h3 class="font-semibold text-gray-900 mb-4">Send Broadcast</h3>
            <form method="POST" action="/admin/whatsapp/{{ $broadcast->id }}/send" id="send-form">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Send To</label>
                    <select name="scope" id="scope-select" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                        <option value="all">All Opted-In Contacts</option>
                        <option value="segment">Specific Segment</option>
                        <option value="group" {{ $broadcast->group_jid ? 'selected' : '' }}>WhatsApp Group</option>
                    </select>
                </div>

                <div id="segment-selector" class="mb-4" style="display:none">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose Segment</label>
                    <select name="segment_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                        <option value="">Select a segment...</option>
                        @foreach($segments as $segment)
                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="group-selector" class="mb-4" style="display:none">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose Group</label>
                    <select name="group_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none">
                        <option value="">Select a group...</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ $broadcast->group_jid == $group->group_jid ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" id="send-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Send Now
                </button>

                <div id="send-loading" class="hidden mt-3 text-center text-sm text-gray-500">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Sending broadcast...
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var scopeSelect = document.getElementById('scope-select');
    var segmentSelector = document.getElementById('segment-selector');
    var groupSelector = document.getElementById('group-selector');
    var sendForm = document.getElementById('send-form');
    var sendBtn = document.getElementById('send-btn');
    var sendLoading = document.getElementById('send-loading');
    var testBtn = document.getElementById('test-api-btn');
    var testResult = document.getElementById('test-api-result');

    function toggleScope() {
        var val = scopeSelect.value;
        if (segmentSelector) segmentSelector.style.display = val === 'segment' ? 'block' : 'none';
        if (groupSelector) groupSelector.style.display = val === 'group' ? 'block' : 'none';
    }

    if (scopeSelect) {
        scopeSelect.addEventListener('change', toggleScope);
        toggleScope();
    }

    if (sendForm) {
        sendForm.addEventListener('submit', function(e) {
            if (sendBtn) sendBtn.disabled = true;
            if (sendLoading) sendLoading.classList.remove('hidden');
        });
    }

    if (testBtn) {
        testBtn.addEventListener('click', function() {
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Testing...';
            testResult.className = 'ml-3 text-sm hidden';

            fetch('/admin/whatsapp/test-api')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    testBtn.disabled = false;
                    testBtn.innerHTML = '<i class="fas fa-plug mr-1"></i> Test API Connection';
                    testResult.classList.remove('hidden');
                    if (data.success) {
                        testResult.className = 'ml-3 text-sm text-green-600';
                        testResult.textContent = 'Connected successfully!';
                    } else {
                        testResult.className = 'ml-3 text-sm text-red-600';
                        testResult.textContent = data.error || 'Connection failed';
                    }
                })
                .catch(function(err) {
                    testBtn.disabled = false;
                    testBtn.innerHTML = '<i class="fas fa-plug mr-1"></i> Test API Connection';
                    testResult.classList.remove('hidden');
                    testResult.className = 'ml-3 text-sm text-red-600';
                    testResult.textContent = 'Request failed: ' + err.message;
                });
        });
    }
});
</script>
@endsection
