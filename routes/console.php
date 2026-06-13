<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Marketing\MarketingService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('marketing:process-queue', function () {
    $service = new MarketingService();
    
    $this->info('Processing tweet queue...');
    $tweetResult = $service->processTweetQueue();
    $this->info("Sent: {$tweetResult['sent']}, Failed: {$tweetResult['failed']}");
    
    $this->info('Marketing queue processing complete.');
})->purpose('Process tweet queue')->everyFifteenMinutes();

Artisan::command('blog:publish-scheduled', function () {
    $count = \App\Models\BlogPost::where('is_published', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->count();
    
    if ($count > 0) {
        \App\Models\BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['published_at' => now()]);
    }
    
    $this->info("Checked scheduled blog posts - {$count} ready to publish.");
})->purpose('Publish scheduled blog posts')->everyFifteenMinutes();