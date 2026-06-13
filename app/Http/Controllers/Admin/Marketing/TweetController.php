<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing\TweetQueue;
use App\Models\Marketing\BlogPost;
use App\Services\Marketing\MarketingService;
use Illuminate\Http\Request;

class TweetController extends Controller
{
    public function index()
    {
        $tweets = TweetQueue::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.marketing.tweets.index', compact('tweets'));
    }

    public function create()
    {
        $blogPosts = BlogPost::where('is_published', true)->get();
        return view('admin.marketing.tweets.create', compact('blogPosts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:280',
        ]);

        $scheduledTime = $request->scheduled_at 
            ? \Carbon\Carbon::parse($request->scheduled_at)
            : now();

        TweetQueue::create([
            'content' => $request->content,
            'blog_post_id' => $request->blog_post_id,
            'scheduled_send_time' => $scheduledTime,
            'status' => $request->has('send_now') ? 'scheduled' : 'draft',
        ]);

        if ($request->has('send_now')) {
            $tweet = TweetQueue::latest()->first();
            $marketing = app(MarketingService::class);
            $marketing->processTweetQueue();
        }

        return redirect()->route('admin.marketing.tweets.index')->with('success', 'Tweet queued.');
    }

    public function edit(TweetQueue $tweet)
    {
        return view('admin.marketing.tweets.edit', compact('tweet'));
    }

    public function update(Request $request, TweetQueue $tweet)
    {
        $request->validate([
            'content' => 'required|string|max:280',
        ]);

        $tweet->update([
            'content' => $request->content,
            'scheduled_send_time' => $request->scheduled_at ? \Carbon\Carbon::parse($request->scheduled_at) : null,
        ]);

        return redirect()->route('admin.marketing.tweets.index')->with('success', 'Tweet updated.');
    }

    public function destroy(TweetQueue $tweet)
    {
        $tweet->delete();
        return back()->with('success', 'Tweet deleted.');
    }

    public function sendNow(TweetQueue $tweet)
    {
        $marketing = app(MarketingService::class);
        $result = $marketing->postTweet($tweet->content);
        
        if ($result['success']) {
            $tweet->update(['status' => 'sent', 'sent_at' => now(), 'twitter_response' => json_encode($result)]);
            return back()->with('success', 'Tweet sent!');
        }
        
        return back()->with('error', 'Failed to send tweet: ' . $result['error']);
    }
}