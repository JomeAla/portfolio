@extends('layouts.admin')

@section('title', 'Coupons')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Coupon Codes</h1>
        <p class="text-gray-600 mt-2">Manage discount coupons</p>
    </div>
    <a href="/admin/coupons/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors flex items-center gap-2">
        <i class="fas fa-plus"></i>
        Add Coupon
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Code</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Discount</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Usage</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Valid Until</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($coupons as $coupon)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <span class="font-mono font-bold text-slate-900">{{ $coupon->code }}</span>
                </td>
                <td class="px-6 py-4">
                    @if($coupon->discount_type === 'percentage')
                    <span class="text-emerald-600 font-semibold">{{ $coupon->discount_value }}%</span>
                    @else
                    <span class="text-emerald-600 font-semibold">₦{{ number_format($coupon->discount_value, 2) }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $coupon->used_count }} @if($coupon->usage_limit)/ {{ $coupon->usage_limit }}@endif
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $coupon->valid_until ? $coupon->valid_until->format('M d, Y') : 'No expiry' }}
                </td>
                <td class="px-6 py-4">
                    @if($coupon->is_active)
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">Active</span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">Inactive</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <a href="/admin/coupons/{{ $coupon->id }}/edit" class="text-gray-400 hover:text-blue-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="/admin/coupons/{{ $coupon->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    No coupons yet. <a href="/admin/coupons/create" class="text-blue-600 hover:underline">Create your first coupon</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $coupons->links() }}
</div>
@endsection
