<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'rule_id',
        'lead_id',
        'action',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    protected $table = 'automation_logs';

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}