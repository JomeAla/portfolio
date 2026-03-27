@extends('layouts.admin')

@section('title', 'Testimonials')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Testimonials</h1>
        <p class="text-gray-600 mt-2">Manage client testimonials</p>
    </div>
    <a href="/admin/testimonials/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Add Testimonial
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Name</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Company</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($testimonials as $testimonial)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="font-medium text-slate-900">{{ $testimonial->name }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-gray-500">{{ $testimonial->company }}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="/admin/testimonials/{{ $testimonial->id }}/edit" class="text-gray-400 hover:text-blue-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="/admin/testimonials/{{ $testimonial->id }}" class="inline">
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
                    No testimonials yet. <a href="/admin/testimonials/create" class="text-blue-600 hover:underline">Add your first testimonial</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $testimonials->links() }}
</div>
@endsection
