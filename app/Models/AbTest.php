<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbTest extends Model
{
    protected $table = 'ab_tests';
    
    protected $fillable = [
        'name',
        'subject_a',
        'subject_b',
        'body_a',
        'body_b',
        'sequence_step_id',
        'status',
        'winner',
        'opens_a',
        'opens_b',
        'clicks_a',
        'clicks_b',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(SequenceStep::class, 'sequence_step_id');
    }

    public function getTotalA(): int
    {
        return ($this->opens_a ?? 0) + ($this->clicks_a ?? 0);
    }

    public function getTotalB(): int
    {
        return ($this->opens_b ?? 0) + ($this->clicks_b ?? 0);
    }

    public function getOpenRateAAttribute(): float
    {
        $sent = $this->sent_a ?? 0;
        return $sent > 0 ? round(($this->opens_a / $sent) * 100, 1) : 0;
    }

    public function getOpenRateBAttribute(): float
    {
        $sent = $this->sent_b ?? 0;
        return $sent > 0 ? round(($this->opens_b / $sent) * 100, 1) : 0;
    }

    public function getWinner(): ?string
    {
        if ($this->opens_a > $this->opens_b) return 'a';
        if ($this->opens_b > $this->opens_a) return 'b';
        return null;
    }
}