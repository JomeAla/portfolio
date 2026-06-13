@extends('layouts.admin')

@section('title', 'Blog Posts')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Blog Posts</h1>
        <p class="text-slate-600 mt-2">Manage your blog content</p>
    </div>
    <a href="/admin/marketing/blog/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>New Post
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Slug</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Twitter</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach($posts as $post)
            <tr>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-slate-800">{{ $post->title }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-500">{{ $post->slug }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded {{ $post->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ $post->is_published ? 'Published' : 'Draft' }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded {{ $post->post_to_twitter ? 'bg-sky-100 text-sky-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $post->post_to_twitter ? 'Yes' : 'No' }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-slate-500">{{ $post->created_at->format('M j, Y') }}</div>
                </td>
                <td class="px-6 py-4 text-right">
                    <a href="/admin/marketing/blog/{{ $post->id }}/edit" class="text-blue-600 hover:text-blue-800 mr-3">Edit</a>
                    <form method="POST" action="/admin/marketing/blog/{{ $post->id }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this post?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $posts->links() }}
</div>
@endsection