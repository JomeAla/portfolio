@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">A/B Testing</h1>
            <p class="text-slate-600 mt-1">Test email subject lines and content to optimize engagement</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="font-bold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i>How A/B Testing Works</h3>
        <p class="text-sm text-blue-700">Create tests for email subject lines or content. Leads receive random variations (A or B). Track opens and clicks to find the winner.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Create New Test</h2>
                
                <form method="POST" action="{{ route('admin.marketing.ab-tests.store') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Test Name</label>
                        <input type="text" name="name" required placeholder="e.g., Welcome Subject Test" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Subject A</label>
                        <input type="text" name="subject_a" required placeholder="Version A subject" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Subject B</label>
                        <input type="text" name="subject_b" required placeholder="Version B subject" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Body A (optional)</label>
                            <textarea name="body_a" rows="2" placeholder="Version A content" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Body B (optional)</label>
                            <textarea name="body_b" rows="2" placeholder="Version B content" 
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm"></textarea>
                        </div>
                    </div>

                    <button type="submit" 
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Create Test
                    </button>
                </form>
            </div>
        </div>

        <!-- Tests List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Tests ({{ $tests->total() }})</h3>
                </div>

                @if($tests->isEmpty())
                <div class="p-8 text-center">
                    <div class="text-slate-400 mb-2">
                        <i class="fas fa-flask text-4xl"></i>
                    </div>
                    <p class="text-slate-600">No A/B tests yet</p>
                </div>
                @else
                <div class="divide-y divide-slate-200">
                    @foreach($tests as $test)
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-medium text-slate-800">{{ $test->name }}</h4>
                                    @if($test->status === 'running')
                                        <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Running</span>
                                    @elseif($test->status === 'completed')
                                        <span class="px-2 py-0.5 text-xs rounded bg-slate-100 text-slate-600">Completed</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-700">Draft</span>
                                    @endif
                                    @if($test->winner)
                                        <span class="px-2 py-0.5 text-xs rounded bg-indigo-100 text-indigo-700">
                                            Winner: {{ strtoupper($test->winner) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500">
                                    Created {{ $test->created_at->diffForHumans() }}
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                @if($test->status === 'draft')
                                    <form method="POST" action="{{ route('admin.marketing.ab-tests.start', $test) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">
                                            Start
                                        </button>
                                    </form>
                                @elseif($test->status === 'running')
                                    <form method="POST" action="{{ route('admin.marketing.ab-tests.stop', $test) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
                                            Stop
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.marketing.ab-tests.destroy', $test) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-xs text-red-600 hover:text-red-800"
                                        onclick="return confirm('Delete this test?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-slate-50 rounded p-3">
                                <div class="font-medium text-slate-700 mb-1">A</div>
                                <div class="text-slate-600 text-xs truncate">{{ $test->subject_a }}</div>
                            </div>
                            <div class="bg-slate-50 rounded p-3">
                                <div class="font-medium text-slate-700 mb-1">B</div>
                                <div class="text-slate-600 text-xs truncate">{{ $test->subject_b }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div class="flex items-center justify-between bg-blue-50 rounded p-2">
                                <div>
                                    <div class="text-xs text-blue-600">Opens A</div>
                                    <div class="text-lg font-bold text-blue-700">{{ $test->opens_a ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-blue-600">Clicks A</div>
                                    <div class="text-lg font-bold text-blue-700">{{ $test->clicks_a ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between bg-green-50 rounded p-2">
                                <div>
                                    <div class="text-xs text-green-600">Opens B</div>
                                    <div class="text-lg font-bold text-green-700">{{ $test->opens_b ?? 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-green-600">Clicks B</div>
                                    <div class="text-lg font-bold text-green-700">{{ $test->clicks_b ?? 0 }}</div>
                                </div>
                            </div>
                        </div>

                        @if($test->opens_a > 0 || $test->opens_b > 0)
                        <div class="mt-3">
                            <div class="relative h-4 bg-slate-200 rounded-full overflow-hidden">
                                @php
                                    $totalOpens = $test->opens_a + $test->opens_b;
                                    $percentA = $totalOpens > 0 ? round(($test->opens_a / $totalOpens) * 100) : 50;
                                @endphp
                                <div class="absolute left-0 top-0 h-full bg-blue-500" style="width: {{ $percentA }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-slate-500 mt-1">
                                <span>A: {{ $percentA }}%</span>
                                <span>B: {{ 100 - $percentA }}%</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="mt-4">
                {{ $tests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection