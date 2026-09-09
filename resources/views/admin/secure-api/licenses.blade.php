@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Secure API — Licenses</h1>
        <span class="text-sm text-slate-500">Issuer: JoAla Ventures</span>
    </div>

    @if (session('issued_key'))
        <div class="mb-6 bg-emerald-50 border border-emerald-300 rounded-lg p-4">
            <p class="text-sm font-semibold text-emerald-800">License created — copy the key now, it will not be shown again:</p>
            <code class="block mt-2 text-lg tracking-widest text-emerald-900">{{ session('issued_key') }}</code>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-300 rounded-lg p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Issue License</h2>
        <form method="POST" action="{{ route('admin.secure-api.licenses.create') }}" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Plan</label>
                <select name="plan" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="pro">Pro (₦15,000/mo)</option>
                    <option value="enterprise">Enterprise (₦50,000/mo)</option>
                    <option value="lifetime">Lifetime (₦75,000)</option>
                    <option value="agency">Agency (₦40,000/mo)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                <select name="type" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="subscription">Subscription</option>
                    <option value="lifetime">Lifetime</option>
                    <option value="agency">Agency</option>
                    <option value="enterprise">Enterprise</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Days / Activations</label>
                <div class="flex gap-2">
                    <input type="number" name="days" value="365" class="w-1/2 border border-slate-300 rounded-lg px-3 py-2" title="Days (0 = never)">
                    <input type="number" name="max_activations" value="1" class="w-1/2 border border-slate-300 rounded-lg px-3 py-2" title="Max activations (0 = unlimited)">
                </div>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Domain (optional)</label>
                <input type="text" name="domain" class="w-full border border-slate-300 rounded-lg px-3 py-2" placeholder="client-site.com">
            </div>
            <div class="col-span-2 md:col-span-4">
                <button class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2 rounded-lg">Generate License Key</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-4 py-3">Key</th>
                    <th class="text-left px-4 py-3">Type</th>
                    <th class="text-left px-4 py-3">Plan</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Domain</th>
                    <th class="text-left px-4 py-3">Expires</th>
                    <th class="text-left px-4 py-3">Activations</th>
                    <th class="text-left px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($items as $l)
                    <tr>
                        <td class="px-4 py-3"><code>{{ substr($l->key_suffix, 0, 4) }}-****-****-{{ substr($l->key_suffix, -4) }}</code></td>
                        <td class="px-4 py-3">{{ ucfirst($l->type) }}</td>
                        <td class="px-4 py-3">{{ ucfirst($l->plan) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $l->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($l->status === 'suspended' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ $l->status }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $l->domain ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $l->expires_at ? $l->expires_at->format('Y-m-d') : 'never' }}</td>
                        <td class="px-4 py-3">{{ $l->activations_count }} / {{ $l->max_activations ?: '∞' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.secure-api.licenses.action', $l->id) }}" class="inline-flex gap-2">
                                @csrf
                                @if ($l->status === 'active')
                                    <button name="action" value="suspend" class="text-amber-700 hover:underline">Suspend</button>
                                @else
                                    <button name="action" value="unsuspend" class="text-emerald-700 hover:underline">Unsuspend</button>
                                @endif
                                <button name="action" value="revoke" class="text-red-700 hover:underline" onclick="return confirm('Revoke this license?')">Revoke</button>
                                <button name="action" value="delete" class="text-red-700 hover:underline" onclick="return confirm('Delete this license permanently?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-slate-500">No licenses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $items->links() }}</div>
    </div>
</div>
@endsection
