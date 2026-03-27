<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'JoAla Portfolio') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: {{ \App\Models\Setting::get('primary_color', '#0f172a') }};
            --accent: {{ \App\Models\Setting::get('accent_color', '#3b82f6') }};
        }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .bg-accent { background-color: var(--accent); }
        .text-accent { color: var(--accent); }
        .border-accent { border-color: var(--accent); }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white fixed h-full">
            <div class="p-6">
                <h1 class="text-xl font-bold">JoAla Admin</h1>
            </div>
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-home w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 {{ request()->routeIs('admin.settings*') ? 'bg-slate-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </a>
                <a href="{{ route('admin.projects.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 {{ request()->routeIs('admin.projects*') ? 'bg-slate-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-briefcase w-5"></i>
                    <span>Projects</span>
                </a>
                <a href="{{ route('admin.services.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 {{ request()->routeIs('admin.services*') ? 'bg-slate-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-code w-5"></i>
                    <span>Services</span>
                </a>
                <a href="{{ route('admin.testimonials.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 {{ request()->routeIs('admin.testimonials*') ? 'bg-slate-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-quote-left w-5"></i>
                    <span>Testimonials</span>
                </a>
                <a href="{{ route('admin.briefs') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 {{ request()->routeIs('admin.briefs*') ? 'bg-slate-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-envelope w-5"></i>
                    <span>Project Briefs</span>
                </a>
                <a href="{{ route('admin.pages.index') }}" class="flex items-center px-6 py-3 hover:bg-slate-800 {{ request()->routeIs('admin.pages*') ? 'bg-slate-800 border-l-4 border-blue-500' : '' }}">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Pages</span>
                </a>
            </nav>
            <div class="absolute bottom-0 w-full p-6">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2 text-red-400 hover:text-red-300">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
    @yield('scripts')
</body>
</html>