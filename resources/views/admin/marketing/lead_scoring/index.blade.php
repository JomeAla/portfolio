@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Lead Scoring</h1>
            <p class="text-slate-600 mt-1">Track and manage lead engagement scores</p>
        </div>
        <form method="POST" action="{{ route('admin.marketing.lead-scoring.recalculate') }}">
            @csrf
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-calculator mr-2"></i>Recalculate All Scores
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <!-- Scoring Rules -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Scoring Rules</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="bg-slate-50 p-3 rounded">
                <div class="font-medium">Email Opened</div>
                <div class="text-green-600">+5 points</div>
            </div>
            <div class="bg-slate-50 p-3 rounded">
                <div class="font-medium">Email Clicked</div>
                <div class="text-green-600">+10 points</div>
            </div>
            <div class="bg-slate-50 p-3 rounded">
                <div class="font-medium">Order Placed</div>
                <div class="text-green-600">+50 points</div>
            </div>
            <div class="bg-slate-50 p-3 rounded">
                <div class="font-medium">Unsubscribed</div>
                <div class="text-red-600">-10 points</div>
            </div>
        </div>
    </div>

    <!-- Leaderboard -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <h2 class="font-bold text-slate-800">Top Leads by Score</h2>
        </div>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Rank</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Lead</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Score</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Source</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($leads as $index => $lead)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        @if($index === 0)<span class="text-2xl">🥇</span>
                        @elseif($index === 1)<span class="text-2xl">🥈</span>
                        @elseif($index === 2)<span class="text-2xl">🥉</span>
                        @else<span class="text-slate-400">{{ $index + 1 }}</span>@endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-800">{{ $lead->name }}</div>
                        <div class="text-sm text-slate-500">{{ $lead->email }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-lg font-bold {{ $lead->score > 50 ? 'text-green-600' : ($lead->score > 20 ? 'text-blue-600' : 'text-slate-600') }}">
                            {{ $lead->score ?? 0 }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded {{ $lead->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $lead->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $lead->source ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $leads->links() }}
    </div>
</div>
@endsection