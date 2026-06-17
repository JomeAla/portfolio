@extends('front.customer.layout')

@section('customer-content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Achievements</h1>
            <p class="text-slate-600">Track your progress and unlock rewards</p>
        </div>
        <div class="text-right">
            <div class="text-3xl font-bold text-amber-600">{{ $totalPoints ?? 0 }}</div>
            <p class="text-sm text-slate-500">Total Points</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($achievements as $achievement)
    <div class="bg-white rounded-2xl border overflow-hidden transition-all duration-300
        {{ $achievement['is_awarded'] ? 'border-emerald-300 bg-emerald-50/30 shadow-md' : 'border-slate-200 hover:shadow-md' }}">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0
                    {{ $achievement['is_awarded'] ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400' }}">
                    <i class="fas {{ $achievement['icon'] }} text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-900 {{ $achievement['is_awarded'] ? '' : 'opacity-60' }}">
                        {{ $achievement['name'] }}
                        @if($achievement['is_awarded'])
                        <i class="fas fa-check-circle text-emerald-500 ml-1"></i>
                        @endif
                    </h3>
                    <p class="text-sm text-slate-600 mt-1">{{ $achievement['description'] }}</p>
                    @if($achievement['points'] > 0)
                    <span class="inline-flex items-center gap-1 text-xs font-medium mt-2 px-2 py-1 rounded-full
                        {{ $achievement['is_awarded'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <i class="fas fa-star"></i> {{ $achievement['points'] }} pts
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
