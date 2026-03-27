@extends('layouts.app')

@section('title', 'Store - Digital Products')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold mb-2">Digital Store</h1>
            <p class="text-blue-100">Premium templates, scripts, and digital products</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="flex flex-wrap gap-4 mb-8">
            <a href="{{ route('store') }}" class="px-4 py-2 rounded-full {{ !request('type') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                All
            </a>
            <a href="{{ route('store', ['type' => 'template']) }}" class="px-4 py-2 rounded-full {{ request('type') == 'template' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Templates
            </a>
            <a href="{{ route('store', ['type' => 'code']) }}" class="px-4 py-2 rounded-full {{ request('type') == 'code' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Code/Scripts
            </a>
            <a href="{{ route('store', ['type' => 'ebook']) }}" class="px-4 py-2 rounded-full {{ request('type') == 'ebook' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                E-books
            </a>
            <a href="{{ route('store', ['type' => 'software']) }}" class="px-4 py-2 rounded-full {{ request('type') == 'software' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Software
            </a>
        </div>

        @if($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
                @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-box text-4xl text-gray-400"></i>
                </div>
                @endif
                <div class="p-5">
                    <span class="text-xs font-medium text-blue-600 uppercase">{{ $product->type }}</span>
                    <h3 class="font-semibold text-lg text-slate-900 mt-1">{{ $product->title }}</h3>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $product->short_description ?? Str::limit($product->description, 80) }}</p>
                    <div class="flex items-center justify-between mt-4">
                        <div>
                            @if($product->isOnSale())
                            <span class="text-gray-400 line-through">₦{{ number_format($product->price) }}</span>
                            <span class="text-xl font-bold text-emerald-600">₦{{ number_format($product->sale_price) }}</span>
                            @else
                            <span class="text-xl font-bold text-slate-900">₦{{ number_format($product->price) }}</span>
                            @endif
                        </div>
                        <a href="{{ route('store.show', $product->slug) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            View
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <i class="fas fa-store text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No products available yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection
