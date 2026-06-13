@extends('layouts.admin')

@section('title', 'Email Campaigns')

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Email Campaigns</h1>
        <p class="text-gray-600 mt-2">Create and manage broadcast email campaigns</p>
    </div>
    <a href="/admin/email/campaigns/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
        <i class="fas fa-plus mr-2"></i>New Campaign
    </a>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Total Campaigns</p>
        <p class="text-2xl font-bold text-slate-800">{{ count($campaigns) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Sent</p>
        <p class="text-2xl font-bold text-green-600">{{ $stats['sent'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Avg Open Rate</p>
        <p class="text-2xl font-bold text-blue-600">{{ $stats['open_rate'] }}%</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/50 p-6">
        <p class="text-sm text-gray-500">Avg Click Rate</p>
        <p class="text-2xl font-bold text-purple-600">{{ $stats['click_rate'] }}%</p>
    </div>
</div>

<!-- Campaigns List -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200/50 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Campaign</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Status</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Recipients</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Opens</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Clicks</th>
                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500">Date</th>
                <th class="px-6 py-4 text-right text-sm font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($campaigns as $campaign)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $campaign['name'] }}</p>
                        <p class="text-sm text-gray-500">{{ $campaign['subject'] }}</p>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                        {{ $campaign['status'] == 'sent' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $campaign['status'] == 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                        {{ $campaign['status'] == 'scheduled' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $campaign['status'] == 'sending' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        {{ ucfirst($campaign['status']) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-slate-600">{{ $campaign['total_recipients'] }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $campaign['total_opens'] }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $campaign['total_clicks'] }}</td>
                <td class="px-6 py-4 text-slate-500 text-sm">{{ $campaign['created_at'] }}</td>
                <td class="px-6 py-4 text-right">
                    @if($campaign['status'] == 'draft')
                    <a href="/admin/email/campaigns/{{ $campaign['id'] }}/edit" class="text-blue-600 hover:text-blue-800 mr-3">
                        <i class="fas fa-edit"></i>
                    </a>
                    @endif
                    <a href="/admin/email/campaigns/{{ $campaign['id'] }}" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    No campaigns yet. Create your first campaign!
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection