<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppGroup extends Model
{
    protected $table = 'whatsapp_groups';

    protected $fillable = [
        'name',
        'group_jid',
        'description',
        'member_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'member_count' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
