@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-800">Marketing Dashboard</h1>
    <p class="text-slate-600 mt-2">Overview of your marketing campaigns and performance</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Total Leads</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['total_leads'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Active Leads</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['active_leads'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-check text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Blog Posts</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['published_posts'] }}/{{ $stats['total_blog_posts'] }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-blog text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Landing Pages</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['landing_pages'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <i class="fas fa-landing-page text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Pending Tweets</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['pending_tweets'] }}</p>
            </div>
            <div class="w-12 h-12 bg-sky-100 rounded-full flex items-center justify-center">
                <i class="fab fa-twitter text-sky-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Sent Tweets</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['sent_tweets'] }}</p>
            </div>
            <div class="w-12 h-12 bg-sky-200 rounded-full flex items-center justify-center">
                <i class="fab fa-twitter text-sky-700 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Email Queued</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['email_queued'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                <i class="fas fa-envelope text-indigo-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Email Sent</p>
                <p class="text-3xl font-bold text-slate-800">{{ $stats['email_sent'] }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-200 rounded-full flex items-center justify-center">
                <i class="fas fa-paper-plane text-indigo-700 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Open Rate</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['open_rate'] }}%</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-envelope-open text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-500 text-sm">Click Rate</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['click_rate'] }}%</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-mouse-pointer text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Recent Leads</h2>
        @if($recent_leads->count() > 0)
            <div class="space-y-3">
                @foreach($recent_leads as $lead)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <div>
                            <p class="font-medium text-slate-800">{{ $lead->name ?? 'No name' }}</p>
                            <p class="text-sm text-slate-500">{{ $lead->email }}</p>
                        </div>
                        <span class="text-xs text-slate-400">{{ $lead->created_at ? $lead->created_at->format('M d, Y') : '' }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-500">No leads yet</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Recent Tweets</h2>
        @if($recent_tweets->count() > 0)
            <div class="space-y-3">
                @foreach($recent_tweets as $tweet)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                        <div>
                            <p class="text-sm text-slate-800 truncate w-64">{{ $tweet->content }}</p>
                            <span class="text-xs px-2 py-1 rounded 
                                {{ $tweet->status === 'sent' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $tweet->status === 'scheduled' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $tweet->status === 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                                {{ $tweet->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($tweet->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-500">No tweets yet</p>
        @endif
    </div>
</div>

<div class="mt-8">
    <h2 class="text-xl font-bold text-slate-800 mb-4">Quick Actions</h2>
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('admin.marketing.leads') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            <i class="fas fa-users mr-2"></i>Leads
        </a>
        <a href="{{ route('admin.marketing.tags') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
            <i class="fas fa-tags mr-2"></i>Tags
        </a>
        <a href="{{ route('admin.marketing.campaigns') }}" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700">
            <i class="fas fa-bullhorn mr-2"></i>Campaigns
        </a>
        <a href="{{ route('admin.marketing.automation') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
            <i class="fas fa-random mr-2"></i>Automation Rules
        </a>
        <a href="{{ route('admin.marketing.ab-tests') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
            <i class="fas fa-flask mr-2"></i>A/B Testing
        </a>
        <a href="{{ route('admin.marketing.webhooks') }}" class="bg-cyan-600 text-white px-4 py-2 rounded-lg hover:bg-cyan-700">
            <i class="fas fa-plug mr-2"></i>Webhooks
        </a>
        <a href="{{ route('admin.marketing.email-templates') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">
            <i class="fas fa-envelope mr-2"></i>Email Templates
        </a>
        <a href="{{ route('admin.marketing.email-builder') }}" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700">
            <i class="fas fa-paint-brush mr-2"></i>Email Builder
        </a>
        <a href="{{ route('admin.marketing.lead-scoring') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            <i class="fas fa-star mr-2"></i>Lead Scoring
        </a>
        <a href="{{ route('admin.marketing.segments') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-layer-group mr-2"></i>Segments
        </a>
        <a href="{{ route('admin.marketing.analytics') }}" class="bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700">
            <i class="fas fa-chart-pie mr-2"></i>Analytics
        </a>
        <a href="{{ route('admin.marketing.deals') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
            <i class="fas fa-handshake mr-2"></i>Deals Pipeline
        </a>
        <a href="{{ route('admin.marketing.tasks') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
            <i class="fas fa-tasks mr-2"></i>Tasks
        </a>
        <a href="{{ route('admin.marketing.notebooklm') }}" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-4 py-2 rounded-lg hover:from-purple-700 hover:to-pink-700">
            <i class="fas fa-magic mr-2"></i>AI Generator
        </a>
        <a href="/admin/run-migrations" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
            <i class="fas fa-database mr-2"></i>Run Migrations
        </a>
        <a href="{{ route('admin.marketing.landing-pages.create') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
            <i class="fas fa-plus mr-2"></i>New Landing Page
        </a>
        <a href="{{ route('admin.marketing.blog.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>New Blog Post
        </a>
        <a href="{{ route('admin.marketing.tweets.create') }}" class="bg-sky-500 text-white px-4 py-2 rounded-lg hover:bg-sky-600">
            <i class="fab fa-twitter mr-2"></i>Schedule Tweet
        </a>
        <a href="{{ route('admin.marketing.settings') }}" class="bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700">
            <i class="fas fa-cog mr-2"></i>Settings
        </a>
    </div>
</div>
@endsection