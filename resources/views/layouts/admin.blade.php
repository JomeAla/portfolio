<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($settings['favicon']))
    <link rel="icon" type="image/png" href="/storage/{{ $settings['favicon'] }}?v=2">
    <link rel="shortcut icon" type="image/png" href="/storage/{{ $settings['favicon'] }}?v=2">
    <link rel="apple-touch-icon" href="/storage/{{ $settings['favicon'] }}">
    @else
    <link rel="icon" type="image/png" href="/favicon.png?v=2">
    <link rel="shortcut icon" type="image/png" href="/favicon.png?v=2">
    <link rel="apple-touch-icon" href="/favicon.png">
    @endif
    <title>@yield('title', 'Admin Panel') - JoAla Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @yield('styles')
</head>
<body class="bg-white">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white fixed h-full flex flex-col">
            <div class="p-6">
                <h1 class="text-xl font-bold">JoAla Admin</h1>
            </div>
            <nav class="mt-4 flex-1 overflow-y-auto">
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
                <a href="/admin/products" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Products</span>
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
                    <span id="brief-badge" class="ml-auto hidden bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">0</span>
                </a>
                <a href="/admin/invoices" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-file-invoice-dollar w-5"></i>
                    <span>Invoices</span>
                </a>
                <a href="/admin/coupons" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-tag w-5"></i>
                    <span>Coupons</span>
                </a>
                <a href="/admin/pages" class="flex items-center px-6 py-3 hover:bg-slate-800">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Pages</span>
                </a>
                <div class="px-6 py-3 text-slate-400 text-sm font-semibold uppercase">Marketing</div>
                <a href="/admin/marketing" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/marketing/leads" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-users w-5"></i>
                    <span>Leads</span>
                </a>
                <a href="/admin/marketing/tags" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-tag w-5"></i>
                    <span>Tags</span>
                </a>
                <a href="/admin/marketing/segments" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-layer-group w-5"></i>
                    <span>Segments</span>
                </a>
                <a href="/admin/marketing/lead-scoring" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-star w-5"></i>
                    <span>Lead Scoring</span>
                </a>
                <div class="px-6 py-2 text-slate-500 text-xs font-semibold uppercase">Email Marketing</div>
                <a href="/admin/marketing/newsletter" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-envelope-open-text w-5"></i>
                    <span>Newsletter</span>
                </a>
                <a href="/admin/marketing/email-templates" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-envelope w-5"></i>
                    <span>Email Templates</span>
                </a>
                <a href="/admin/marketing/email-builder" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-paint-brush w-5"></i>
                    <span>Email Builder</span>
                </a>
                <a href="/admin/marketing/email-queue" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-paper-plane w-5"></i>
                    <span>Email Queue</span>
                </a>
                <a href="/admin/marketing/sequences" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-list-ul w-5"></i>
                    <span>Sequences</span>
                </a>
                <a href="/admin/marketing/campaigns" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-bullhorn w-5"></i>
                    <span>Campaigns</span>
                </a>
                <div class="px-6 py-2 text-slate-500 text-xs font-semibold uppercase">WhatsApp</div>
                <a href="/admin/whatsapp" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fab fa-whatsapp w-5"></i>
                    <span>Broadcast</span>
                </a>
                <a href="/admin/whatsapp/templates" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-layer-group w-5"></i>
                    <span>Templates</span>
                </a>
                <a href="/admin/whatsapp/flows" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-code-branch w-5"></i>
                    <span>Flows</span>
                </a>
                <a href="/admin/whatsapp/conversations" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-comments w-5"></i>
                    <span>Conversations</span>
                </a>
                <a href="/admin/whatsapp/contacts" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-address-book w-5"></i>
                    <span>Contacts</span>
                </a>
                <div class="px-6 py-2 text-slate-500 text-xs font-semibold uppercase">Content</div>
                <a href="/admin/marketing/blog" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-blog w-5"></i>
                    <span>Blog Posts</span>
                </a>
                <a href="/admin/marketing/landing-pages" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-laptop-code w-5"></i>
                    <span>Landing Pages</span>
                </a>
                <div class="px-6 py-2 text-slate-500 text-xs font-semibold uppercase">Automation</div>
                <a href="/admin/marketing/automation/builder" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-robot w-5"></i>
                    <span>Automation</span>
                </a>
                <a href="/admin/marketing/ab-tests" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-flask w-5"></i>
                    <span>A/B Tests</span>
                </a>
                <a href="/admin/marketing/webhooks" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-plug w-5"></i>
                    <span>Webhooks</span>
                </a>
                <div class="px-6 py-2 text-slate-500 text-xs font-semibold uppercase">Funnels</div>
                <a href="/admin/marketing/funnels" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-funnel-dollar w-5"></i>
                    <span>Funnels</span>
                </a>
                <a href="/admin/marketing/templates" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-copy w-5"></i>
                    <span>Templates</span>
                </a>
                <div class="px-6 py-2 text-slate-500 text-xs font-semibold uppercase">Analytics & Settings</div>
                <a href="/admin/marketing/analytics" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Analytics</span>
                </a>
                <a href="/admin/marketing/settings" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fab fa-twitter w-5"></i>
                    <span>Twitter</span>
                </a>
                <a href="/admin/marketing/tweets" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fab fa-twitter w-5"></i>
                    <span>Tweets</span>
                </a>
                <a href="/admin/affiliates" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-users w-5"></i>
                    <span>Affiliates</span>
                </a>
                <a href="/admin/membership/tiers" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-crown w-5"></i>
                    <span>Membership</span>
                </a>
                <a href="/admin/courses" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-graduation-cap w-5"></i>
                    <span>Courses</span>
                </a>
                <a href="/admin/refunds" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-undo-alt w-5"></i>
                    <span>Refunds</span>
                </a>
<a href="/admin/support" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-ticket-alt w-5"></i>
                    <span>Support</span>
                    <span id="support-badge" class="ml-auto hidden bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">0</span>
                </a>
                <a href="/admin/notifications" class="flex items-center px-6 py-2 hover:bg-slate-800 text-sm">
                    <i class="fas fa-bell w-5"></i>
                    <span>Notifications</span>
</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 flex-1">
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
    <script>
    function updateBadge(id, url) {
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var badge = document.getElementById(id);
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            })
            .catch(function() {});
    }
    document.addEventListener('DOMContentLoaded', function() {
        updateBadge('brief-badge', '/admin/briefs/unread-count');
        updateBadge('support-badge', '/admin/support/unread-count');
    });
    setInterval(function() {
        updateBadge('brief-badge', '/admin/briefs/unread-count');
        updateBadge('support-badge', '/admin/support/unread-count');
    }, 30000);
    </script>
</body>
</html>
