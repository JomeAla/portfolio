@extends('layouts.admin')

@section('title', 'New Email Sequence')

@section('content')
<form method="POST" action="/admin/marketing/sequences">
    @csrf
    <div class="mb-6">
        <a href="/admin/marketing/sequences" class="text-blue-600 hover:text-blue-800">&larr; Back to Sequences</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Create Sequence</h2>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Name</label>
            <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="e.g., Welcome Series">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
            <textarea name="description" rows="3" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Optional description"></textarea>
        </div>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" checked class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-slate-700">Active</span>
            </label>
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">
            Create Sequence
        </button>
    </div>
</form>
@endsection