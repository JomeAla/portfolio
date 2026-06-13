@extends('layouts.admin')

@section('title', 'New Tweet')

@section('content')
<form method="POST" action="/admin/marketing/tweets">
    @csrf
    <div class="mb-6">
        <a href="/admin/marketing/tweets" class="text-blue-600 hover:text-blue-800">&larr; Back to Tweets</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Compose Tweet</h2>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Tweet Content</label>
            <textarea name="content" id="tweetContent" rows="4" maxlength="280" required class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent" placeholder="What's happening?"></textarea>
            <div class="text-right text-sm text-slate-500 mt-1">
                <span id="charCount">0</span>/280
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Link to Blog Post (Optional)</label>
            <select name="blog_post_id" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                <option value="">None</option>
                @foreach($posts as $post)
                    <option value="{{ $post->id }}">{{ $post->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Schedule (Optional)</label>
            <input type="datetime-local" name="scheduled_send_time" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-500 focus:border-transparent">
            <p class="text-sm text-slate-500 mt-1">Leave empty to save as draft</p>
        </div>

        <button type="submit" class="bg-sky-500 text-white px-6 py-2 rounded-lg hover:bg-sky-600 font-medium">
            Queue Tweet
        </button>
    </div>
</form>

@section('scripts')
<script>
    const textarea = document.getElementById('tweetContent');
    const charCount = document.getElementById('charCount');
    textarea.addEventListener('input', () => {
        charCount.textContent = textarea.value.length;
        if (textarea.value.length > 280) {
            charCount.classList.add('text-red-600');
        } else {
            charCount.classList.remove('text-red-600');
        }
    });
</script>
@endsection
@endsection