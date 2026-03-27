@extends('layouts.admin')

@section('title', 'Projects')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Projects</h1>
        <p class="text-gray-600 mt-2">Manage your portfolio projects</p>
    </div>
    <a href="/admin/projects/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Add Project
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Title</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Category</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Featured</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="font-medium text-slate-900">{{ $project->title }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                        {{ $project->category }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($project->is_featured)
                    <span class="text-emerald-600"><i class="fas fa-check-circle"></i> Yes</span>
                    @else
                    <span class="text-gray-400">No</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="/admin/projects/{{ $project->id }}/edit" class="text-gray-400 hover:text-blue-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="/admin/projects/{{ $project->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                    No projects yet. <a href="/admin/projects/create" class="text-blue-600 hover:underline">Create your first project</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $projects->links() }}
</div>
@endsection
