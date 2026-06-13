@extends('layouts.admin')

@section('title', 'Funnel Overview')

@section('content')
<div class="min-h-screen bg-gray-50">
    <header class="bg-gradient-to-r from-purple-600 to-pink-600 text-white py-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">{{ $funnel->name }}</h1>
                    <p class="text-lg opacity-90 mt-1">{{ $funnel->description ?? '' }}</p>
                </div>
                <a href="/admin/marketing/funnels/{{ $funnel->id }}/edit" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-edit mr-2"></i>Edit Funnel
                </a>
            </div>
        </div>
    </header>
    
    <main class="max-w-6xl mx-auto px-4 py-8">
        <h2 class="text-xl font-bold text-gray-700 mb-4">All Funnel Stages ({{ $funnel->stages->count() }} stages)</h2>
        <div class="grid gap-6">
            @foreach($funnel->stages as $stage)
            @php
                $content = is_array($stage->content) ? $stage->content : json_decode($stage->content, true);
                if (empty($content)) { $content = []; }
                if (isset($content['url'])) { $content['url'] = str_replace('\\/', '/', $content['url']); }
                $stageNum = $stage->order;
                $urlContent = $content['url'] ?? '';
                $hasDownloadPage = strpos($urlContent, 'download-free.php') !== false;
                $hasDirectDownload = strpos($urlContent, '/downloads/') !== false || strpos($urlContent, 'downloads/') !== false;
            @endphp
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                        Stage {{ $stageNum }}
                    </span>
                    <span class="text-gray-500 capitalize">{{ str_replace('_', ' ', $stage->type) }}</span>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $stage->name ?? 'Untitled Stage' }}</h3>
                
                @if($hasDownloadPage)
                <a href="{{ $urlContent }}" target="_blank" class="inline-block mt-3 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Visit Download Page
                </a>
                @elseif($hasDirectDownload)
                <a href="{{ $urlContent }}" target="_blank" class="inline-block mt-3 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-download mr-2"></i>Download File
                </a>
                @elseif(in_array($stage->type, ['landing', 'landing_page']) && !empty($content['url']))
                <a href="{{ $content['url'] }}" target="_blank" class="inline-block mt-3 bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Visit This Stage
                </a>
                @elseif($stage->type === 'checkout')
                <a href="/checkout?funnel={{ $funnel->id }}&stage={{ $stage->id }}" class="inline-block mt-3 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-shopping-cart mr-2"></i>Checkout
                </a>
                @elseif(in_array($stage->type, ['sales_page', 'upsell', 'downsell']) && !empty($content['url']))
                <a href="{{ $content['url'] }}" target="_blank" class="inline-block mt-3 bg-orange-600 text-white px-5 py-2 rounded-lg hover:bg-orange-700">
                    <i class="fas fa-shopping-bag mr-2"></i>View Sales Page
                </a>
                @else
                <div class="mt-3 p-3 bg-gray-100 rounded-lg">
                    <p class="text-sm text-gray-600">
                        Type: {{ $stage->type }}
                    </p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </main>
    
    <footer class="text-center py-6 text-gray-500">
        <p>&copy; {{ date('Y') }} Joala Ventures | <a href="/admin/marketing/funnels/{{ $funnel->id }}/edit" class="text-purple-600 underline">Edit Funnel</a></p>
    </footer>
</div>
@endsection