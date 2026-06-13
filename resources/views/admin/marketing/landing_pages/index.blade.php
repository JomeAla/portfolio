@extends('layouts.admin')

@section('title', 'Landing Pages')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Landing Pages</h1>
        <p class="text-slate-600 mt-2">Create and manage conversion pages</p>
    </div>
    <a href="/admin/marketing/landing-pages/create" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
        <i class="fas fa-plus mr-2"></i>New Page
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($pages as $page)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-bold text-slate-800">{{ $page->title }}</h3>
            <span class="px-2 py-1 text-xs rounded {{ $page->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $page->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <p class="text-sm text-slate-500 mb-4">URL: /l/{{ $page->slug }}</p>
        <div class="flex flex-wrap gap-2 mb-4">
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                {{ $page->leads_count ?? 0 }} leads
            </span>
            @if($page->sequence)
                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">
                    {{ $page->sequence->name }}
                </span>
            @endif
            @if($page->funnel)
                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">
                    <i class="fas fa-funnel-dollar mr-1"></i>{{ $page->funnel->name }}
                </span>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="/admin/marketing/landing-pages/{{ $page->id }}/edit" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
            <a href="/admin/marketing/landing-pages/{{ $page->id }}/embed" class="text-slate-600 hover:text-slate-800 text-sm">Embed</a>
            <a href="/l/{{ $page->slug }}" target="_blank" class="text-sky-600 hover:text-sky-800 text-sm">View</a>
            <form method="POST" action="/admin/marketing/landing-pages/{{ $page->id }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete?')">Delete</button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">
    {{ $pages->links() }}
</div>
@endsection