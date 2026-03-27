@extends('layouts.admin')

@section('title', 'Project Briefs')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Project Briefs</h1>
        <p class="text-gray-600 mt-2">Manage client project submissions</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Client</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Project Type</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Budget</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Date</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($briefs as $brief)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div>
                        <span class="font-medium text-slate-900">{{ $brief->name }}</span>
                        @if($brief->company)
                        <p class="text-sm text-gray-500">{{ $brief->company }}</p>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                        {{ ucfirst($brief->project_type) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $brief->budget_range ?? 'Not specified' }}
                </td>
                <td class="px-6 py-4">
                    @php
                        $statusColors = [
                            'new' => 'bg-yellow-100 text-yellow-700',
                            'contacted' => 'bg-blue-100 text-blue-700',
                            'in_progress' => 'bg-purple-100 text-purple-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                        ];
                    @endphp
                    <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColors[$brief->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst(str_replace('_', ' ', $brief->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $brief->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="/admin/briefs/{{ $brief->id }}" class="text-gray-400 hover:text-blue-600">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="/admin/briefs/{{ $brief->id }}" class="inline">
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
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    No project briefs yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $briefs->links() }}
</div>
@endsection
