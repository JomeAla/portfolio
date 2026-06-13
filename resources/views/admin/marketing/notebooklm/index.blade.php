@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">AI Content Generator</h1>
            <p class="text-slate-600 mt-1">Generate content following your writing rules</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">Guardrails Active</span>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">DNA Bible Active</span>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Generator Tools -->
        <div class="space-y-6">
            <!-- Blog Post Generator -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">
                    <i class="fas fa-pen mr-2"></i>Blog Post
                </h2>
                <form method="POST" action="{{ route('admin.marketing.notebooklm.generate') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="content_type" value="blog_post">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Topic</label>
                        <input type="text" name="topic" required placeholder="e.g., Why Laravel is best for startups" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Title (optional)</label>
                        <input type="text" name="title" placeholder="Custom title" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
                        Generate Blog Post
                    </button>
                </form>
            </div>

            <!-- Tweet Generator -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">
                    <i class="fab fa-twitter mr-2"></i>Tweet Generator
                </h2>
                <form method="POST" action="{{ route('admin.marketing.notebooklm.tweets') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Source Content</label>
                        <textarea name="content" rows="4" required placeholder="Paste blog post or article..." 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Number of Tweets</label>
                        <input type="number" name="count" value="5" min="1" max="10" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <button type="submit" class="w-full bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700 text-sm">
                        Generate Tweets
                    </button>
                </form>
            </div>

            <!-- Email Sequence Generator -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">
                    <i class="fas fa-envelope mr-2"></i>Email Sequence
                </h2>
                <form method="POST" action="{{ route('admin.marketing.notebooklm.sequence') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Sequence Name</label>
                        <input type="text" name="sequence_name" required placeholder="e.g., Welcome sequence for new leads" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Number of Emails</label>
                        <input type="number" name="steps" value="5" min="1" max="10" 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm">
                    </div>

                    <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm">
                        Generate Sequence
                    </button>
                </form>
            </div>

            <!-- Chat -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">
                    <i class="fas fa-comments mr-2"></i>AI Chat
                </h2>
                <form method="POST" action="{{ route('admin.marketing.notebooklm.chat') }}" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Your Message</label>
                        <textarea name="message" rows="3" required placeholder="Ask anything..." 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Context (optional)</label>
                        <textarea name="context" rows="2" placeholder="Background info..." 
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 text-sm"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700 text-sm">
                        Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="space-y-6">
            @if(session('generated_content'))
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-slate-800 mb-3">Generated Blog Post</h3>
                <div class="prose prose-sm max-w-none">
                    {!! nl2br(e(session('generated_content'))) !!}
                </div>
            </div>
            @endif

            @if(session('generated_tweets'))
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-slate-800 mb-3">Generated Tweets</h3>
                <div class="space-y-3">
                    @foreach(session('generated_tweets') as $index => $tweet)
                    <div class="bg-slate-50 rounded p-3">
                        <div class="text-xs text-slate-500 mb-1">Tweet {{ $index + 1 }}</div>
                        <p class="text-sm text-slate-700">{{ $tweet['content'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(session('generated_sequence'))
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-slate-800 mb-3">Generated Email Sequence</h3>
                <div class="space-y-4">
                    @foreach(session('generated_sequence') as $index => $email)
                    <div class="border border-slate-200 rounded p-3">
                        <div class="text-xs text-slate-500 mb-1">Email {{ $index + 1 }}</div>
                        <div class="font-medium text-slate-700">{{ $email['subject'] }}</div>
                        <p class="text-sm text-slate-600 mt-1">{{ $email['body'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(session('chat_response'))
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-slate-800 mb-3">AI Response</h3>
                <div class="prose prose-sm max-w-none">
                    {!! nl2br(e(session('chat_response'))) !!}
                </div>
            </div>
            @endif

            @if(!session('generated_content') && !session('generated_tweets') && !session('generated_sequence') && !session('chat_response'))
            <div class="bg-slate-50 rounded-lg p-8 text-center">
                <div class="text-slate-400 mb-4">
                    <i class="fas fa-magic text-5xl"></i>
                </div>
                <p class="text-slate-600">Generated content will appear here</p>
                <p class="text-sm text-slate-500 mt-2">All content follows your Guardrails and DNA Bible rules</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection