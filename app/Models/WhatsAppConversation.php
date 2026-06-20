<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConversation extends Model
{
    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'name', 'description', 'trigger_event', 'steps', 'is_active',
    ];

    protected $casts = [
        'steps' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($q) { return $q->where('is_active', true); }

    public function logs()
    {
        return $this->hasMany(WhatsAppConversationLog::class, 'conversation_id');
    }
}
