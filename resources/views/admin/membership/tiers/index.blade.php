@extends('layouts.admin')

@section('title', 'Membership Tiers')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Membership Tiers</h1>
        <p class="text-gray-600 mt-2">Manage subscription plans and access levels</p>
    </div>
    <a href="/admin/membership/tiers/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>Add Tier
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @forelse($tiers as $tier)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">{{ $tier->name }}</h3>
                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $tier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $tier->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            
            <p class="text-gray-600 text-sm mb-4">{{ $tier->description ?? 'No description' }}</p>
            
            <div class="mb-4">
                <span class="text-3xl font-bold text-blue-600">
                    @if($tier->price == 0)
                        Free
                    @else
                        ₦{{ number_format($tier->price, 2) }}
                        <span class="text-sm font-normal text-gray-500">/{{ $tier->billing_period }}</span>
                    @endif
                </span>
            </div>
            
            @if($tier->features)
            <div class="mb-6">
                <p class="text-sm font-medium text-gray-700 mb-2">Features:</p>
                <ul class="space-y-1">
                    @foreach(json_decode($tier->features) as $feature)
                    <li class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="flex gap-2 pt-4 border-t border-gray-100">
                <a href="/admin/membership/tiers/{{ $tier->id }}/edit" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form method="POST" action="/admin/membership/tiers/{{ $tier->id }}" class="inline" onsubmit="return confirm('Delete this tier?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
            <span class="text-sm text-gray-500">{{ $tier->members_count ?? 0 }} members</span>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-12">
        <p class="text-gray-500">No membership tiers yet. Create your first tier!</p>
    </div>
    @endforelse
</div>
@endsection