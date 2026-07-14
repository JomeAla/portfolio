@extends('layouts.app')

@section('title', 'Home')
@section('meta_description', 'Nigeria-based web developer specializing in Laravel, React & WordPress. Custom web apps, Shopify stores & business automation. 50+ projects delivered.')
@section('og_title', 'JoAla Ventures — Web Developer in Nigeria')
@section('og_description', 'Nigeria-based web developer specializing in Laravel, React & WordPress. Custom web apps, Shopify stores & business automation. 50+ projects delivered.')
@section('og_image', asset('joala-og-image.png'))

@section('content')
<!-- Hero Section -->
<section class="relative min-h-[90vh] flex items-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-20 w-72 h-72 bg-blue-500 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-purple-500 rounded-full blur-3xl"></div>
    </div>

    <!-- p5.js Canvas -->
    <div id="hero-canvas" class="absolute inset-0 z-0" style="pointer-events:none;"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm mb-6">
                    <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                    {{ $hero['hero']['badge'] ?? 'Available for projects' }}
                </div>
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white tracking-tight leading-tight">
                    {{ $hero['hero']['title'] ?? $settings['hero_title'] ?? 'Building Digital Solutions That Matter' }}
                </h1>
                <p class="text-xl text-slate-400 mt-6 max-w-xl">
                    {{ $hero['hero']['subtitle'] ?? $settings['hero_subtitle'] ?? 'I transform ideas into powerful web and mobile applications. Custom development tailored to your business needs.' }}
                </p>
                <div class="flex flex-wrap gap-4 mt-8">
                    <a href="{{ $hero['cta']['link'] ?? $settings['cta_link'] ?? route('brief.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-all hover:scale-105">
                        {{ $hero['cta']['text'] ?? $settings['cta_text'] ?? 'Start Your Project' }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-2 bg-slate-700/50 hover:bg-slate-700 text-white font-semibold px-6 py-3 rounded-xl transition-all border border-slate-600 hover:border-slate-500">
                        View Work
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="flex gap-8 mt-12 pt-8 border-t border-slate-700/50">
                    <div>
                        <p class="text-3xl font-bold text-white">{{ $hero['stats']['projects'] ?? '50+' }}</p>
                        <p class="text-slate-400 text-sm">{{ $hero['stats']['projects_label'] ?? 'Projects Completed' }}</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-white">{{ $hero['stats']['experience'] ?? '5+' }}</p>
                        <p class="text-slate-400 text-sm">{{ $hero['stats']['experience_label'] ?? 'Years Experience' }}</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-white">{{ $hero['stats']['satisfaction'] ?? '100%' }}</p>
                        <p class="text-slate-400 text-sm">{{ $hero['stats']['satisfaction_label'] ?? 'Client Satisfaction' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-slate-900">What I Do</h2>
            <p class="text-lg text-slate-600 mt-4 max-w-2xl mx-auto">Delivering high-quality custom applications tailored to your business needs.</p>
        </div>
        
        <div class="flex flex-wrap justify-center gap-8">
            @forelse($services as $index => $service)
            @php
                $colors = ['blue', 'purple', 'emerald', 'amber', 'rose', 'cyan', 'indigo', 'orange'];
                $color = $colors[$index % count($colors)];
                $iconClass = $service->icon ?: 'fas fa-code';
            @endphp
            <a href="{{ route('brief.create') }}" class="block p-8 rounded-2xl bg-slate-50 border-2 border-slate-100 hover:border-{{ $color }}-500 hover:shadow-lg transition-all cursor-pointer w-full md:w-[calc(33.333%-21px)] max-w-sm">
                <div class="w-14 h-14 bg-{{ $color }}-100 rounded-2xl flex items-center justify-center mb-6">
                    <i class="{{ $iconClass }} text-2xl text-{{ $color }}-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-3">{{ $service->title }}</h3>
                <p class="text-slate-600">{{ $service->description }}</p>
            </a>
            @empty
            <div class="text-center py-8">
                <p class="text-slate-500">No services available at the moment.</p>
            </div>
            @endforelse
        </div>
        
        <div class="text-center mt-12">
            <a href="{{ route('services') }}" class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:text-blue-700">
                View All Services <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Blog Posts -->
@if(isset($recentPosts) && $recentPosts->count() > 0)
<section class="py-8 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-4xl font-bold text-slate-900">Latest from Blog</h2>
                <p class="text-lg text-slate-600 mt-4">Tips, tutorials, and insights</p>
            </div>
            <a href="{{ route('blog') }}" class="hidden md:inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($recentPosts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition-all hover:-translate-y-1">
                @if($post->featured_image)
                <div class="aspect-video overflow-hidden">
                    <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                </div>
                @else
                <div class="aspect-video bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-blog text-4xl text-white/50"></i>
                </div>
                @endif
                <div class="p-6">
                    <p class="text-sm text-blue-600 font-medium mb-2">{{ $post->published_at->format('M d, Y') }}</p>
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $post->title }}</h3>
                    @if($post->excerpt)
                    <p class="text-slate-600 text-sm mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Featured Projects -->
@if($featuredProjects->count() > 0)
<section class="py-8 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-4xl font-bold text-slate-900">Featured Work</h2>
                <p class="text-lg text-slate-600 mt-4">Some of my recent projects</p>
            </div>
            <a href="{{ route('portfolio') }}" class="hidden md:inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($featuredProjects as $project)
            <a href="{{ route('portfolio.show', $project->slug) }}" class="group relative overflow-hidden rounded-2xl bg-slate-900">
                <div class="aspect-video relative overflow-hidden">
                    @if($project->thumbnail)
                    <img src="{{ asset($project->thumbnail) }}" alt="{{ $project->title }}" loading="lazy" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-110">
                    @else
                    <div class="w-full h-full bg-slate-800 flex items-center justify-center">
                        <i class="fas fa-image text-4xl text-slate-600"></i>
                    </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <span class="text-blue-400 text-sm font-medium">{{ $project->category ?? 'Web' }}</span>
                    <h3 class="text-2xl font-bold text-white mt-2">{{ $project->title }}</h3>
                    <p class="text-slate-300 mt-2 line-clamp-2">{{ $project->description }}</p>
                    <div class="flex items-center gap-4 mt-4">
                        @if($project->github_url)
                        <span class="text-slate-400 text-sm"><i class="fab fa-github mr-2"></i>View Code</span>
                        @endif
                        @if($project->live_url)
                        <span class="text-slate-400 text-sm"><i class="fas fa-external-link-alt mr-2"></i>Live Demo</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Featured Products (Store) -->
@if(isset($featuredProducts) && $featuredProducts->count() > 0)
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-4xl font-bold text-slate-900">Digital Products</h2>
                <p class="text-lg text-slate-600 mt-4">Premium templates, scripts, and more</p>
            </div>
            <a href="{{ route('store') }}" class="hidden md:inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium">
                View Store <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
            <a href="{{ route('store.show', $product->slug) }}" class="group bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl transition-all hover:-translate-y-1">
                <div class="aspect-video relative overflow-hidden">
                    @if($product->image)
                    <img src="{{ asset($product->image) }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    @else
                    <div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-box text-4xl text-white/50"></i>
                    </div>
                    @endif
                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-white/90 text-slate-700">
                            {{ ucfirst($product->type) }}
                        </span>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $product->title }}</h3>
                    <div class="flex items-center justify-between mt-3">
                        @if($product->isOnSale())
                        <div>
                            <span class="text-sm text-gray-400 line-through">₦{{ number_format($product->price) }}</span>
                            <span class="text-lg font-bold text-emerald-600">₦{{ number_format($product->sale_price) }}</span>
                        </div>
                        @else
                        <span class="text-lg font-bold text-slate-900">₦{{ number_format($product->price) }}</span>
                        @endif
                        <span class="text-sm text-blue-600 font-medium">View</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        
        <div class="text-center mt-8">
            <a href="{{ route('store') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                <i class="fas fa-shopping-bag"></i>
                Browse Store
            </a>
        </div>
    </div>
