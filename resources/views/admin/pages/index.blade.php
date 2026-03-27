@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Pages</h1>
        <p class="text-gray-600 mt-2">Manage your website pages</p>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Title</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Slug</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Last Updated</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pages as $page)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="font-medium text-slate-900">{{ $page->title }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-gray-600">{{ $page->slug }}</span>
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $page->updated_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4">
                    <a href="/admin/pages/{{ $page->id }}/edit" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                    No pages found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
