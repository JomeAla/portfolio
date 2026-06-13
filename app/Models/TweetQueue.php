<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TweetQueue extends Model
{
    protected $fillable = [
        'content',
        'blog_post_id',
        'scheduled_send_time',
        'status',
        'twitter_response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'scheduled_send_time' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}