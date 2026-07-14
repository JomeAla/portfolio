@extends('layouts.app')

@section('title', 'Blog')
@section('meta_description', 'Web development tips, tutorials & insights for Nigerian businesses. Learn about Laravel, WordPress, Shopify & business automation from JoAla Ventures.')
@section('og_title', 'Blog — Web Development Tips & Tutorials')
@section('og_description', 'Web development tips, tutorials & insights for Nigerian businesses. Learn about Laravel, WordPress, Shopify & business automation.')

@section('content')
<section class="py-20 bg-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-white">Blog</h1>
            <p class="text-xl text-slate-400 mt-4">Insights and thoughts on development</p>
        </div>

        <form action="{{ route('blog') }}" method="GET" class="mb-8">
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..." 
                    class="flex-1 px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    Search
                </button>
            </div>
            @if(request('search'))
            <a href="{{ route('blog') }}" class="inline-block mt-2 text-sm text-blue-400 hover:text-blue-300">&larr; Clear search</a>
            @endif
        </form>

        @if($posts->isEmpty())
            <div class="text-center py-20">
                <p class="text-slate-400 text-lg">No blog posts yet.</p>
            </div>
        @else
            <div class="space-y-8">
                @foreach($posts as $post)
                    <article class="bg-slate-800/50 rounded-2xl overflow-hidden border border-slate-700/50 hover:border-slate-600/50 transition-all hover:shadow-xl hover:shadow-blue-500/10">
                        @if($post->featured_image)
                            <a href="{{ route('blog.show', $post->slug) }}">
                                <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" loading="lazy" class="w-full h-64 object-cover">
                            </a>
                        @endif
                        <div class="p-8">
                            <div class="flex items-center gap-4 text-sm text-slate-400 mb-4">
                                <time>{{ $post->published_at->format('F j, Y') }}</time>
                            </div>
                            <a href="{{ route('blog.show', $post->slug) }}">
                                <h2 class="text-2xl font-bold text-white hover:text-blue-400 transition-colors">{{ $post->title }}</h2>
                            </a>
                            @if($post->excerpt)
                                <p class="text-slate-400 mt-3">{{ $post->excerpt }}</p>
                            @endif
                            <a href="{{ route('blog.show', $post->slug) }}" class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-300 mt-4">
                                Read more <i class="fas fa-arrow-right text-sm"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</section>
@endsection