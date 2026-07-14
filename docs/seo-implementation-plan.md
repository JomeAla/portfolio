# SEO Implementation Plan — JoAla Ventures (joala.com.ng)

**Date:** 2026-07-13
**Baseline SEO Score:** 44/100
**Target:** 75/100

---

## Priority Legend

| Priority | Timeline | Impact |
|----------|----------|--------|
| 🔴 HIGH | This week | Direct ranking & indexing impact |
| 🟡 MEDIUM | This month | Performance & user experience |
| 🟢 LOW | Next month | Polish & authority building |

---

## 🔴 HIGH PRIORITY — This Week

### 1. Fix sitemap.xml (Currently 404)

**Problem:** `/sitemap.xml` returns 404. Crawlers can't discover pages.

**Solution:**
```bash
composer require spatie/laravel-sitemap
php artisan vendor:publish --provider="Spatie\Sitemap\SitemapServiceProvider"
```

Create a route or command to generate sitemap with all public pages:
- Home, About, Services, Portfolio, Blog, Contact, Store
- Each blog post, portfolio item, and product page

Register route in `routes/web.php`:
```php
Route::get('/sitemap.xml', function () {
    return response()->view('sitemap')->header('Content-Type', 'application/xml');
});
```

Submit to Google Search Console once live.

### 2. Add JSON-LD Structured Data

Add to `<head>` on all pages via a Blade partial (`resources/views/partials/schema.blade.php`).

**Person schema** (About page):
```json
{
  "@context": "https://schema.org",
  "@type": "Person",
  "name": "Jome Alawuru",
  "jobTitle": "Software Developer",
  "url": "https://joala.com.ng",
  "sameAs": [
    "https://github.com/jomswoks",
    "https://twitter.com/jomswoks"
  ],
  "knowsAbout": ["Laravel", "React", "Vue", "WordPress", "Shopify"]
}
```

**LocalBusiness schema** (Contact/Footer):
```json
{
  "@context": "https://schema.org",
  "@type": "ProfessionalService",
  "name": "JoAla Ventures",
  "url": "https://joala.com.ng",
  "telephone": "+2349065257784",
  "email": "support@joala.com.ng",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "132 Ovwian Main Road, Opposite the Primary School",
    "addressLocality": "Ovwian",
    "addressRegion": "Delta State",
    "addressCountry": "NG"
  },
  "priceRange": "$$"
}
```

**Article schema** (Blog):
```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{ $post->title }}",
  "datePublished": "{{ $post->created_at }}",
  "author": {
    "@type": "Person",
    "name": "Jome Alawuru"
  }
}
```

**Product schema** (Store items), **BreadcrumbList** (all pages), **Review** (testimonials) — similar pattern.

**Implementation:** Create `resources/views/partials/schema.blade.php` and include it in the master layout before `</head>`.

### 3. Rewrite Meta Descriptions

Update per page. Target 150-160 characters with primary keyword + CTA.

| Page | Current | Proposed |
|------|---------|----------|
| Home | "Professional portfolio website" | "Nigeria-based web developer specializing in Laravel, React & WordPress. Custom web apps, Shopify stores & business automation. 50+ projects." |
| Services | Generic fallback | "Custom web development, WordPress, Shopify, mobile apps & business automation services in Nigeria. Laravel, React & Vue specialist. Get a quote." |
| Portfolio | Generic fallback | "Browse 50+ web development projects by JoAla Ventures. Laravel, React, WordPress & Shopify case studies from Nigeria's top freelance developer." |
| Blog | Generic fallback | "Web development tips, tutorials & insights for Nigerian businesses. Learn about Laravel, WordPress, Shopify & business automation." |
| About | Generic fallback | "Meet Jome Alawuru — founder of JoAla Ventures. Nigerian software developer with 5+ years building custom web & mobile applications." |
| Store | Generic fallback | "Premium Laravel templates, email sequences & WhatsApp marketing bundles. Digital products for Nigerian entrepreneurs & developers." |
| Contact | Generic fallback | "Get in touch with JoAla Ventures. Let's discuss your web development project. Based in Ovwian, Delta State, Nigeria." |

**Implementation:** Set `$pageMeta` variables in each page view (or via controller) and output in `<meta name="description">`. Create a Blade section or a shared meta partial.

### 4. Add OG / Twitter Card Tags

Add to master layout `resources/views/layouts/app.blade.php` in `<head>`:

```html
<meta property="og:title" content="@yield('og_title', $pageTitle ?? 'JoAla Ventures')" />
<meta property="og:description" content="@yield('og_description', $pageDescription ?? '')" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:image" content="@yield('og_image', asset('joala-og-image.png'))" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="JoAla Ventures" />
<meta property="og:locale" content="en_NG" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="@yield('og_title', $pageTitle ?? 'JoAla Ventures')" />
<meta name="twitter:description" content="@yield('og_description', $pageDescription ?? '')" />
<meta name="twitter:image" content="@yield('og_image', asset('joala-og-image.png'))" />
```

