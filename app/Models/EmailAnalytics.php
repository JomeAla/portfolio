<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAnalytics extends Model
{
    protected $table = 'email_analytics';
    
    protected $fillable = [
        'email_queue_id',
        'lead_id',
        'sequence_step_id',
        'sent_at',
        'opened_at',
        'clicked_at',
        'opened',
        'clicked',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'opened' => 'boolean',
        'clicked' => 'boolean',
    ];

    public function email()
    {
        return $this->belongsTo(EmailQueue::class, 'email_queue_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function step()
    {
        return $this->belongsTo(SequenceStep::class, 'sequence_step_id');
    }
}
