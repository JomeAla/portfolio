<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelLead extends Model
{
    protected $fillable = [
        'funnel_id',
        'lead_id',
        'stage_id',
        'entered_at',
        'exited_at',
        'converted',
        'source',
        'email',
        'score',
        'last_activity',
        'times_visited',
        'pages_viewed',
        'email_opens',
        'clicks_count',
        'is_tagged_hot',
        'workflow_state',
        'status',
        'ab_variant',
        'wait_until',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
        'converted' => 'boolean',
        'last_activity' => 'datetime',
        'workflow_state' => 'array',
    ];

    public function funnel()
    {
        return $this->belongsTo(Funnel::class);
    }

    public function stage()
    {
        return $this->belongsTo(FunnelStage::class, 'stage_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function addPageView()
    {
        $this->increment('times_visited');
        $this->increment('pages_viewed');
    }

    public function addEmailOpen()
    {
        $this->increment('email_opens');
    }

    public function addClick()
    {
        $this->increment('clicks_count');
    }

    public function addScore(int $points)
    {
        $this->increment('score', $points);
    }

    public function isHot(?int $threshold = null): bool
    {
        $threshold = $threshold ?? ($this->funnel->score_hot_threshold ?? 100);
        return $this->score >= $threshold;
    }

    public function getEngagementLevel(): string
    {
        $score = $this->score ?? 0;
        if ($score >= 100) return 'hot';
        if ($score >= 50) return 'warm';
        return 'cold';
    }
}