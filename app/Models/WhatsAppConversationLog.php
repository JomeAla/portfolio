<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConversationLog extends Model
{
    protected $table = 'whatsapp_conversation_logs';

    protected $fillable = [
        'conversation_id', 'contact_id', 'current_step', 'status', 'last_response', 'last_step_at',
    ];

    protected $casts = [
        'last_step_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    public function contact()
    {
        return $this->belongsTo(WhatsAppContact::class, 'contact_id');
    }
}
