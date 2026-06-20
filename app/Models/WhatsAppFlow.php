<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppFlow extends Model
{
    protected $table = 'whatsapp_flows';

    protected $fillable = [
        'name', 'description', 'flow_id', 'flow_json', 'flow_data', 'status',
    ];

    protected $casts = [
        'flow_json' => 'array',
        'flow_data' => 'array',
    ];

    public function scopeDeployed($q) { return $q->where('status', 'deployed'); }
}
