@extends('layouts.app')

@section('title', 'Affiliate Login')

@section('content')
<section class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="inline-block">
                <h1 class="text-3xl font-bold text-white">JoAla</h1>
            </a>
            <p class="text-slate-400 mt-2">Partner Login</p>
        </div>
        
        <div class="bg-slate-800/50 backdrop-blur-xl rounded-2xl border border-slate-700 p-8">
            @if(session('error'))
            <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
            @endif
            
            <form method="POST" action="{{ route('affiliate.login.submit') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                    <input type="email" name="email" required 
                        class="w-full bg-slate-700/50 border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
                        placeholder="you@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                    <input type="password" name="password" required 
                        class="w-full bg-slate-700/50 border border-slate-600 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
                        placeholder="••••••••">
                </div>

                <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors">
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-slate-400 text-sm">
                    Don't have an account? 
                    <a href="/affiliate" class="text-blue-400 hover:text-blue-300 font-medium">
                        Apply now
                    </a>
                </p>
            </div>
        </div>
        
        <div class="text-center mt-6">
            <a href="/" class="text-slate-500 hover:text-slate-400 text-sm">← Back to home</a>
        </div>
    </div>
</section>
@endsection