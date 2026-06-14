<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\SlugGenerator;

class Lead extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';

    public function getRouteKeyName()
    {
        return 'id';
    }
    
    protected $fillable = [
        'email',
        'name',
        'landing_page_id',
        'sequence_id',
        'status',
        'is_newsletter',
        'confirmed',
        'confirmation_token',
        'confirmed_at',
        'source',
        'enrolled_at',
        'score',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'referrer_url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'confirmed' => 'boolean',
        'is_newsletter' => 'boolean',
        'score' => 'integer',
    ];

    public function funnels(): BelongsToMany
    {
        return $this->belongsToMany(Funnel::class, 'funnel_leads')
            ->withPivot(['stage_id', 'entered_at', 'exited_at', 'converted']);
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class, 'segment_leads')
            ->withPivot('synced_at');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($lead) {
            if (empty($lead->status)) {
                $lead->status = 'active';
            }
        });

        static::created(function ($lead) {
            if (app()->bound(\App\Services\SegmentService::class)) {
                app(\App\Services\SegmentService::class)->onLeadCreated($lead);
            }
        });

        static::updated(function ($lead) {
            if (app()->bound(\App\Services\SegmentService::class)) {
                app(\App\Services\SegmentService::class)->onLeadUpdated($lead);
            }
        });
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Sequence::class, 'sequence_id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(EmailQueue::class, 'lead_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'lead_tags', 'lead_id', 'tag_id');
    }

    public function addScore(int $points): void
    {
        $this->increment('score', $points);
    }

    public function removeScore(int $points): void
    {
        $this->decrement('score', $points);
    }

    public static function subscribeToNewsletter(string $email, ?string $name = null): self
    {
        $existing = Lead::where('email', $email)->first();

        if ($existing && $existing->confirmed) {
            $existing->update([
                'name' => $name ?? $existing->name,
                'is_newsletter' => true,
            ]);
            return $existing;
        }

        $lead = Lead::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'is_newsletter' => true,
                'confirmed' => false,
                'confirmation_token' => Str::random(64),
            ]
        );

        return $lead;
    }

    public function confirm(): bool
    {
        if ($this->confirmed) {
            return true;
        }

        $this->update([
            'confirmed' => true,
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);

        return true;
    }

    public function unsubscribe(): bool
    {
        $this->update(['status' => 'unsubscribed']);
        return true;
    }

    public function scopeNewsletter($query)
    {
        return $query->where('is_newsletter', true);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('confirmed', true);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class)->latest();
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(LeadScore::class)->latest();
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(EmailAnalytics::class, 'lead_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'lead_id');
    }

    public function automationLogs(): HasMany
    {
        return $this->hasMany(\App\Models\AutomationLog::class);
    }

    public function scopeHot($query)
    {
        return $query->where('score', '>=', 100);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getFullNameAttribute(): string
    {
        return $this->name ?? $this->email;
    }

    public function isHot(): bool
    {
        return $this->score >= 100;
    }
}