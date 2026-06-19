@php($settings = $settings ?? [])
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Home') | Joala Ventures</title>
    <meta name="description" content="{{ $settings['site_description'] ?? 'Professional portfolio website' }}">
    @if(!empty($settings['favicon']))
    <link rel="icon" type="image/png" href="/storage/{{ $settings['favicon'] }}?v=2">
    <link rel="shortcut icon" type="image/png" href="/storage/{{ $settings['favicon'] }}?v=2">
    <link rel="apple-touch-icon" href="/storage/{{ $settings['favicon'] }}">
    @else
    <link rel="icon" type="image/png" href="/favicon.png?v=2">
    <link rel="shortcut icon" type="image/png" href="/favicon.png?v=2">
    <link rel="apple-touch-icon" href="/favicon.png">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Geist', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: '{{ $settings['primary_color'] ?? '#0f172a' }}',
                        accent: '{{ $settings['accent_color'] ?? '#3b82f6' }}',
                    }
                }
            }
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="/joala-logo.png" alt="Joala Ventures" class="h-16 md:h-20">
                </a>
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Home</a>
                    <a href="{{ route('portfolio') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Portfolio</a>
                    <a href="{{ route('services') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Services</a>
                    <a href="{{ route('blog') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Blog</a>
                    <a href="{{ route('store') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Store</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">About</a>
                    <a href="{{ route('contact') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Contact</a>
                    <a href="{{ route('brief.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">Start a Project</a>
                    @if(session()->has('customer_id'))
                        <a href="/customer/notifications" class="relative text-slate-600 hover:text-slate-900 transition-colors">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notif-badge" style="display:none" class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center leading-none">0</span>
                        </a>
                        <a href="/customer/dashboard" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">My Account</a>
                    @else
                        <a href="/customer/login" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">My Account</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="pt-16">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white py-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <h3 class="text-xl font-bold mb-4">{{ $settings['site_name'] ?? 'JoAla' }}</h3>
                    <p class="text-slate-400 max-w-md">{{ $settings['site_description'] ?? 'Professional developer specializing in custom web and mobile applications.' }}</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="{{ route('portfolio') }}" class="hover:text-white transition-colors">Portfolio</a></li>
                        <li><a href="{{ route('services') }}" class="hover:text-white transition-colors">Services</a></li>
                        <li><a href="{{ route('blog') }}" class="hover:text-white transition-colors">Blog</a></li>
                        <li><a href="{{ route('store') }}" class="hover:text-white transition-colors">Store</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">About</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">Terms</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('refund') }}" class="hover:text-white transition-colors">Refund Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 text-slate-400">
                        <li><i class="fas fa-phone mr-2"></i>+2349065257784</li>
                        <li><i class="fab fa-whatsapp mr-2"></i>+2349065257784</li>
                        <li><i class="fas fa-envelope mr-2"></i>support@joala.com.ng</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>132 Ovwian main road, Opposite the Primary School, Ovwian, Delta State, Nigeria</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-8 pt-8 text-center text-slate-400 text-sm">
                &copy; {{ date('Y') }} {{ $settings['site_name'] ?? 'JoAla' }}. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    @if(session()->has('customer_id'))
    <script>
    function updateNotifBadge() {
        fetch('/customer/notifications/unread-count')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var badge = document.getElementById('notif-badge');
                if (!badge) return;
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(function() {});
    }
    document.addEventListener('DOMContentLoaded', updateNotifBadge);
    setInterval(updateNotifBadge, 30000);
    </script>
    @endif
    @yield('scripts')
    @if(!app()->isLocal() && env('APP_ENV') !== 'local')
    <script>
    (function(){
        var x=new XMLHttpRequest();
        x.open('GET','{{ route("process.emails") }}',true);
        x.send();
        var y=new XMLHttpRequest();
        y.open('GET','{{ route("process.automation") }}',true);
        y.send();
    })();
    </script>
    @endif
</body>
</html>