**Also need:** Generate a 1200x630px OG image (`public/joala-og-image.png`) with the logo and tagline.

### 5. Add Canonical Tags

In master layout `<head>`:
```html
<link rel="canonical" href="{{ url()->current() }}" />
```

### 15. Submit to Google Search Console

1. Go to https://search.google.com/search-console
2. Add property `joala.com.ng`
3. Verify via DNS TXT record, HTML file upload, or Google Analytics
4. Submit sitemap URL once generated
5. Monitor crawl errors and indexing status weekly

---

## 🟡 MEDIUM PRIORITY — This Month

### 6. Add HSTS Header

In `.htaccess` or Apache config:
```apache
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

Or via Laravel middleware in `app/Http/Middleware/SecurityHeaders.php`:
```php
public function handle($request, Closure $next)
{
    $response = $next($request);
    $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    return $response;
}
```

Register in `app/Http/Kernel.php` global middleware.

### 7. Add Last-Modified / ETag Headers

In `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

<IfModule mod_headers.c>
    Header append Cache-Control "public" for files
    FileETag MTime Size
</IfModule>
```

### 8. Add Google Maps Embed on Contact Page

In `resources/views/contact.blade.php` (or equivalent), add an iframe with the business location embedded map.

Also ensure LocalBusiness schema (from task 2) is included on this page.

### 9. Implement Lazy Loading on Images

Add `loading="lazy"` attribute to all `<img>` tags below the fold:

```html
<img src="..." alt="..." loading="lazy" />
```

Files to update:
- Blog listing images
- Portfolio project images
- Store product images
- Testimonial avatars (if images)

Hero images above the fold should NOT be lazy-loaded.

### 13. Add X-Frame-Options Header

In `.htaccess`:
```apache
Header always set X-Frame-Options "SAMEORIGIN"
```

Or add to the SecurityHeaders middleware created in task 6.

---

## 🟢 LOW PRIORITY — Next Month

### 10. Add FAQ Section to Services Page

On `/services`, add an accordion/FAQ per service category:

**Example for Web Development:**
- How long does it take to build a custom web app?
- What technologies do you use?
- How much does a web application cost in Nigeria?
- Do you provide post-launch support?
- Can you integrate with existing systems?

### 11. Add External Authority Links

In blog posts and services, link to:
- Laravel documentation (laravel.com)
- Official WordPress docs
- Shopify Partners
- React / Vue official sites
- Relevant .gov.ng or .edu.ng resources for local authority

### 12. Add Case Study Format Per Project

Update portfolio template (`resources/views/portfolio/show.blade.php`) to include:
- **Problem:** What the client needed
- **Solution:** Technical approach & stack
- **Results:** Measurable outcomes (e.g., "30% faster load time", "2x conversion rate")
- **Tech stack badges** (Laravel, React, etc.)
- **Client testimonial** inline
- **Live URL** if available

### 14. Optimize Image Alt Text

Review all images:
- Remove keyword-stuffing from long alt texts
- Keep descriptive, natural language (e.g., `"JoAla Ventures portfolio website project screenshot"`)
- Ensure every `<img>` has an `alt` attribute (even if empty for decorative)

---

## Implementation Order (Recommended Execution)

| Step | Task | Est. Time | Dependencies |
|------|------|-----------|-------------|
| 1 | Fix sitemap.xml | 30 min | None |
| 5 | Add canonical tags | 10 min | None |
| 3 | Rewrite meta descriptions | 45 min | None |
| 4 | Add OG / Twitter Card tags | 30 min | Need OG image asset |
| 2 | Add JSON-LD structured data | 1 hr | None |
| 15 | Submit to Google Search Console | 20 min | Sitemap (step 1) |
| 7 | Add caching headers | 20 min | .htaccess access |
| 6 | Add HSTS header | 15 min | .htaccess or middleware |
| 13 | Add X-Frame-Options header | 10 min | Same middleware as step 6 |
| 9 | Implement lazy loading | 30 min | Template access |
| 8 | Google Maps embed | 20 min | Contact page template |
| 10 | FAQ section | 1 hr | Services page |
| 12 | Case study format | 2 hr | Portfolio template |
| 11 | External links | 30 min | Content pages |
| 14 | Alt text cleanup | 30 min | Image audit |

---

## Success Metrics

| Metric | Current | 30-day Target | 90-day Target |
|--------|---------|---------------|---------------|
| Indexed Pages | ~8-10 | 25+ | 40+ |
| Sitemap Status | 404 | Valid | Auto-generated weekly |
| Pages with Schema | 0 | All pages | All pages |
| Core Web Vitals | Unknown | Pass all 3 | Pass all 3 |
| Organic Traffic (est.) | Unknown | +50% vs baseline | +200% vs baseline |
| Crawl Errors | Unknown | 0 | 0 |
