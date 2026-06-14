@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Automation Rules</h1>
            <p class="text-slate-600 mt-1">Create rules to automate actions based on lead behavior</p>
        </div>
        <a href="/admin/marketing/automation/builder" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center gap-2">
            <i class="fas fa-robot"></i>
            Open Automation Builder
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create Rule Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Create New Rule</h2>
                
                <form method="POST" action="{{ route('admin.marketing.automation.store') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Rule Name</label>
                        <input type="text" name="name" required placeholder="e.g., Follow up after welcome" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <span class="text-blue-600">IF</span> (Trigger)
                        </label>
                        
                        <select name="trigger_type" id="triggerType" onchange="updateTriggerFields()"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm mb-2">
                            @foreach(['email_opened' => 'Email Opened', 'email_clicked' => 'Email Clicked', 'link_clicked' => 'Specific Link Clicked', 'score_reached' => 'Lead Score Reached', 'tag_added' => 'Tag Added', 'page_visited' => 'Page Visited', 'form_submitted' => 'Form Submitted', 'campaign_enrolled' => 'Campaign Enrolled', 'lead_created' => 'New Lead Created', 'cart_abandoned' => 'Cart Abandoned'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        
                        <div id="triggerValueField" class="hidden">
                            <input type="text" name="trigger_value" id="triggerValue" placeholder="Value (e.g., tag name, URL)" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            <span class="text-green-600">THEN</span> (Action)
                        </label>
                        
                        <select name="action_type" id="actionType" onchange="updateActionFields()"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm mb-3">
                            @foreach(['enroll_sequence' => 'Enroll in Sequence', 'add_tag' => 'Add Tag', 'remove_tag' => 'Remove Tag', 'send_email' => 'Send Immediate Email', 'update_score' => 'Update Lead Score', 'notify_admin' => 'Notify Admin', 'webhook' => 'Trigger Webhook'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <!-- Action Fields -->
                        <div id="sequenceField" class="hidden mb-2">
                            <select name="action_sequence_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                                <option value="">-- Select Sequence --</option>
                                @foreach($sequences as $seq)
                                    <option value="{{ $seq->id }}">{{ $seq->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="tagField" class="hidden mb-2">
                            <input type="text" name="tag_name" placeholder="Tag name" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                        </div>

                        <div id="scoreField" class="hidden space-y-2">
                            <select name="score_operation" class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                                <option value="add">Add</option>
                                <option value="set">Set to</option>
                                <option value="subtract">Subtract</option>
                            </select>
                            <input type="number" name="score_change" placeholder="Score change" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                        </div>

                        <div id="emailField" class="hidden space-y-2">
                            <input type="text" name="email_subject" placeholder="Email subject" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                            <textarea name="email_body" rows="3" placeholder="Email body" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm"></textarea>
                        </div>

                        <div id="webhookField" class="hidden mb-2">
                            <input type="url" name="webhook_url" placeholder="Webhook URL" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" checked
                            class="rounded border-slate-300 text-indigo-600">
                        <label for="is_active" class="ml-2 text-sm text-slate-700">Active</label>
                    </div>

                    <button type="submit" 
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Create Rule
                    </button>
                </form>
            </div>
        </div>

        <!-- Rules List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Active Rules ({{ $rules->total() }})</h3>
                </div>

                @if($rules->isEmpty())
                <div class="p-8 text-center">
                    <div class="text-slate-400 mb-2">
                        <i class="fas fa-robot text-4xl"></i>
                    </div>
                    <p class="text-slate-600">No automation rules yet</p>
                    <p class="text-sm text-slate-500">Create your first rule to get started</p>
                </div>
                @else
                <div class="divide-y divide-slate-200">
                    @foreach($rules as $rule)
                    <div class="p-4 hover:bg-slate-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-medium text-slate-800">{{ $rule->name }}</h4>
                                    <span class="px-2 py-0.5 text-xs rounded {{ $rule->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-blue-600 font-medium">IF</span>
                                    <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs">
                                        {{ $rule->trigger_type }}
                                    </span>
                                    @if($rule->trigger_value)
                                        <span class="text-slate-500">{{ $rule->trigger_value }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-sm mt-1">
                                    <span class="text-green-600 font-medium">THEN</span>
                                    <span class="bg-green-50 text-green-700 px-2 py-0.5 rounded text-xs">
                                        {{ $rule->action_type }}
                                    </span>
                                    @if($rule->action_sequence_id)
                                        <span class="text-slate-600">{{ $rule->sequence->name ?? 'Sequence #' . $rule->action_sequence_id }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400 mt-2">
                                    Triggered {{ $rule->times_triggered ?? 0 }} times
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.marketing.automation.toggle', $rule) }}">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" 
                                        class="px-3 py-1 text-xs rounded {{ $rule->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        {{ $rule->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.marketing.automation.destroy', $rule) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="px-3 py-1 text-xs text-red-600 hover:text-red-800"
                                        onclick="return confirm('Delete this rule?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="mt-4">
                {{ $rules->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function updateTriggerFields() {
    const trigger = document.getElementById('triggerType').value;
    const valueField = document.getElementById('triggerValueField');
    const valueInput = document.getElementById('triggerValue');
    
    const showValue = ['email_opened', 'email_clicked', 'link_clicked', 'score_reached', 'tag_added', 'page_visited'].includes(trigger);
    valueField.classList.toggle('hidden', !showValue);
    valueInput.required = showValue;
}

function updateActionFields() {
    const action = document.getElementById('actionType').value;
    
    document.getElementById('sequenceField').classList.toggle('hidden', action !== 'enroll_sequence');
    document.getElementById('tagField').classList.toggle('hidden', !['add_tag', 'remove_tag'].includes(action));
    document.getElementById('scoreField').classList.toggle('hidden', action !== 'update_score');
    document.getElementById('emailField').classList.toggle('hidden', action !== 'send_email');
    document.getElementById('webhookField').classList.toggle('hidden', action !== 'webhook');
}

document.addEventListener('DOMContentLoaded', function() {
    updateTriggerFields();
    updateActionFields();
});
</script>
@endsection