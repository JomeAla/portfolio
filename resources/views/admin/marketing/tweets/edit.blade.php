@extends('layouts.admin')

@section('title', 'Edit Tweet')

@section('content')
<form method="POST" action="/admin/marketing/tweets/{{ $tweet->id }}">
    @csrf
    @method('PUT')
    <div class="mb-6">
        <a href="/admin/marketing/tweets" class="text-blue-600 hover:text-blue-800">&larr; Back to Tweets</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Edit Tweet</h2>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Tweet Content</label>
            <textarea name="content" id="tweetContent" rows="4" maxlength="280" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">{{ $tweet->content }}</textarea>
            <div class="text-right text-sm text-slate-500 mt-1">
                <span id="charCount">{{ strlen($tweet->content) }}</span>/280
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Link to Blog Post</label>
            <select name="blog_post_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                <option value="">None</option>
                @foreach(\App\Models\BlogPost::where('is_published', true)->get() as $post)
                    <option value="{{ $post->id }}" {{ $tweet->blog_post_id == $post->id ? 'selected' : '' }}>{{ $post->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Schedule</label>
            <input type="datetime-local" name="scheduled_send_time" value="{{ $tweet->scheduled_send_time ? $tweet->scheduled_send_time->format('Y-m-d\TH:i') : '' }}" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
        </div>

        <button type="submit" class="bg-sky-500 text-white px-6 py-2 rounded-lg hover:bg-sky-600 font-medium">
            Update Tweet
        </button>
    </div>
</form>

@section('scripts')
<script>
    const textarea = document.getElementById('tweetContent');
    const charCount = document.getElementById('charCount');
    textarea.addEventListener('input', () => {
        charCount.textContent = textarea.value.length;
    });
</script>
@endsection
@endsection