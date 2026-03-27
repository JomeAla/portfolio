@extends('layouts.admin')

@section('title', 'Services')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Services</h1>
        <p class="text-gray-600 mt-2">Manage your service offerings</p>
    </div>
    <a href="/admin/services/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Add Service
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Title</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Description</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($services as $service)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="font-medium text-slate-900">{{ $service->title }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-gray-500">{{ Str::limit($service->description, 80) }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="/admin/services/{{ $service->id }}/edit" class="text-gray-400 hover:text-blue-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="/admin/services/{{ $service->id }}" class="inline">
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
                <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                    No services yet. <a href="/admin/services/create" class="text-blue-600 hover:underline">Add your first service</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $services->links() }}
</div>
@endsection
