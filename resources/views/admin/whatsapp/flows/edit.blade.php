@extends('layouts.admin')

@section('title', 'Edit ' . $flow->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/flows" class="text-pink-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Flows</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Edit Flow</h1>
    <p class="text-gray-500 mb-6">{{ $flow->name }}</p>

    <form method="POST" action="/admin/whatsapp/flows/{{ $flow->id }}" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" value="{{ $flow->name }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Flow ID</label>
                <input type="text" name="flow_id" value="{{ $flow->flow_id }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <input type="text" name="description" value="{{ $flow->description }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Flow JSON *</label>
            <textarea name="flow_json" required rows="12" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none font-mono text-sm">{{ json_encode($flow->flow_json, JSON_PRETTY_PRINT) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pre-filled Data JSON</label>
            <textarea name="flow_data" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none font-mono text-sm">{{ $flow->flow_data ? json_encode($flow->flow_data, JSON_PRETTY_PRINT) : '' }}</textarea>
        </div>
        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-save mr-2"></i>Update Flow
        </button>
    </form>
</div>
@endsection
