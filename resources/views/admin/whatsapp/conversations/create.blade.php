@extends('layouts.admin')

@section('title', 'Create Conversation')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/conversations" class="text-indigo-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Create Conversation Flow</h1>
    <p class="text-gray-500 mb-6">Build multi-step automated conversations with branching logic.</p>

    <form method="POST" action="/admin/whatsapp/conversations" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none" placeholder="e.g., Post-Purchase Followup">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Event</label>
                <select name="trigger_event" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                    <option value="manual">Manual</option>
                    <option value="lead_created">Lead Created</option>
                    <option value="purchase_made">Purchase Made</option>
                    <option value="broadcast_reply">Broadcast Reply</option>
                    <option value="schedule">Schedule</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <input type="text" name="description" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Steps (JSON) *</label>
            <textarea name="steps" required rows="14" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none font-mono text-sm" placeholder='[
  {
    "step_order": 1,
    "message": "Hi {{name}}, thanks for your purchase!",
    "delay_minutes": 0
  },
  {
    "step_order": 2,
    "delay_minutes": 1440,
    "template_id": 1,
    "conditions": [
      { "field": "message", "operator": "contains", "value": "yes", "next_step": 3 },
      { "field": "message", "operator": "contains", "value": "no", "next_step": 4 }
    ]
  }
]'></textarea>
            <p class="text-xs text-gray-500 mt-1">
                Each step: step_order, message/template_id, delay_minutes, optional conditions
                (field, operator: equals/contains/starts_with, value, next_step)
            </p>
        </div>

        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Available Templates:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($templates as $t)
                <span class="px-2 py-1 bg-white border rounded text-xs text-gray-600">#{{ $t->id }}: {{ $t->name }}</span>
                @endforeach
            </div>
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-save mr-2"></i>Create Conversation
        </button>
    </form>
</div>
@endsection
