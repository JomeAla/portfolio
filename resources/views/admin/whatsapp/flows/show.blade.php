@extends('layouts.admin')

@section('title', $flow->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/flows" class="text-pink-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Flows</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $flow->name }}</h1>
                    <p class="text-gray-500 mt-1">{{ $flow->description }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $flow->status == 'deployed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst($flow->status) }}
                </span>
            </div>

            @if($flow->flow_id)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-blue-700"><strong>Meta Flow ID:</strong> {{ $flow->flow_id }}</p>
            </div>
            @endif

            <h3 class="text-sm font-medium text-gray-500 mb-2">Flow JSON Definition</h3>
            <pre class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs font-mono overflow-auto max-h-96">{{ json_encode($flow->flow_json, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
            <div class="space-y-3">
                @if($flow->status == 'draft')
                <form method="POST" action="/admin/whatsapp/flows/{{ $flow->id }}/deploy">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                        <i class="fas fa-rocket mr-2"></i>Deploy Flow
                    </button>
                </form>
                @endif
                <a href="/admin/whatsapp/flows/{{ $flow->id }}/edit" class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6 mt-6">
            <h3 class="font-semibold text-gray-900 mb-2">Info</h3>
            <div class="text-sm space-y-2 text-gray-600">
                <p><span class="text-gray-400">Created:</span> {{ $flow->created_at->format('M j, Y g:i A') }}</p>
                <p><span class="text-gray-400">Flow ID:</span> {{ $flow->flow_id ?: 'Not deployed' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
