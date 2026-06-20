<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppBroadcast extends Model
{
    protected $table = 'whatsapp_broadcasts';

    protected $fillable = [
        'name',
        'message',
        'payload',
        'template_id',
        'status',
        'scheduled_at',
        'total_recipients',
        'sent_count',
        'failed_count',
        'log',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'log' => 'array',
        'payload' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(\App\Models\WhatsAppTemplate::class, 'template_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }
}
