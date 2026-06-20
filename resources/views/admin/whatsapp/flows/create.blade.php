@extends('layouts.admin')

@section('title', 'Create Flow')

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/flows" class="text-pink-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Flows</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Create WhatsApp Flow</h1>
    <p class="text-gray-500 mb-6">Design interactive form experiences that open inside WhatsApp.</p>

    <form method="POST" action="/admin/whatsapp/flows" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Flow Name *</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none" placeholder="e.g., Booking Form">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Flow ID (optional)</label>
                <input type="text" name="flow_id" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none" placeholder="flow_id from Meta">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <input type="text" name="description" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none" placeholder="What this flow does">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Flow JSON (Form Definition) *</label>
            <textarea name="flow_json" required rows="12" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none font-mono text-sm" placeholder='{
  "type": "form",
  "screens": [
    {
      "id": "FORM",
      "title": "Book Appointment",
      "layout": [
        { "type": "text", "label": "Your Name", "name": "name", "required": true },
        { "type": "phone", "label": "Phone", "name": "phone", "required": true },
        { "type": "date", "label": "Preferred Date", "name": "date" }
      ]
    }
  ]
}'></textarea>
            <p class="text-xs text-gray-500 mt-1">Uses WhatsApp Flows JSON Schema format. See <a href="https://developers.facebook.com/docs/whatsapp/flows" target="_blank" class="text-blue-600">Meta docs</a>.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pre-filled Data JSON (optional)</label>
            <textarea name="flow_data" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none font-mono text-sm" placeholder='{"name": "@{{name}}", "phone": "@{{phone}}"}'></textarea>
        </div>

        <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-save mr-2"></i>Create Flow
        </button>
    </form>
</div>
@endsection
