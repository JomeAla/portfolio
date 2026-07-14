<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>JoAla Ventures — Blog</title>
        <link>{{ url('/blog') }}</link>
        <description>Web development tips, tutorials & insights for Nigerian businesses. By Jome Alawuru.</description>
        <language>en</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ url('/rss.xml') }}" rel="self" type="application/rss+xml"/>
        @foreach($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ url('/blog/' . $post->slug) }}</link>
            <guid isPermaLink="true">{{ url('/blog/' . $post->slug) }}</guid>
            <description>{{ $post->excerpt ?? $post->title }}</description>
            <pubDate>{{ $post->published_at?->toRssString() }}</pubDate>
            <author>support@joala.com.ng (Jome Alawuru)</author>
            @if($post->featured_image)
            <enclosure url="{{ asset($post->featured_image) }}" type="image/jpeg"/>
            @endif
        </item>
        @endforeach
    </channel>
</rss>