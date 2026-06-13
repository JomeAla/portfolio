<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\SlugGenerator;

class BlogPost extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'title';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
        'featured_image',
        'is_published',
        'post_to_twitter',
        'published_at',
        'show_popup',
        'popup_delay',
        'popup_title',
        'popup_html',
        'funnel_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'post_to_twitter' => 'boolean',
        'published_at' => 'datetime',
        'show_popup' => 'boolean',
    ];

    public function funnel()
    {
        return $this->belongsTo(Funnel::class);
    }

    public function tweets(): HasMany
    {
        return $this->hasMany(TweetQueue::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    public function isScheduled(): bool
    {
        return $this->is_published && 
               $this->published_at && 
               $this->published_at->isFuture();
    }

    public function canPublish(): bool
    {
        return !$this->is_published || $this->isScheduled() === false;
    }
}