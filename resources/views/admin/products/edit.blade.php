@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
    <p class="text-gray-600 mt-2">Update your product details</p>
</div>

<form method="POST" action="/admin/products/{{ $product->id }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Basic Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Title *</label>
                <input type="text" name="title" value="{{ $product->title }}" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                <input type="text" name="short_description" value="{{ $product->short_description }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Description *</label>
                <textarea name="description" rows="6" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $product->description }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Type *</label>
                <select name="type" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="template" {{ $product->type == 'template' ? 'selected' : '' }}>Template/Theme</option>
                    <option value="code" {{ $product->type == 'code' ? 'selected' : '' }}>Code/Script</option>
                    <option value="ebook" {{ $product->type == 'ebook' ? 'selected' : '' }}>E-book</option>
                    <option value="software" {{ $product->type == 'software' ? 'selected' : '' }}>Software</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                <input type="number" name="order" value="{{ $product->order }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Pricing</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Regular Price (₦) *</label>
                <input type="number" name="price" step="0.01" value="{{ $product->price }}" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sale Price (₦)</label>
                <input type="number" name="sale_price" step="0.01" value="{{ $product->sale_price }}" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Leave empty for no sale">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Media & Files</h2>
        
        @if($product->image)
        <div class="mb-4">
            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" class="w-48 h-32 object-cover rounded-lg">
            <p class="text-sm text-gray-500 mt-1">Current image</p>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Change Image</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Change File</label>
                <input type="file" name="file"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                @if($product->file_path)
                <p class="text-sm text-emerald-600 mt-1">File uploaded</p>
                @endif
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Add Gallery Images</label>
                <input type="file" name="images[]" accept="image/*" multiple
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
        </div>

        @if($product->images)
        <div class="mt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Current Gallery</p>
            <div class="flex flex-wrap gap-2">
                @foreach(json_decode($product->images) as $image)
                <img src="{{ asset('storage/' . $image) }}" alt="Gallery" class="w-24 h-24 object-cover rounded-lg">
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Status</h2>
        
        <div class="space-y-4">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Active (visible in store)</span>
            </label>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }}
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Featured (show on homepage)</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Update Product
        </button>
        <a href="/admin/products" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
