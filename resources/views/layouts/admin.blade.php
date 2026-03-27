<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - JoAla Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="/admin" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-home w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/settings" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-cog w-5"></i>
                    <span>Settings</span>
                </a>
                <a href="/admin/projects" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-briefcase w-5"></i>
                    <span>Projects</span>
                </a>
                <a href="/admin/services" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-code w-5"></i>
                    <span>Services</span>
                </a>
                <a href="/admin/testimonials" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-quote-left w-5"></i>
                    <span>Testimonials</span>
                </a>
                <a href="/admin/briefs" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-envelope w-5"></i>
                    <span>Project Briefs</span>
                </a>
                <a href="/admin/pages" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Pages</span>
                </a>
            </nav>
            <div class="absolute bottom-0 w-full p-6">
                <form method="POST" action="/admin/logout">
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
