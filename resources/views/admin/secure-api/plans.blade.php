@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Secure API — Plans</h1>

    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-300 rounded-lg p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left px-4 py-3">Plan</th>
                    <th class="text-left px-4 py-3">Price (₦)</th>
                    <th class="text-left px-4 py-3">Quota/mo</th>
                    <th class="text-left px-4 py-3">RPM</th>
                    <th class="text-left px-4 py-3">Service Accounts</th>
                    <th class="text-left px-4 py-3">Trial / Grace</th>
                    <th class="text-left px-4 py-3">Save</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($plans as $plan)
                    <tr>
                        <form method="POST" action="{{ route('admin.secure-api.plans.update', $plan->id) }}" class="contents">
                            @csrf
                            <td class="px-4 py-3">
                                <strong>{{ $plan->name }}</strong>
                                @if ($plan->is_default) <span class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">Default</span> @endif
                                <span class="ml-1 text-slate-400">{{ $plan->slug }}</span>
                            </td>
                            <td class="px-4 py-3"><input type="number" step="0.01" min="0" name="price_monthly" value="{{ $plan->price_monthly }}" class="w-24 border border-slate-300 rounded px-2 py-1"></td>
                            <td class="px-4 py-3"><input type="number" min="-1" name="quota_monthly" value="{{ $plan->quota_monthly }}" class="w-20 border border-slate-300 rounded px-2 py-1"></td>
                            <td class="px-4 py-3"><input type="number" min="1" name="rate_rpm" value="{{ $plan->rate_rpm }}" class="w-20 border border-slate-300 rounded px-2 py-1"></td>
                            <td class="px-4 py-3"><input type="number" min="-1" name="service_accounts" value="{{ $plan->service_accounts }}" class="w-20 border border-slate-300 rounded px-2 py-1"></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <input type="number" min="0" name="trial_days" value="{{ $plan->trial_days }}" class="w-14 border border-slate-300 rounded px-2 py-1">
                                    <input type="number" min="0" name="grace_days" value="{{ $plan->grace_days }}" class="w-14 border border-slate-300 rounded px-2 py-1">
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <button class="bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-3 py-1.5 rounded">Save</button>
                            </td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-sm text-slate-500 mt-4">
        The WP plugin (client mode) reads plan limits from the license plan returned by
        <code>POST /api/secure-api/activate</code>. Lifetime and Agency are store products here;
        Lifetime licenses never expire, Agency licenses carry seat limits.
    </p>
</div>
@endsection
