@extends('layouts.admin')

@section('title', 'Affiliates')

@section('content')
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Affiliates</h1>
            <p class="text-slate-600 mt-2">Manage your affiliate partners</p>
        </div>
        <a href="/affiliate/register" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>New Affiliate Link
        </a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Affiliates</div>
        <div class="text-2xl font-bold text-slate-800">{{ count($affiliates) }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Active</div>
        <div class="text-2xl font-bold text-green-600">{{ collect($affiliates)->where('status', 'active')->count() }}</div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm text-gray-500">Total Earned</div>
        <div class="text-2xl font-bold text-emerald-600">&#8358;{{ number_format(array_sum(array_column($affiliates, 'total_earned'))) }}</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referral Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Earned</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($affiliates as $affiliate)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">{{ $affiliate['id'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $affiliate['name'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $affiliate['email'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <code class="bg-gray-100 px-2 py-1 rounded">{{ $affiliate['referral_code'] }}</code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $affiliate['status'] == 'active' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $affiliate['status'] == 'inactive' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $affiliate['status'] == 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($affiliate['status']) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">&#8358;{{ number_format($affiliate['total_earned']) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ $affiliate['created_at'] }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <form action="{{ route('admin.affiliates.delete', $affiliate['id']) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this affiliate?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No affiliates yet</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection