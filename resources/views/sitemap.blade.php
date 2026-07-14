<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <priority>1.0</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc>{{ url('/services') }}</loc>
        <priority>0.9</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc>{{ url('/portfolio') }}</loc>
        <priority>0.8</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc>{{ url('/blog') }}</loc>
        <priority>0.8</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc>{{ url('/store') }}</loc>
        <priority>0.7</priority>
        <changefreq>weekly</changefreq>
    </url>
    <url>
        <loc>{{ url('/about') }}</loc>
        <priority>0.6</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc>{{ url('/contact') }}</loc>
        <priority>0.6</priority>
        <changefreq>monthly</changefreq>
    </url>
    <url>
        <loc>{{ url('/terms') }}</loc>
        <priority>0.3</priority>
        <changefreq>yearly</changefreq>
    </url>
    <url>
        <loc>{{ url('/privacy') }}</loc>
        <priority>0.3</priority>
        <changefreq>yearly</changefreq>
    </url>
    <url>
        <loc>{{ url('/refund') }}</loc>
        <priority>0.3</priority>
        <changefreq>yearly</changefreq>
    </url>
    @if(isset($posts) && $posts->count())
        @foreach($posts as $post)
    <url>
        <loc>{{ url('/blog/' . $post->slug) }}</loc>
        <priority>0.7</priority>
        <changefreq>monthly</changefreq>
        @if($post->updated_at)<lastmod>{{ $post->updated_at->toW3cString() }}</lastmod>@endif
    </url>
        @endforeach
    @endif
    @if(isset($projects) && $projects->count())
        @foreach($projects as $project)
    <url>
        <loc>{{ url('/portfolio/' . $project->slug) }}</loc>
        <priority>0.6</priority>
        <changefreq>monthly</changefreq>
    </url>
        @endforeach
    @endif
    @if(isset($products) && $products->count())
        @foreach($products as $product)
    <url>
        <loc>{{ url('/store/' . $product->slug) }}</loc>
        <priority>0.6</priority>
        <changefreq>weekly</changefreq>
    </url>
        @endforeach
    @endif
</urlset>