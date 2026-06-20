<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'name', 'category', 'message_type', 'header_type', 'header_value',
        'body', 'footer', 'buttons', 'sections', 'media_url', 'catalog_id', 'status',
    ];

    protected $casts = [
        'buttons' => 'array',
        'sections' => 'array',
    ];

    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeDraft($q) { return $q->where('status', 'draft'); }

    public function getButtonCountAttribute(): int
    {
        return count($this->buttons ?? []);
    }
}
