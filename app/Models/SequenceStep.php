<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SequenceStep extends Model
{
    protected $table = 'sequence_steps';
    
    protected $fillable = [
        'sequence_id',
        'step_number',
        'subject',
        'body',
        'delay_days',
        'delay_hours',
        'step_order',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequence::class, 'sequence_id');
    }

    public function emailQueue(): HasMany
    {
        return $this->hasMany(EmailQueue::class, 'sequence_step_id');
    }
}
