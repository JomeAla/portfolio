@extends('layouts.admin')

@section('title', 'Promotional Banners')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Promotional Banners</h1>
        <p class="text-gray-600 mt-2">Manage site-wide promotional banners</p>
    </div>
    <a href="/admin/banners/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Add Banner
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($banners as $banner)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
        @if($banner->image)
        <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" class="w-full h-40 object-cover">
        @else
        <div class="w-full h-40 flex items-center justify-center" style="background-color: {{ $banner->background_color }}">
            <span class="text-2xl font-bold" style="color: {{ $banner->text_color }}">{{ $banner->title }}</span>
        </div>
        @endif
        <div class="p-4">
            <h3 class="font-semibold text-slate-900">{{ $banner->title }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $banner->message }}</p>
            <div class="flex items-center justify-between mt-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $banner->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                </span>
                <div class="flex gap-2">
                    <a href="/admin/banners/{{ $banner->id }}/edit" class="text-gray-400 hover:text-blue-600">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form method="POST" action="/admin/banners/{{ $banner->id }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-600" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-12 text-gray-500">
        No banners yet. <a href="/admin/banners/create" class="text-blue-600 hover:underline">Create your first banner</a>
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $banners->links() }}
</div>
@endsection
