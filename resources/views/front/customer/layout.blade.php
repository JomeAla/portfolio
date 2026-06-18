@extends('layouts.app')

@section('title', 'Customer Portal')

@section('styles')
<style>
footer { display: none !important; }
.sidebar-link { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px; font-weight: 600; font-size: 14px; transition: all 0.2s ease; width: 100%; text-align: left; text-decoration: none; margin-bottom: 6px; }
.sidebar-link.active { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.sidebar-link:not(.active) { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
.sidebar-link:not(.active):hover { background: #fff; color: #1e293b; border-color: #cbd5e0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transform: translateY(-1px); }
.sidebar-link i { font-size: 16px; width: 20px; text-align: center; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="flex">
        <!-- Left Sidebar -->
        <aside class="w-72 bg-white border-r border-slate-200 min-h-screen fixed left-0 top-0 overflow-y-auto">
            <!-- Logo Area -->
            <div class="p-6 border-b border-slate-100">
                <a href="/customer/dashboard" class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-violet-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-slate-900">My Account</span>
                        <p class="text-xs text-slate-500">Customer Portal</p>
                    </div>
                </a>
            </div>
            
            <!-- Navigation Links -->
            <nav class="p-4 space-y-2">
                <a href="/customer/dashboard" class="sidebar-link {{ request()->is('customer/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home w-5"></i> Dashboard
                </a>
                <a href="/customer/orders" class="sidebar-link {{ request()->is('customer/orders') ? 'active' : '' }}">
                    <i class="fas fa-shopping-bag w-5"></i> My Orders
                </a>
                <a href="/customer/downloads" class="sidebar-link {{ request()->is('customer/downloads') ? 'active' : '' }}">
                    <i class="fas fa-download w-5"></i> Downloads
                </a>
                <a href="/customer/subscriptions" class="sidebar-link {{ request()->is('customer/subscriptions') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-5"></i> Subscriptions
                </a>
                <a href="/customer/referrals" class="sidebar-link {{ request()->is('customer/referrals') ? 'active' : '' }}">
                    <i class="fas fa-share-alt w-5"></i> Referrals
                </a>
                <a href="/customer/achievements" class="sidebar-link {{ request()->is('customer/achievements') ? 'active' : '' }}">
                    <i class="fas fa-trophy w-5"></i> Achievements
                </a>
                <a href="/customer/affiliate" class="sidebar-link {{ request()->is('customer/affiliate') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-usd w-5"></i> Affiliate
                </a>
                <form action="{{ url('/customer/my-courses') }}" method="GET" style="margin: 0; padding: 0;">
                    <button type="submit" class="sidebar-link {{ request()->is('customer/my-courses*') ? 'active' : '' }}" style="border: none; cursor: pointer; text-decoration: none;">
                        <i class="fas fa-graduation-cap w-5"></i> Courses
                    </button>
                </form>
                <a href="/customer/settings" class="sidebar-link {{ request()->is('customer/settings') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5"></i> Settings
                </a>
                <a href="/customer/refund" class="sidebar-link {{ request()->is('customer/refund') ? 'active' : '' }}">
                    <i class="fas fa-undo w-5"></i> Request Refund
                </a>
                <a href="/customer/notifications" class="sidebar-link {{ request()->is('customer/notifications') ? 'active' : '' }}">
                    <i class="fas fa-bell w-5"></i> Notifications
                    <span id="notif-badge" class="hidden ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">0</span>
                </a>
            </nav>
            
            <!-- Bottom Links -->
            <div class="p-4 border-t border-slate-100">
                <a href="/" class="sidebar-link text-blue-600 hover:bg-blue-50">
                    <i class="fas fa-store w-5"></i> Browse Store
                </a>
                <a href="/customer/logout" class="sidebar-link text-red-600 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-5"></i> Sign Out
                </a>
            </div>
        </aside>
        
<!-- Main Content Area -->
        <main class="ml-72 flex-1 p-8">
            @yield('customer-content')
        </main>
    </div>
</div>

<!-- Chat Support Widget -->
<div id="chat-widget" class="fixed bottom-6 right-6 z-50">
    <!-- Chat Button -->
    <button onclick="toggleChat()" class="w-14 h-14 bg-gradient-to-r from-blue-600 to-violet-600 rounded-full shadow-lg flex items-center justify-center text-white hover:scale-110 transition-transform">
        <i class="fas fa-comments text-xl"></i>
    </button>
</div>

<!-- Chat Panel (Hidden by default) -->
<div id="chat-panel" class="fixed bottom-24 right-6 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 hidden z-50">
    <div class="p-4 bg-gradient-to-r from-blue-600 to-violet-600 rounded-t-2xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-headset text-white"></i>
                </div>
                <div>
                    <p class="font-semibold text-white">Support Chat</p>
                    <p class="text-xs text-white/70">We typically reply within minutes</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-white hover:text-white/70">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div id="chat-messages" class="h-64 p-4 overflow-y-auto">
        <div class="bg-slate-100 rounded-2xl p-3 max-w-[80%]">
            <p class="text-sm text-slate-700">Hello! How can we help you today?</p>
        </div>
    </div>
    <div class="p-4 border-t border-slate-100">
        <form onsubmit="sendMessage(event)">
            <div class="flex gap-2">
                <input type="text" id="chat-input" placeholder="Type a message..." class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateNotifBadge() {
    fetch('/customer/notifications/unread-count')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var badge = document.getElementById('notif-badge');
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        })
        .catch(function() {});
}
document.addEventListener('DOMContentLoaded', updateNotifBadge);
setInterval(updateNotifBadge, 30000);

function toggleChat() {
    var panel = document.getElementById('chat-panel');
    panel.classList.toggle('hidden');
}

function sendMessage(e) {
    e.preventDefault();
    var input = document.getElementById('chat-input');
    var message = input.value.trim();
    if (!message) return;
    
    var container = document.getElementById('chat-messages');
    
    // Add user message
    var userMsg = document.createElement('div');
    userMsg.className = 'bg-blue-600 text-white rounded-2xl p-3 max-w-[80%] ml-auto mb-2';
    userMsg.innerHTML = '<p class="text-sm">' + message + '</p>';
    container.appendChild(userMsg);
    
    input.value = '';
    container.scrollTop = container.scrollHeight;
    
    // Simulate response
    setTimeout(function() {
        var reply = document.createElement('div');
        reply.className = 'bg-slate-100 rounded-2xl p-3 max-w-[80%] mb-2';
        reply.innerHTML = '<p class="text-sm text-slate-700">Thank you for your message! Our support team will get back to you shortly. You can also email us at support@joala.com.ng</p>';
        container.appendChild(reply);
        container.scrollTop = container.scrollHeight;
    }, 1000);
}
</script>
@endsection