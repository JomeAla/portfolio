@extends('layouts.app')

@section('title', $product->title ?? 'Product')

@section('content')
<?php $p = $product; ?>
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <a href="/store" class="text-blue-600 hover:underline mb-6 inline-flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to Store
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <img src="{{ asset($p->image ?? '') }}" alt="{{ $p->title }}" class="w-full rounded-2xl mb-6">

                <div class="bg-white rounded-2xl p-6 mt-6">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">Description</h2>
                    <div class="text-gray-600">
                        @if($p->full_description)
                            {!! $p->full_description !!}
                        @else
                            {{ $p->description ?? 'No description available' }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 sticky top-6">
                    <span class="text-sm font-medium text-blue-600 uppercase">{{ $p->type ?? 'product' }}</span>
                    <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $p->title }}</h1>
                    
                    <div class="mt-4">
                        @if($p->sale_price && $p->sale_price < $p->price)
                        <span class="text-gray-400 line-through text-lg">₦{{ number_format($p->price) }}</span>
                        <span class="text-3xl font-bold text-emerald-600">₦{{ number_format($p->sale_price) }}</span>
                        @else
                        <span class="text-3xl font-bold text-slate-900">₦{{ number_format($p->price ?? 0) }}</span>
                        @endif
                    </div>

                    <a href="/buy/{{ $p->slug }}" class="mt-6 block w-full bg-emerald-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-emerald-700 text-center">
                        Buy Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection