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
            <a href="{{ route('store', ['type' => 'webapp']) }}" class="px-4 py-2 rounded-full {{ request('type') == 'webapp' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Web Apps
            </a>
            <a href="{{ route('store', ['type' => 'mobileapp']) }}" class="px-4 py-2 rounded-full {{ request('type') == 'mobileapp' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Mobile Apps
            </a>
            <a href="{{ route('store', ['type' => 'desktopapp']) }}" class="px-4 py-2 rounded-full {{ request('type') == 'desktopapp' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                Desktop Apps
            </a>
        </div>

        @if($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
                @if($product->image)
                <img src="{{ asset($product->image) }}" alt="{{ $product->title }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center relative">
                    <div class="text-center text-white">
                        <i class="fas fa-envelope text-4xl mb-2"></i>
                        <p class="text-sm font-medium">Email Templates</p>
                    </div>
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
                        @if($product->slug === 'email-sequence-templates-pack')
                        <a href="/email-sequence-templates-pack.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'email-marketing-premium-bundle')
                        <a href="/email-marketing-premium-bundle.php" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'done-for-you-email-automation')
                        <a href="/done-for-you-email-automation.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'course-creator-kit')
                        <a href="/course-creator-kit.php" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'local-business-digital-kit')
                        <a href="/local-business-kit.php" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                            View
                        </a>
@elseif($product->slug === 'saas-starter-kit')
                        <a href="/saas-starter-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'website-audit-kit')
                        <a href="/website-audit-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'course-creator-kit')
                        <a href="/course-creator-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'local-business-digital-kit')
                        <a href="/local-business-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'wordpress-starter-kit')
                        <a href="/wordpress-starter-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'email-sequence-templates-pack')
                        <a href="/email-sequence-templates-pack.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'email-marketing-premium-bundle')
                        <a href="/email-marketing-premium-bundle.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'done-for-you-email-automation')
                        <a href="/done-for-you-email-automation.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'freelancer-toolkit')
                        <a href="/freelancer-toolkit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'instagram-growth-system')
                        <a href="/instagram-growth-system.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'nigerian-business-digital-kit')
                        <a href="/nigerian-business-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'church-organization-website-kit')
                        <a href="/church-website-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'restaurant-pos-kit')
                        <a href="/restaurant-pos-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'school-management-system')
                        <a href="/school-management-system.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'real-estate-property-kit')
                        <a href="/real-estate-property-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @elseif($product->slug === 'e-commerce-starter-kit')
                        <a href="/ecommerce-starter-kit.php" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            View
                        </a>
                        @else
                        <a href="{{ route('store.show', $product->slug) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            View
                        </a>
                        @endif
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
