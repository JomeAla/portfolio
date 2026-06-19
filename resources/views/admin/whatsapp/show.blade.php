@extends('layouts.admin')

@section('title', $broadcast->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp" class="text-green-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Broadcasts
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $broadcast->name }}</h1>
                    <p class="text-gray-500 mt-1">
                        Created {{ $broadcast->created_at->format('M j, Y g:i A') }}
                    </p>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    {{ $broadcast->status == 'sent' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $broadcast->status == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                    {{ $broadcast->status == 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $broadcast->status == 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ ucfirst($broadcast->status) }}
                </span>
            </div>

            @if($broadcast->scheduled_at)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-clock mr-2"></i>
                    Scheduled for {{ $broadcast->scheduled_at->format('M j, Y g:i A') }}
                </p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-3">Message Preview</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 whitespace-pre-wrap text-gray-800 leading-relaxed">
                    {{ $broadcast->message }}
                </div>
            </div>

            @if($broadcast->log && count($broadcast->log) > 0)
            <div class="mt-8">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Delivery Log</h3>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 max-h-64 overflow-y-auto">
                    @foreach($broadcast->log as $entry)
                    <div class="text-xs font-mono {{ isset($entry['error']) ? 'text-red-600' : 'text-green-600' }} mb-1">
                        {{ $entry['phone'] ?? 'N/A' }}: {{ $entry['error'] ?? 'Delivered' }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Delivery Stats</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Total Recipients</p>
                    <p class="text-xl font-bold text-slate-800">{{ $broadcast->total_recipients ?: '-' }}</p>
                </div>
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500">Delivered</p>
                    <p class="text-xl font-bold text-green-600">{{ $broadcast->sent_count ?: '-' }}</p>
                </div>
                <div class="border-t pt-4">
                    <p class="text-sm text-gray-500">Failed</p>
                    <p class="text-xl font-bold text-red-600">{{ $broadcast->failed_count ?: '-' }}</p>
                </div>
            </div>
        </div>

        @if($broadcast->status == 'draft')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6 mt-6">
            <h3 class="font-semibold text-gray-900 mb-4">Send Broadcast</h3>
            <form method="POST" action="/admin/whatsapp/{{ $broadcast->id }}/send" onsubmit="return confirm('Send this broadcast to all opted-in contacts?')">
                @csrf
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Send Now
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
