@extends('front.customer.layout')

@section('customer-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-slate-900">Notifications</h1>
    <p class="text-slate-600">Stay updated with your account activity</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    @if(count($notifications ?? []) > 0)
    <div class="divide-y divide-slate-100">
        @foreach($notifications as $notif)
        <a href="{{ $notif['link'] ? $notif['link'] : '/customer/notifications/' . $notif['id'] . '/read' }}" class="flex items-start gap-4 p-5 hover:bg-slate-50 transition-colors {{ $notif['is_read'] ? 'opacity-60' : '' }}">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                @switch($notif['type'])
                    @case('order') bg-blue-50 text-blue-600 @break
                    @case('referral') bg-emerald-50 text-emerald-600 @break
                    @case('achievement') bg-amber-50 text-amber-600 @break
                    @case('course') bg-violet-50 text-violet-600 @break
                    @default bg-slate-100 text-slate-600
                @endswitch
            ">
                @switch($notif['type'])
                    @case('order') <i class="fas fa-shopping-bag"></i> @break
                    @case('referral') <i class="fas fa-share-alt"></i> @break
                    @case('achievement') <i class="fas fa-trophy"></i> @break
                    @case('course') <i class="fas fa-graduation-cap"></i> @break
                    @default <i class="fas fa-bell"></i>
                @endswitch
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-semibold text-slate-900">{{ $notif['title'] }}</p>
                    @if(!$notif['is_read'])
                    <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                    @endif
                </div>
                <p class="text-sm text-slate-600 mt-1">{{ $notif['message'] }}</p>
                <p class="text-xs text-slate-400 mt-2">{{ date('M d, Y g:i A', strtotime($notif['created_at'])) }}</p>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="p-12 text-center">
        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-bell-slash text-2xl text-slate-400"></i>
        </div>
        <p class="text-slate-600">No notifications yet</p>
        <p class="text-sm text-slate-500 mt-1">We'll notify you when something happens</p>
    </div>
    @endif
</div>
@endsection