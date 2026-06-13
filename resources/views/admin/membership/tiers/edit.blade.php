@extends('layouts.admin')

@section('title', 'Edit Membership Tier')

@section('content')
<div class="mb-6">
    <a href="/admin/membership/tiers" class="text-blue-600 hover:underline flex items-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Back to Tiers
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 p-8 max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Membership Tier</h1>
    
    <form method="POST" action="/admin/membership/tiers/{{ $tier['id'] }}">
        @csrf
        @method('PUT')
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Tier Name</label>
            <input type="text" name="name" required value="{{ $tier['name'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ $tier['description'] }}</textarea>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                <input type="number" name="price" step="0.01" min="0" value="{{ $tier['price'] }}" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Billing Period</label>
                <select name="billing_period" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="monthly" {{ $tier['billing_period'] == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ $tier['billing_period'] == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    <option value="one_time" {{ $tier['billing_period'] == 'one_time' ? 'selected' : '' }}>One Time</option>
                </select>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Features (one per line)</label>
            <textarea name="features" rows="5" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none">{{ implode("\n", json_decode($tier['features'] ?? '[]')) }}</textarea>
        </div>
        
        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" {{ $tier['is_active'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Active</span>
            </label>
        </div>
        
        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                Update Tier
            </button>
            <a href="/admin/membership/tiers" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection