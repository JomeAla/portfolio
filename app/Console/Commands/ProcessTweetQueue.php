<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TweetQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessTweetQueue extends Command
{
    protected $signature = 'tweets:process {--limit=50 : Maximum tweets to process}';
    protected $description = 'Process scheduled tweets';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        
        $tweets = TweetQueue::where('status', 'scheduled')
            ->where('scheduled_send_time', '<=', now())
            ->orderBy('scheduled_send_time')
            ->limit($limit)
            ->get();

        $this->info("Processing {$tweets->count()} tweets...");

        foreach ($tweets as $tweet) {
            try {
                $this->info("Posting tweet: " . substr($tweet->content, 0, 50) . "...");
                
                $twitterSetting = \App\Models\TwitterSetting::first();
                
                if (!$twitterSetting || !$twitterSetting->access_token) {
                    $tweet->update(['status' => 'failed', 'error' => 'Twitter not configured']);
                    $this->warn('Twitter not configured');
                    continue;
                }

                $response = Http::withToken($twitterSetting->access_token)
                    ->post('https://api.twitter.com/2/tweets', [
                        'text' => $tweet->content,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    $tweet->update([
                        'status' => 'sent',
                        'tweet_id' => $data['data']['id'] ?? null,
                        'sent_at' => now(),
                    ]);

                    $this->info("Tweet posted successfully!");
                } else {
                    throw new \Exception($response->body());
                }

            } catch (\Exception $e) {
                $this->error("Failed to post tweet {$tweet->id}: " . $e->getMessage());
                
                Log::error('Tweet post failed', [
                    'tweet_id' => $tweet->id,
                    'error' => $e->getMessage(),
                ]);

                $tweet->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'attempts' => $tweet->attempts + 1,
                ]);
            }
        }

        $this->info("Tweet processing complete!");
        
        return 0;
    }
}