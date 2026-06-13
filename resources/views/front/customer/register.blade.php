@extends('layouts.app')

@section('title', 'Register - My Account')

@section('content')
<div class="min-h-screen flex">
    <!-- Left Side - Form -->
    <div class="flex-1 flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="/customer/dashboard" class="inline-flex items-center gap-2 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-violet-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                </a>
                <h1 class="text-3xl font-bold text-slate-900">Create Account</h1>
                <p class="text-slate-600 mt-2">Join us to access your orders and downloads</p>
            </div>
            
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl mb-6">
                {{ session('error') }}
            </div>
            @endif
            
            <form method="POST" action="/customer/register" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-3.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="John Doe">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-4 py-3.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="you@example.com">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                    <input type="password" name="password" required minlength="6" class="w-full px-4 py-3.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" placeholder="Min. 6 characters">
                </div>
                
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3.5 rounded-xl transition-all hover:scale-[1.02]">
                    Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-slate-600">Already have an account? <a href="/customer/login" class="text-blue-600 font-semibold hover:underline">Sign in</a></p>
            </div>
        </div>
    </div>
    
    <!-- Right Side - Visual -->
    <div class="hidden lg:flex flex-1 bg-gradient-to-br from-violet-600 via-purple-600 to-pink-600 items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-20 left-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="relative text-white text-center max-w-md">
            <div class="w-24 h-24 bg-white/20 rounded-3xl flex items-center justify-center mx-auto mb-8">
                <i class="fas fa-gift text-4xl"></i>
            </div>
            <h2 class="text-3xl font-bold mb-4">Start Your Journey</h2>
            <p class="text-white/80">Create an account to track orders, access downloads, refer friends, and earn rewards through our affiliate program.</p>
        </div>
    </div>
</div>
@endsection