@extends('layouts.app')

@section('title', 'Payment Error')

@section('content')
<section class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-lg mx-auto px-4 text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-times text-3xl text-red-600"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Payment Failed</h1>
            <p class="text-slate-600 mb-6">{{ $message ?? 'There was an issue processing your payment.' }}</p>

            <a href="{{ route('home') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl">
                Back to Home
            </a>
        </div>
    </div>
</section>
@endsection
