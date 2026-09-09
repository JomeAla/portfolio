<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecureApiWebhookEvent extends Model
{
    protected $table = 'secure_api_webhook_events';

    protected $fillable = [
        'provider', 'event_id', 'event_type', 'subject', 'status', 'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
