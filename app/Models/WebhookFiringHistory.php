<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookFiringHistory extends Model
{
    protected $table = 'webhook_firing_history';

    protected $fillable = [
        'automation_rule_id',
        'lead_id',
        'event_type',
        'webhook_url',
        'payload',
        'response_code',
        'response_body',
        'status',
        'error_message',
        'response_time_ms',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_code' => 'integer',
        'response_time_ms' => 'float',
    ];

    public function automationRule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}