@extends('layouts.admin')

@section('title', 'Brief Details')

@section('content')
<div class="mb-8">
    <a href="/admin/briefs" class="text-blue-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Briefs
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Project Details</h1>
            
            <div class="space-y-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Project Type</h3>
                    <p class="text-lg text-slate-900">{{ ucfirst($brief->project_type) }}</p>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Description</h3>
                    <p class="text-slate-700 whitespace-pre-wrap">{{ $brief->description }}</p>
                </div>

                @if($brief->budget_range)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Budget Range</h3>
                    <p class="text-slate-900">{{ $brief->budget_range }}</p>
                </div>
                @endif

                @if($brief->timeline)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Timeline</h3>
                    <p class="text-slate-900">{{ $brief->timeline }}</p>
                </div>
                @endif

                @if($brief->files)
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Attached Files</h3>
                    <ul class="space-y-2">
                        @foreach(json_decode($brief->files) as $file)
                        <li>
                            <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-blue-600 hover:underline">
                                <i class="fas fa-file mr-2"></i>{{ basename($file) }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Client Information</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Name</h3>
                    <p class="text-slate-900">{{ $brief->name }}</p>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Email</h3>
                    <a href="mailto:{{ $brief->email }}" class="text-blue-600 hover:underline">{{ $brief->email }}</a>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Phone</h3>
                    <a href="tel:{{ $brief->phone }}" class="text-blue-600 hover:underline">{{ $brief->phone }}</a>
                </div>
                
                @if($brief->company)
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Company</h3>
                    <p class="text-slate-900">{{ $brief->company }}</p>
                </div>
                @endif

                <div>
                    <h3 class="text-sm font-medium text-gray-500">Submitted</h3>
                    <p class="text-slate-900">{{ $brief->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Update Status</h2>
            
            <form method="POST" action="/admin/briefs/{{ $brief->id }}">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="new" {{ $brief->status == 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ $brief->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="in_progress" {{ $brief->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $brief->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Add internal notes...">{{ $brief->notes }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        Update
                    </button>
                </div>
            </form>

            <form method="POST" action="/admin/briefs/{{ $brief->id }}" class="mt-3" onsubmit="return confirm('Are you sure you want to delete this brief?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    Delete Brief
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
