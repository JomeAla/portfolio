@extends('layouts.admin')

@section('title', 'Edit ' . $conv->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/conversations" class="text-indigo-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Edit Conversation</h1>
    <p class="text-gray-500 mb-6">{{ $conv->name }}</p>

    <form method="POST" action="/admin/whatsapp/conversations/{{ $conv->id }}" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input type="text" name="name" value="{{ $conv->name }}" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trigger</label>
                <select name="trigger_event" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none">
                    @foreach(['manual','lead_created','purchase_made','broadcast_reply','schedule'] as $ev)
                    <option value="{{ $ev }}" {{ $conv->trigger_event == $ev ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($ev)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <input type="text" name="description" value="{{ $conv->description }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none" placeholder="Description">
        </div>
        <div>
            <textarea name="steps" required rows="14" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none font-mono text-sm">{{ json_encode($conv->steps, JSON_PRETTY_PRINT) }}</textarea>
        </div>
        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            <i class="fas fa-save mr-2"></i>Update
        </button>
    </form>
</div>
@endsection
