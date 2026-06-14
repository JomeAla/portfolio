<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&family=Instrument+Serif&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Geist', sans-serif; background: #F7F6F3; color: #1a1a1a; }
        .bg-cream { background: #F7F6F3; }
        .text-cream { color: #F7F6F3; }
        .border-cream { border-color: #EAEAEA; }
        .serif { font-family: 'Instrument Serif', serif; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="bg-cream min-h-screen">
    @php $content = json_decode($page->custom_html, true); @endphp
    
    <!-- Hero Section -->
    <section class="py-20 md:py-32 animate-fade">
        <div class="max-w-5xl mx-auto px-6">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-[#1a1a1a]/5 rounded-full mb-6">
                        <i class="ph-fill ph-gift text-sm"></i>
                        <span class="text-xs font-medium uppercase tracking-wider text-[#1a1a1a]/60">Free Download</span>
                    </span>
                    <h1 class="serif text-4xl md:text-6xl font-normal mb-6 leading-[1.1]">
                        {{ $content['headline'] ?? 'Free Resource' }}
                    </h1>
                    <p class="text-lg text-[#1a1a1a]/60 max-w-md mb-8">
                        {{ $content['subheadline'] ?? '' }}
                    </p>
                    <div class="flex items-center gap-4 text-sm text-[#1a1a1a]/40">
                        <span class="flex items-center gap-1"><i class="ph-fill ph-check"></i> Instant access</span>
                        <span class="flex items-center gap-1"><i class="ph-fill ph-check"></i> No credit card</span>
                    </div>
                </div>
                
                <!-- Right - Form Card -->
                <div class="bg-white border border-[#EAEAEA] rounded-2xl p-8 shadow-[0_2px_20px_rgba(0,0,0,0.03)]">
                    <form id="leadForm" class="space-y-4">
                        @csrf
                        <input type="hidden" name="landing_page_id" value="{{ $page->id }}">
                        @if(session('utm_source'))<input type="hidden" name="utm_source" value="{{ session('utm_source') }}">@endif
                        @if(session('utm_medium'))<input type="hidden" name="utm_medium" value="{{ session('utm_medium') }}">@endif
                        @if(session('utm_campaign'))<input type="hidden" name="utm_campaign" value="{{ session('utm_campaign') }}">@endif
                        @if(session('utm_term'))<input type="hidden" name="utm_term" value="{{ session('utm_term') }}">@endif
                        @if(session('utm_content'))<input type="hidden" name="utm_content" value="{{ session('utm_content') }}">@endif
                        @if(session('referrer_url'))<input type="hidden" name="referrer_url" value="{{ session('referrer_url') }}">@endif
                        <div>
                            <label class="block text-xs font-medium uppercase tracking-wider text-[#1a1a1a]/40 mb-2">Your name</label>
                            <input type="text" name="name" placeholder="John Doe" required 
                                class="w-full px-4 py-3 bg-[#F7F6F3] border border-[#EAEAEA] rounded-lg text-[#1a1a1a] placeholder-[#1a1a1a]/30 focus:ring-1 focus:ring-[#1a1a1a] focus:border-[#1a1a1a] transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-medium uppercase tracking-wider text-[#1a1a1a]/40 mb-2">Your email</label>
                            <input type="email" name="email" placeholder="name@example.com" required 
                                class="w-full px-4 py-3 bg-[#F7F6F3] border border-[#EAEAEA] rounded-lg text-[#1a1a1a] placeholder-[#1a1a1a]/30 focus:ring-1 focus:ring-[#1a1a1a] focus:border-[#1a1a1a] transition-all">
                        </div>
                        <button type="submit" class="w-full bg-[#1a1a1a] hover:bg-[#333] text-white font-medium px-6 py-4 rounded-lg transition-all flex items-center justify-center gap-2">
                            <i class="ph-fill ph-download-simple text-lg"></i>
                            {{ $content['cta'] ?? 'Download Now' }}
                        </button>
                        <p class="text-xs text-center text-[#1a1a1a]/40">
                            <i class="ph-fill ph-lock-key"></i> Your data is safe. No spam.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- What's Inside Section -->
    @if(isset($content['items']) && is_array($content['items']))
    <section class="py-16 bg-white border-y border-[#EAEAEA]">
        <div class="max-w-5xl mx-auto px-6">
            <h2 class="serif text-2xl md:text-3xl mb-10">What's inside</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($content['items'] as $index => $item)
                <div class="flex items-start gap-3 p-4 bg-[#F7F6F3] rounded-lg">
                    <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-[#1a1a1a] text-white text-xs rounded">{{ $index + 1 }}</span>
                    <span class="text-sm text-[#1a1a1a]/80">{{ $item }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Social Proof / CTA Reminder -->
    <section class="py-20">
        <div class="max-w-2xl mx-auto px-6 text-center">
            <p class="text-lg text-[#1a1a1a]/60 italic serif">
                "Everything you need to launch your WordPress site with confidence."
            </p>
            <p class="text-sm text-[#1a1a1a]/40 mt-4">— Joala Ventures</p>
        </div>
    </section>

    @if(session('success'))
    <div class="fixed bottom-6 right-6 bg-[#1a1a1a] text-white px-6 py-3 rounded-lg flex items-center gap-2">
        <i class="ph-fill ph-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    
    <script>
        document.getElementById('leadForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Get funnel ID from URL if present
            const urlParams = new URLSearchParams(window.location.search);
            const funnelId = urlParams.get('funnel');
            
            const submitUrl = '{{ request()->url() }}/submit';
            const baseUrl = '{{ request()->root() }}';
            const slug = '{{ $slug ?? "wordpress-starter-kit" }}';
            const downloadUrl = funnelId 
                ? `${baseUrl}/download/${slug}?funnel=${funnelId}`
                : `${baseUrl}/download/${slug}`;
            
            try {
                const response = await fetch(submitUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                const data = await response.json();
                if (data.success) {
                    // Redirect to download page with funnel ID
                    window.location.href = downloadUrl;
                }
            } catch (error) {
                alert('Something went wrong. Please try again.');
            }
        });
    </script>
</body>
</html>