@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description ?? strip_tags($post->excerpt ?? ''))
@section('og_title', $post->meta_title ?? $post->title)
@section('og_description', $post->meta_description ?? strip_tags($post->excerpt ?? ''))
@section('og_image', $post->featured_image ? asset($post->featured_image) : asset('joala-og-image.png'))
@section('og_type', 'article')

@section('meta')
    <meta property="article:published_time" content="{{ $post->published_at?->toIso8601String() }}" />
    <meta property="article:author" content="Jome Alawuru" />
@endsection

@section('content')
<article class="py-20 bg-slate-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('blog') }}" class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 mb-8">
            <i class="fas fa-arrow-left"></i> Back to Blog
        </a>

        @if($post->featured_image)
            <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-80 object-cover rounded-2xl mb-8">
        @endif

        <header class="mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-white">{{ $post->title }}</h1>
            <div class="flex items-center gap-4 text-slate-400 mt-4">
                <time>{{ $post->published_at->format('F j, Y') }}</time>
            </div>
        </header>

        <div class="blog-content prose prose-lg max-w-none" style="color: #e2e8f0;">
            {!! $post->body !!}
        </div>
        
        <style>
        .blog-content h1, .blog-content h2, .blog-content h3, .blog-content h4 {
            color: #ffffff !important;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 700;
        }
        .blog-content p {
            color: #e2e8f0 !important;
            margin-bottom: 1em;
            line-height: 1.8;
        }
        .blog-content ul, .blog-content ol {
            color: #e2e8f0 !important;
            margin-bottom: 1em;
            padding-left: 1.5em;
        }
        .blog-content li {
            margin-bottom: 0.5em;
        }
        .blog-content a {
            color: #60a5fa !important;
            text-decoration: underline;
        }
        .blog-content blockquote {
            border-left: 4px solid #4f46e5;
            padding-left: 1em;
            margin: 1em 0;
            font-style: italic;
            color: #94a3b8;
        }
        .blog-content strong {
            color: #ffffff !important;
        }
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1em 0;
        }
        .blog-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1em 0;
        }
        .blog-content th, .blog-content td {
            border: 1px solid #475569;
            padding: 0.5em;
            color: #e2e8f0;
        }
        .blog-content th {
            background: #1e293b;
        }
        </style>

        <div class="mt-8 p-4 bg-slate-800 rounded-xl">
            <p class="text-slate-400 mb-3">Share this article:</p>
            <div class="flex gap-3">
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(route('blog.show', $post->slug)) }}" target="_blank" class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('blog.show', $post->slug)) }}" target="_blank" class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fab fa-linkedin"></i> LinkedIn
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.show', $post->slug)) }}" target="_blank" class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fab fa-facebook"></i> Facebook
                </a>
                <a href="mailto:?subject={{ urlencode($post->title) }}&body=Check out this article: {{ urlencode(route('blog.show', $post->slug)) }}" class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-envelope"></i> Email
                </a>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-700">
            <a href="{{ route('blog') }}" class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300">
                <i class="fas fa-arrow-left"></i> Back to Blog
            </a>
        </div>
    </div>
</article>

@if($post->show_popup)
<div id="blogPopup" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-2xl max-w-lg mx-4 p-8 shadow-2xl relative text-slate-900">
        <button onclick="closeBlogPopup()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <h3 class="text-xl font-bold mb-4">{{ $post->popup_title ?? 'Wait!' }}</h3>
        <div class="mb-6">
            {!! $post->popup_html ?? '<p>Thanks for reading! Want to get updates?</p>' !!}
        </div>
        <form id="blogPopupForm" class="space-y-3">
            @csrf
            <input type="email" name="email" placeholder="Enter your email" required 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">
                Subscribe
            </button>
        </form>
        <button onclick="closeBlogPopup()" class="mt-3 w-full text-gray-500 text-sm hover:text-gray-700">
            No thanks
        </button>
    </div>
</div>
@endif

@section('scripts')
@if($post->show_popup)
<script>
const blogPopupDelay = {{ $post->popup_delay ?? 10 }} * 1000;
const blogPopupKey = 'blogPopup_{{ $post->id }}';
const blogFunnelId = {{ $post->funnel_id ?? 'null' }};

if (!localStorage.getItem(blogPopupKey)) {
    setTimeout(() => {
        document.getElementById('blogPopup').style.display = 'flex';
        localStorage.setItem(blogPopupKey, '1');
    }, blogPopupDelay);
}

function closeBlogPopup() {
    document.getElementById('blogPopup').style.display = 'none';
}

document.getElementById('blogPopupForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const email = formData.get('email');
    
    try {
        let url = '{{ route("newsletter.subscribe") }}';
        let body = Object.fromEntries(formData);
        
        if (blogFunnelId) {
            body.funnel_id = blogFunnelId;
        }
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });
        
        const data = await response.json();
        if (data.success) {
            alert(data.message || 'Thanks for subscribing!');
            closeBlogPopup();
        }
    } catch (error) {
        alert('Something went wrong. Please try again.');
    }
});
</script>
@endif
@endsection
@endsection