@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Email Templates</h1>
            <p class="text-slate-600 mt-1">Create and manage reusable email templates</p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.marketing.email-templates.store') }}" style="display:inline;">
                @csrf
                <button type="submit" name="seed_defaults" value="1" 
                    class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50 text-sm">
                    <i class="fas fa-magic mr-2"></i>Add Defaults
                </button>
            </form>
            <a href="{{ route('admin.marketing.email-templates.create') }}" 
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
                <i class="fas fa-plus mr-2"></i>New Template
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $template)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-slate-800">{{ $template->name }}</h3>
                <span class="px-2 py-1 text-xs rounded {{ $template->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            
            <p class="text-sm text-slate-500 mb-3">{{ $template->description ?? 'No description' }}</p>
            
            @if($template->category)
            <span class="px-2 py-1 text-xs bg-slate-100 text-slate-600 rounded mb-3 inline-block">
                {{ $template->category }}
            </span>
            @endif
            
            <div class="bg-slate-50 rounded p-3 mb-3">
                <p class="text-xs text-slate-500 mb-1"><strong>Subject:</strong></p>
                <p class="text-sm text-slate-700 truncate">{{ $template->subject }}</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.marketing.email-templates.edit', $template) }}" 
                    class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-edit mr-1"></i>Edit
                </a>
                <a href="{{ route('admin.marketing.email-templates.preview', $template) }}" target="_blank"
                    class="text-slate-600 hover:text-slate-800 text-sm">
                    <i class="fas fa-eye mr-1"></i>Preview
                </a>
                <form method="POST" action="{{ route('admin.marketing.email-templates.duplicate', $template) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="text-slate-600 hover:text-slate-800 text-sm">
                        <i class="fas fa-copy mr-1"></i>Duplicate
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.marketing.email-templates.destroy', $template) }}" style="display:inline;">
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
                <i class="fas fa-envelope-open text-5xl"></i>
            </div>
            <p class="text-slate-600 mb-4">No email templates yet</p>
            <a href="{{ route('admin.marketing.email-templates.create') }}" 
                class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                Create Template
            </a>
        </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $templates->links() }}
    </div>
</div>
@endsection