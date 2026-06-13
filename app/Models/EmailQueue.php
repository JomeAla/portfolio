<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailQueue extends Model
{
    protected $table = 'email_queue';
    
    protected $fillable = [
        'lead_id',
        'sequence_step_id',
        'subject',
        'body',
        'status',
        'scheduled_send_time',
        'sent_at',
        'opened',
        'clicked',
        'opened_at',
        'clicked_at',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(SequenceStep::class, 'sequence_step_id');
    }

    public function emailOpens()
    {
        return $this->hasMany(EmailOpen::class, 'email_queue_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOpened($query)
    {
        return $query->where('opened', true);
    }

    public function scopeClicked($query)
    {
        return $query->where('clicked', true);
    }

    public function scopeDelivered($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }
}