@extends('layouts.admin')

@section('title', 'Edit Step')

@section('content')
<form method="POST" action="/admin/marketing/steps/{{ $sequenceStep->id }}">
    @csrf
    @method('PUT')
    
    <div class="mb-6 flex items-center justify-between">
        <a href="/admin/marketing/sequences/{{ $sequenceStep->sequence->id }}/edit" class="text-blue-600 hover:text-blue-800">
            &larr; Back to Sequence
        </a>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            Update Step
        </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Edit Email Step</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Subject</label>
                <input type="text" name="subject" value="{{ $sequenceStep->subject }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Delay (days)</label>
                <input type="number" name="delay_days" value="{{ $sequenceStep->delay_days }}" min="0" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Email Body</label>
            <textarea name="body" rows="12" required class="w-full border border-slate-300 rounded-lg px-4 py-2 font-mono text-sm">{{ $sequenceStep->body }}</textarea>
        </div>
    </div>
</form>
@endsection