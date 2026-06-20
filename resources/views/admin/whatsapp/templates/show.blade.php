@extends('layouts.admin')

@section('title', $template->name)

@section('content')
<div class="mb-6">
    <a href="/admin/whatsapp/templates" class="text-green-600 hover:underline flex items-center gap-2"><i class="fas fa-arrow-left"></i> Back to Templates</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $template->name }}</h1>
                    <div class="flex gap-2 mt-2">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">{{ ucfirst($template->category) }}</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $template->message_type == 'text' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $template->message_type == 'interactive' ? 'bg-purple-100 text-purple-700' : '' }}
                            {{ $template->message_type == 'media' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $template->message_type == 'flow' ? 'bg-pink-100 text-pink-700' : '' }}">
                            {{ ucfirst($template->message_type) }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="/admin/whatsapp/templates/{{ $template->id }}/toggle" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1 text-sm font-medium rounded-full {{ $template->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($template->status) }}
                    </button>
                </form>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
                @if($template->header_value)
                    <div class="text-sm font-medium text-gray-500 mb-2">Header: {{ $template->header_value }}</div>
                @endif
                <div class="text-gray-800 whitespace-pre-wrap">{{ $template->body }}</div>
                @if($template->footer)
                    <div class="text-sm text-gray-400 mt-4 border-t pt-2">{{ $template->footer }}</div>
                @endif
            </div>

            @if($template->buttons)
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Buttons ({{ count($template->buttons) }})</h3>
                <div class="space-y-2">
                    @foreach($template->buttons as $btn)
                    <div class="bg-gray-50 border rounded-lg px-4 py-2 text-sm">
                        <span class="font-medium">{{ $btn['title'] ?? 'N/A' }}</span>
                        <span class="text-gray-400 ml-2">({{ $btn['type'] ?? 'quick_reply' }})</span>
                        @if(!empty($btn['url'])) <span class="text-blue-500 ml-2">{{ $btn['url'] }}</span> @endif
                        @if(!empty($btn['phone'])) <span class="text-blue-500 ml-2">{{ $btn['phone'] }}</span> @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($template->sections)
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">List Sections</h3>
                <pre class="bg-gray-50 border rounded-lg p-4 text-xs font-mono">{{ json_encode($template->sections, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif

            @if($template->media_url)
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Media</h3>
                <a href="{{ $template->media_url }}" target="_blank" class="text-blue-600 hover:underline text-sm">{{ $template->media_url }}</a>
            </div>
            @endif

            <div class="mt-6">
                <a href="/admin/whatsapp/templates/{{ $template->id }}/preview" target="_blank" class="text-blue-600 hover:underline text-sm mr-4"><i class="fas fa-code mr-1"></i> View API Payload</a>
                <a href="/admin/whatsapp/templates/{{ $template->id }}/edit" class="text-amber-600 hover:underline text-sm"><i class="fas fa-edit mr-1"></i> Edit</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Send Test</h3>
            <form method="POST" action="/admin/whatsapp/templates/{{ $template->id }}/test" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 outline-none" placeholder="2348012345678">
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    <i class="fab fa-whatsapp mr-2"></i>Send Test
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-6 mt-6">
            <h3 class="font-semibold text-gray-900 mb-2">Template Info</h3>
            <div class="text-sm space-y-2 text-gray-600">
                <p><span class="text-gray-400">ID:</span> {{ $template->id }}</p>
                <p><span class="text-gray-400">Created:</span> {{ $template->created_at->format('M j, Y') }}</p>
                <p><span class="text-gray-400">Updated:</span> {{ $template->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
