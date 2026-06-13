<section class="bg-slate-900 py-16">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">Stay Updated</h2>
        <p class="text-slate-400 mb-6">Get the latest tips and insights delivered to your inbox.</p>
        
        @if(session('success'))
        <div class="bg-green-900/50 border border-green-700 text-green-300 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif
        
        <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="email" name="email" placeholder="Enter your email" required
                class="flex-1 px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                Subscribe
            </button>
        </form>
        
        <p class="text-xs text-slate-500 mt-3">No spam. Unsubscribe anytime.</p>
    </div>
</section>