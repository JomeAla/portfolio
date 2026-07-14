@php
    $siteName = $settings['site_name'] ?? 'JoAla Ventures';
    $siteDescription = $settings['site_description'] ?? 'Professional developer specializing in custom web and mobile applications.';
    $currentUrl = url()->current();
@endphp
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "Person",
            "name": "Jome Alawuru",
            "jobTitle": "Software Developer",
            "url": "{{ url('/') }}",
            "knowsAbout": ["Laravel", "React", "Vue", "WordPress", "Shopify", "Mobile Development", "API Development"],
            "worksFor": {
                "@type": "ProfessionalService",
                "name": "{{ $siteName }}"
            }
        },
        {
            "@type": "ProfessionalService",
            "@id": "{{ url('/') }}#organization",
            "name": "{{ $siteName }}",
            "description": "{{ $siteDescription }}",
            "url": "{{ url('/') }}",
            "telephone": "+2349065257784",
            "email": "support@joala.com.ng",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "132 Ovwian Main Road, Opposite the Primary School",
                "addressLocality": "Ovwian",
                "addressRegion": "Delta State",
                "addressCountry": "NG"
            },
            "priceRange": "$$",
            "image": "{{ asset('joala-logo.png') }}",
            "sameAs": [
                "https://github.com/jomswoks",
                "https://twitter.com/jomswoks"
            ]
        },
        {
            "@type": "BreadcrumbList",
            "@id": "{{ $currentUrl }}#breadcrumb",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "Home",
                    "item": "{{ url('/') }}"
                }
                @if(request()->route() && request()->route()->getName() !== 'home')
                    @php
                        $segments = request()->segments();
                        $path = '';
                    @endphp
                    @foreach($segments as $index => $segment)
                        @php $path .= '/' . $segment; @endphp
                        ,{
                            "@type": "ListItem",
                            "position": {{ $index + 2 }},
                            "name": "{{ ucwords(str_replace('-', ' ', $segment)) }}",
                            "item": "{{ url($path) }}"
                        }
                    @endforeach
                @endif
            ]
        }
    ]
}
</script>
@if(isset($post) && $post)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $post->title }}",
    "description": "{{ $post->meta_description ?? $post->excerpt ?? '' }}",
    "datePublished": "{{ $post->published_at?->toIso8601String() }}",
    "dateModified": "{{ $post->updated_at?->toIso8601String() }}",
    "author": {
        "@type": "Person",
        "name": "Jome Alawuru"
    },
    "image": "{{ $post->featured_image ? asset($post->featured_image) : asset('joala-og-image.png') }}",
    "publisher": {
        "@type": "Organization",
        "name": "{{ $siteName }}"
    }
}
</script>
@endif
@if(isset($product) && $product)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ $product->title }}",
    "description": "{{ $product->description ?? '' }}",
    "image": "{{ $product->image ? asset($product->image) : asset('joala-og-image.png') }}",
    "offers": {
        "@type": "Offer",
        "price": "{{ $product->isOnSale() ? $product->sale_price : $product->price }}",
        "priceCurrency": "NGN",
        "availability": "https://schema.org/InStock",
        "url": "{{ $currentUrl }}"
    }
}
</script>
@endif
@if(isset($testimonials) && $testimonials->count() > 0)
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ $siteName }} Services",
    "review": [
        @foreach($testimonials as $index => $testimonial)
        {
            "@type": "Review",
            "reviewRating": {
                "@type": "Rating",
                "ratingValue": "{{ $testimonial->rating ?? 5 }}",
                "bestRating": "5"
            },
            "author": {
                "@type": "Person",
                "name": "{{ $testimonial->name }}"
            },
            "reviewBody": "{{ $testimonial->content }}"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "{{ $siteName }}",
    "url": "{{ url('/') }}",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ url('/search') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>