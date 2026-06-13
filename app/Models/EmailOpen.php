<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailOpen extends Model
{
    protected $fillable = [
        'email_queue_id',
        'opened_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    public function emailQueue(): BelongsTo
    {
        return $this->belongsTo(EmailQueue::class, 'email_queue_id');
    }
}