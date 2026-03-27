@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Products</h1>
        <p class="text-gray-600 mt-2">Manage your digital products</p>
    </div>
    <a href="/admin/products/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Add Product
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Product</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Type</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Price</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($products as $product)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" class="w-12 h-12 object-cover rounded-lg">
                        @else
                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-gray-400"></i>
                        </div>
                        @endif
                        <div>
                            <span class="font-medium text-slate-900">{{ $product->title }}</span>
                            @if($product->is_featured)
                            <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Featured</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                        {{ ucfirst($product->type) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    @if($product->isOnSale())
                    <span class="text-gray-400 line-through">₦{{ number_format($product->price, 2) }}</span>
                    <span class="text-emerald-600 font-semibold ml-2">₦{{ number_format($product->sale_price, 2) }}</span>
                    @else
                    <span class="font-medium text-slate-900">₦{{ number_format($product->price, 2) }}</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($product->is_active)
                    <span class="text-emerald-600"><i class="fas fa-check-circle"></i> Active</span>
                    @else
                    <span class="text-gray-400">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="/admin/products/{{ $product->id }}/edit" class="text-gray-400 hover:text-blue-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="/admin/products/{{ $product->id }}" class="inline">
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
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                    No products yet. <a href="/admin/products/create" class="text-blue-600 hover:underline">Add your first product</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $products->links() }}
</div>
@endsection
