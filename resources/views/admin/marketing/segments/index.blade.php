@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Segments</h1>
            <p class="text-slate-600 mt-1">Organize leads into dynamic segments</p>
        </div>
        <a href="{{ route('admin.marketing.segments.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>New Segment
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($segments as $segment)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-slate-800">{{ $segment->name }}</h3>
                <span class="px-2 py-1 text-xs rounded {{ $segment->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $segment->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            
            <p class="text-sm text-slate-500 mb-3">{{ $segment->description ?? 'No description' }}</p>
            
            <div class="bg-slate-50 rounded p-3 mb-3">
                <div class="text-2xl font-bold text-indigo-600">{{ $segment->leads_count }}</div>
                <div class="text-xs text-slate-500">Leads in segment</div>
            </div>
            
            @if($segment->conditions)
            <div class="text-xs text-slate-500 mb-3">
                @foreach($segment->conditions as $condition)
                    <span class="inline-block bg-slate-100 px-2 py-1 rounded mr-1 mb-1">
                        {{ $condition['field'] }}: {{ $condition['value'] }}
                    </span>
                @endforeach
            </div>
            @endif

            <div class="flex gap-2">
                <a href="{{ route('admin.marketing.segments.edit', $segment) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-edit mr-1"></i>Edit
                </a>
                <form method="POST" action="{{ route('admin.marketing.segments.destroy', $segment) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete?')">
                        <i class="fas fa-trash mr-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
            <div class="text-slate-400 mb-4">
                <i class="fas fa-layer-group text-5xl"></i>
            </div>
            <p class="text-slate-600 mb-4">No segments yet</p>
            <a href="{{ route('admin.marketing.segments.create') }}" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                Create Segment
            </a>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $segments->links() }}
    </div>
</div>
@endsection