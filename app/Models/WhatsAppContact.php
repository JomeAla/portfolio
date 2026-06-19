<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppContact extends Model
{
    protected $table = 'whatsapp_contacts';

    protected $fillable = [
        'lead_id',
        'phone',
        'opted_in',
        'last_sent_at',
    ];

    protected $casts = [
        'opted_in' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function scopeOptedIn($query)
    {
        return $query->where('opted_in', true);
    }
}
