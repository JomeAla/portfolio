@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.marketing.analytics') }}" class="text-slate-600 hover:text-slate-800">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Marketing Funnel</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="space-y-4">
            @foreach($stages as $index => $stage)
            <div class="flex items-center gap-4">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-slate-800">{{ $stage['name'] }}</span>
                        <span class="text-lg font-bold text-indigo-600">{{ $stage['count'] }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-3">
                        @php $percent = $stages[0]['count'] > 0 ? ($stage['count'] / $stages[0]['count']) * 100 : 0; @endphp
                        <div class="bg-indigo-600 h-3 rounded-full transition-all" style="width: {!! $percent !!}%"></div>
                    </div>
                    @if($index > 0)
                    <div class="text-xs text-slate-500 mt-1">
                        {{ round($percent, 1) }}% of total leads
                    </div>
                    @endif
                </div>
            </div>
            @if($index < count($stages) - 1)
            <div class="flex justify-center">
                <i class="fas fa-chevron-down text-slate-300"></i>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</div>
@endsection