</section>
@endif

<!-- Testimonials -->
@if($testimonials->count() > 0)
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-slate-900">Client Testimonials</h2>
            <p class="text-lg text-slate-600 mt-4">What clients say about working with me</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($testimonials as $testimonial)
            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="flex items-center gap-1 text-yellow-400 mb-4">
                    @for($i = 0; $i < $testimonial->rating; $i++)
                    <i class="fas fa-star"></i>
                    @endfor
                </div>
                <p class="text-slate-700 mb-6">"{{ $testimonial->content }}"</p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center">
                        <span class="text-slate-600 font-semibold">{{ substr($testimonial->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900">{{ $testimonial->name }}</p>
                        @if($testimonial->company)
                        <p class="text-sm text-slate-500">{{ $testimonial->company }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="py-8 bg-gradient-to-br from-blue-600 to-blue-700">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-white">Ready to Start Your Project?</h2>
        <p class="text-xl text-blue-100 mt-4">Let's discuss your idea and bring it to life.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8">
            <a href="{{ route('brief.create') }}" class="bg-white text-blue-600 font-semibold px-8 py-4 rounded-xl hover:bg-blue-50 transition-colors">
                Start a Project
            </a>
            <a href="{{ route('contact') }}" class="bg-blue-500/20 text-white font-semibold px-8 py-4 rounded-xl border border-blue-400/30 hover:bg-blue-500/30 transition-colors">
                Get in Touch
            </a>
        </div>
    </div>
</section>

<!-- Newsletter -->
@include('front.newsletter')
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.9.0/p5.min.js"></script>
<script>
const heroSketch = (p) => {
    let particles = [];
    const numParticles = 80;
    const connectionDistance = 200;

    class Particle {
        constructor() {
            this.pos = p.createVector(p.random(p.width), p.random(p.height));
            this.vel = p5.Vector.random2D().mult(p.random(0.2, 0.8));
            this.size = p.random(3, 7);
            this.color = p.random() > 0.5 ? [59, 130, 246] : [168, 85, 247];
        }

        update() {
            this.pos.add(this.vel);
            if (this.pos.x < 0 || this.pos.x > p.width) this.vel.x *= -1;
            if (this.pos.y < 0 || this.pos.y > p.height) this.vel.y *= -1;
        }

        draw() {
            p.noStroke();
            p.fill(this.color[0], this.color[1], this.color[2], 150);
            p.circle(this.pos.x, this.pos.y, this.size);
        }
    }

    p.setup = () => {
        const container = document.getElementById('hero-canvas');
        const canvas = p.createCanvas(container.offsetWidth, container.offsetHeight);
        canvas.parent('hero-canvas');
        canvas.style('background', 'transparent');
        for (let i = 0; i < numParticles; i++) {
            particles.push(new Particle());
        }
    };

    p.draw = () => {
        p.clear();
        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();
            for (let j = i + 1; j < particles.length; j++) {
                const d = p5.Vector.dist(particles[i].pos, particles[j].pos);
                if (d < connectionDistance) {
                    const alpha = p.map(d, 0, connectionDistance, 80, 0);
                    p.stroke(100, 150, 255, alpha);
                    p.strokeWeight(1);
                    p.line(particles[i].pos.x, particles[i].pos.y, particles[j].pos.x, particles[j].pos.y);
                }
            }
        }
    };

    p.windowResized = () => {
        const container = document.getElementById('hero-canvas');
        p.resizeCanvas(container.offsetWidth, container.offsetHeight);
    };
};

new p5(heroSketch);
</script>
@endsection