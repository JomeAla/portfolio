@extends('layouts.admin')

@section('title', 'Add Coupon')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Add New Coupon</h1>
    <p class="text-gray-600 mt-2">Create a discount coupon code</p>
</div>

<form method="POST" action="/admin/coupons" class="space-y-6">
    @csrf
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/50">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Coupon Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code *</label>
                <input type="text" name="code" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none uppercase"
                    placeholder="e.g., SUMMER20">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text" name="description" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Optional description">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                <select name="discount_type" required class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (₦)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                <input type="number" name="discount_value" step="0.01" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="e.g., 10">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Order (₦)</label>
                <input type="number" name="min_order_amount" step="0.01" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Leave empty for no minimum">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount (₦)</label>
                <input type="number" name="max_discount" step="0.01" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="For percentage discounts">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit</label>
                <input type="number" name="usage_limit" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none"
                    placeholder="Leave empty for unlimited">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                <input type="date" name="valid_until" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
        </div>

        <div class="mt-6">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" checked
                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-200">
                <span class="text-sm font-medium text-gray-700">Active</span>
            </label>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
            Create Coupon
        </button>
        <a href="/admin/coupons" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
            Cancel
        </a>
    </div>
</form>
@endsection
