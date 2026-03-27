@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
    <p class="text-gray-600 mt-2">Add a digital product to your store</p>
</div>

<form method="POST" action="/admin/products" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Basic Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Title *</label>
                <input type="text" name="title" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="E.g., Laravel Admin Template">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                <input type="text" name="short_description" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Brief tagline (shown in product cards)">
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Description *</label>
                <textarea name="description" rows="6" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Detailed product description..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Type *</label>
                <select name="type" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="template">Template/Theme</option>
                    <option value="code">Code/Script</option>
                    <option value="ebook">E-book</option>
                    <option value="software">Software</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                <input type="number" name="order" value="0" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Display order">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Pricing</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Regular Price (₦) *</label>
                <input type="number" name="price" step="0.01" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="0.00">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sale Price (₦)</label>
                <input type="number" name="sale_price" step="0.01" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Leave empty for no sale">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Media & Files</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                <p class="text-xs text-gray-500 mt-1">Main product image (recommended: 600x400)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product File (Digital Download)</label>
                <input type="file" name="file"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                <p class="text-xs text-gray-500 mt-1">ZIP, PDF, or other downloadable file</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Gallery Images</label>
                <input type="file" name="images[]" accept="image/*" multiple
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                <p class="text-xs text-gray-500 mt-1">Additional product screenshots</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Status</h2>
        
        <div class="space-y-4">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" checked
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Active (visible in store)</span>
            </label>

            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_featured" value="1"
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Featured (show on homepage)</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Create Product
        </button>
        <a href="/admin/products" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
