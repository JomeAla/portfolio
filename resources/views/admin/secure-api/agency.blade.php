@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Secure API — Agency Licenses</h1>

    @if (session('issued_key'))
        <div class="mb-6 bg-emerald-50 border border-emerald-300 rounded-lg p-4">
            <p class="text-sm font-semibold text-emerald-800">Agency license created — copy the key now:</p>
            <code class="block mt-2 text-lg tracking-widest text-emerald-900">{{ session('issued_key') }}</code>
            @if (session('client_ids'))
                <p class="text-sm text-emerald-800 mt-2">{{ count(session('client_ids')) }} client license(s) created (delivered via email/manual keys).</p>
            @endif
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-300 rounded-lg p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Create Agency License</h2>
        <form method="POST" action="{{ route('admin.secure-api.agency.create') }}" class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Agency email</label>
                <input type="email" name="email" required class="w-full border border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Plan</label>
                <select name="plan" class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="pro">Pro (₦40,000/mo)</option>
                    <option value="enterprise">Enterprise (₦50,000/mo)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Seats</label>
                <input type="number" name="seats" value="10" min="1" max="500" class="w-full border border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Clients now (bulk)</label>
                <input type="number" name="clients" value="0" min="0" max="100" class="w-full border border-slate-300 rounded-lg px-3 py-2">
            </div>
            <div class="flex items-end">
                <button class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2 rounded-lg">Create Agency</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-4 py-3">ID</th>
                    <th class="text-left px-4 py-3">Agency</th>
                    <th class="text-left px-4 py-3">Plan</th>
                    <th class="text-left px-4 py-3">Seats</th>
                    <th class="text-left px-4 py-3">Used</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Clients</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($agencies as $a)
                    <tr>
                        <td class="px-4 py-3">#{{ $a->id }}</td>
                        <td class="px-4 py-3">{{ $a->meta['email'] ?? '—' }}</td>
                        <td class="px-4 py-3">{{ ucfirst($a->plan) }}</td>
                        <td class="px-4 py-3">{{ $a->max_activations }}</td>
                        <td class="px-4 py-3">{{ $a->agencyClients()->where('status', '!=', 'revoked')->count() }}</td>
                        <td class="px-4 py-3">{{ $a->status }}</td>
                        <td class="px-4 py-3">
                            <details>
                                <summary class="cursor-pointer text-blue-700">view ({{ $a->agencyClients()->count() }})</summary>
                                <div class="mt-2 space-y-2">
                                    @foreach ($a->agencyClients()->with('license')->get() as $c)
                                        <div class="flex items-center gap-3">
                                            <code>#{{ $c->license_id }} …{{ $c->license?->key_suffix }}</code>
                                            <form method="POST" action="{{ route('admin.secure-api.agency.action') }}" class="inline-flex gap-2">
                                                @csrf
                                                <input type="hidden" name="license_id" value="{{ $c->license_id }}">
                                                <input type="hidden" name="action" value="assign">
                                                <input type="email" name="email" value="{{ $c->email }}" placeholder="client@email.com" class="border border-slate-300 rounded px-2 py-1 text-xs">
                                                <button class="text-xs text-blue-700 hover:underline">Set</button>
                                            </form>
                                            @if ($c->status !== 'revoked')
                                                <form method="POST" action="{{ route('admin.secure-api.agency.action') }}">
                                                    @csrf
                                                    <input type="hidden" name="license_id" value="{{ $c->license_id }}">
                                                    <input type="hidden" name="action" value="revoke">
                                                    <button class="text-xs text-red-700 hover:underline" onclick="return confirm('Revoke this client?')">Revoke</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-slate-500">No agency licenses yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
