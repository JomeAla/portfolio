@extends('layouts.admin')

@section('title', 'Tags')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Tags</h1>
        <p class="text-slate-600 mt-2">Categorize and segment your leads</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    @forelse($tags as $tag)
    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="w-4 h-4 rounded-full" style="background-color: {{ $tag->color }}"></span>
            <div>
                <h3 class="font-medium text-slate-800">{{ $tag->name }}</h3>
                <p class="text-sm text-slate-500">{{ $tag->leads_count }} leads</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="editTag({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}')" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
            <form method="POST" action="/admin/marketing/tags/{{ $tag->id }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this tag?')">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <p class="text-slate-500 col-span-3">No tags yet.</p>
    @endforelse
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Create New Tag</h2>
    <form method="POST" action="/admin/marketing/tags" class="flex gap-4 items-end">
        @csrf
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1">Tag Name</label>
            <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-4 py-2">
        </div>
        <div class="w-32">
            <label class="block text-sm font-medium text-slate-700 mb-1">Color</label>
            <input type="color" name="color" value="#6366f1" class="w-full h-10 border border-slate-300 rounded-lg">
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
            Create Tag
        </button>
    </form>
</div>
@endsection
