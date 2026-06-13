@extends('layouts.admin')

@section('title', 'Tweet Scheduler')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Tweet Scheduler</h1>
        <p class="text-slate-600 mt-2">Schedule and manage your tweets</p>
    </div>
    <a href="/admin/marketing/tweets/create" class="bg-sky-500 text-white px-4 py-2 rounded-lg hover:bg-sky-600">
        <i class="fab fa-twitter mr-2"></i>New Tweet
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Content</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Blog Post</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Scheduled</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($tweets as $tweet)
            <tr>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-800 max-w-md truncate">{{ $tweet->content }}</div>
                </td>
                <td class="px-6 py-4">
                    @if($tweet->blogPost)
                        <span class="text-sm text-slate-500">{{ $tweet->blogPost->title }}</span>
                    @else
                        <span class="text-sm text-slate-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded 
                        {{ $tweet->status === 'sent' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $tweet->status === 'scheduled' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $tweet->status === 'draft' ? 'bg-gray-100 text-gray-700' : '' }}
                        {{ $tweet->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($tweet->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-500">
                        @if($tweet->scheduled_send_time)
                            {{ $tweet->scheduled_send_time->format('M j, Y g:i A') }}
                        @else
                            -
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    @if($tweet->status !== 'sent')
                        <form method="POST" action="/admin/marketing/tweets/{{ $tweet->id }}/send" class="inline mr-2">
                            @csrf
                            <button type="submit" class="text-sky-600 hover:text-sky-800">Send Now</button>
                        </form>
                    @endif
                    <a href="/admin/marketing/tweets/{{ $tweet->id }}/edit" class="text-blue-600 hover:text-blue-800 mr-3">Edit</a>
                    <form method="POST" action="/admin/marketing/tweets/{{ $tweet->id }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this tweet?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $tweets->links() }}
</div>
@endsection