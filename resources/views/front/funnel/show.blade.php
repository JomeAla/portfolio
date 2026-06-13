@extends('layouts.app')

@section('title', $funnel->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-slate-800 mb-4">{{ $funnel->name }}</h1>
            <p class="text-lg text-slate-600">{{ $funnel->description }}</p>
        </div>

        <div class="grid gap-6">
            @foreach($funnel->stages as $index => $stage)
            <div class="bg-white rounded-xl shadow-lg p-6 flex items-center gap-6">
                <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center text-xl font-bold">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-slate-800">{{ $stage->name }}</h3>
                    <p class="text-slate-500 capitalize">{{ str_replace('_', ' ', $stage->type) }}</p>
                </div>
                @if($stage->type === 'landing' && !empty($stage->content['url']))
                <a href="{{ $stage->content['url'] }}?funnel={{ $funnel->id }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    View
                </a>
                @elseif($stage->type === 'checkout' && $funnel->product)
                <a href="/store/{{ $funnel->product->slug }}?funnel={{ $funnel->id }}" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                    Buy Now
                </a>
                @endif
            </div>
            @endforeach
        </div>

        @if($funnel->is_active)
        <div class="mt-12 text-center">
            <a href="{{ $funnel->checkout_url ?? '#' }}" class="inline-block bg-blue-600 text-white text-xl font-bold px-12 py-4 rounded-xl hover:bg-blue-700 shadow-lg">
                Get Started
            </a>
        </div>
        @endif
    </div>
</div>
@endsection