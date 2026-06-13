@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Webhooks</h1>
            <p class="text-slate-600 mt-1">Configure webhooks to send data to external services</p>
        </div>
        <a href="{{ route('admin.marketing.webhooks.history') }}" class="bg-slate-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-slate-700">
            <i class="fas fa-history mr-1"></i> View History
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Create Webhook</h2>
                
                <form method="POST" action="{{ route('admin.marketing.webhooks.store') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                        <input type="text" name="name" required placeholder="e.g., CRM Notification" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Webhook URL</label>
                        <input type="url" name="url" required placeholder="https://your-server.com/webhook" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Events</label>
                        <div class="space-y-2">
                            @foreach(['lead_created' => 'Lead Created', 'lead_updated' => 'Lead Updated', 'lead_tagged' => 'Lead Tagged', 'email_sent' => 'Email Sent', 'email_opened' => 'Email Opened', 'email_clicked' => 'Email Clicked', 'order_created' => 'Order Created', 'order_completed' => 'Order Completed', 'invoice_created' => 'Invoice Created', 'invoice_paid' => 'Invoice Paid', 'subscription_created' => 'Subscription Created', 'subscription_renewed' => 'Subscription Renewed', 'subscription_cancelled' => 'Subscription Cancelled', 'cart_created' => 'Cart Created', 'cart_abandoned' => 'Cart Abandoned', 'checkout_started' => 'Checkout Started', 'checkout_completed' => 'Checkout Completed'] as $value => $label)
                            <label class="flex items-center">
                                <input type="checkbox" name="events[]" value="{{ $value }}" 
                                    class="rounded border-slate-300 text-indigo-600">
                                <span class="ml-2 text-sm text-slate-700">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Select events to trigger this webhook</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Secret (optional)</label>
                        <input type="text" name="secret" placeholder="Secret for signing requests" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" checked
                            class="rounded border-slate-300 text-indigo-600">
                        <label for="is_active" class="ml-2 text-sm text-slate-700">Active</label>
                    </div>

                    <button type="submit" 
                        class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Create Webhook
                    </button>
                </form>
            </div>
        </div>

        <!-- Webhooks List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Configured Webhooks ({{ $webhooks->total() }})</h3>
                </div>

                @if($webhooks->isEmpty())
                <div class="p-8 text-center">
                    <div class="text-slate-400 mb-2">
                        <i class="fas fa-bolt text-4xl"></i>
                    </div>
                    <p class="text-slate-600">No webhooks configured</p>
                </div>
                @else
                <div class="divide-y divide-slate-200">
                    @foreach($webhooks as $webhook)
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-medium text-slate-800">{{ $webhook->name }}</h4>
                                    <span class="px-2 py-0.5 text-xs rounded {{ $webhook->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="text-sm text-slate-600 mb-2">{{ $webhook->url }}</div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($webhook->events ?? [] as $event)
                                        <span class="px-2 py-0.5 text-xs bg-slate-100 text-slate-600 rounded">
                                            {{ $event }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="text-xs text-slate-400 mt-2">
                                    Created {{ $webhook->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.marketing.webhooks.test', $webhook) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                        Test
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.marketing.webhooks.toggle', $webhook) }}">
                                    @csrf
                                    <button type="submit" 
                                        class="px-3 py-1 text-xs rounded {{ $webhook->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        {{ $webhook->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.marketing.webhooks.destroy', $webhook) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-xs text-red-600 hover:text-red-800"
                                        onclick="return confirm('Delete this webhook?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="mt-4">
                {{ $webhooks->links() }}
            </div>
        </div>
    </div>
</div>
@endsection