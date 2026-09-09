@extends('front.customer.layout')

@section('customer-content')
<h1 class="text-3xl font-bold text-slate-800 mb-2">My Licenses</h1>
<p class="text-slate-500 mb-8">WP Secure API Gateway license keys issued to <strong>{{ $customer['email'] ?? '' }}</strong>. The full key is sent by email at purchase — activate it in your WordPress admin under <em>Secure API → Licenses</em>.</p>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if(count($licenses ?? []) > 0)
    <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Key</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Plan</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Type</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Expires</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-slate-600">Activations</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($licenses as $license)
            <tr>
                <td class="px-6 py-4 text-slate-800"><code>{{ substr($license->key_suffix, 0, 4) }}-****-****-{{ substr($license->key_suffix, -4) }}</code></td>
                <td class="px-6 py-4 text-slate-800">{{ ucfirst($license->plan) }}</td>
                <td class="px-6 py-4 text-slate-600">{{ ucfirst($license->type) }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $license->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($license->status === 'suspended' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ $license->status }}</span>
                </td>
                <td class="px-6 py-4 text-slate-600">{{ $license->expires_at ? $license->expires_at->format('M d, Y') : 'Never' }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $license->activations_count }} / {{ $license->max_activations ?: '∞' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="p-10 text-center">
        <p class="text-slate-500 mb-4">No licenses yet.</p>
        <a href="/store" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2 rounded-lg">Browse the store</a>
    </div>
    @endif
</div>
@endsection
