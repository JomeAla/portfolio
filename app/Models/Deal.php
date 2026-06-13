<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\SlugGenerator;

class Deal extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'title';
    
    protected $fillable = [
        'lead_id',
        'title',
        'value',
        'stage',
        'probability',
        'expected_close_date',
        'notes',
        'assigned_to',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
    ];

    public static function stages(): array
    {
        return [
            'lead' => 'Lead',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal' => 'Proposal Sent',
            'negotiation' => 'Negotiation',
            'won' => 'Won',
            'lost' => 'Lost',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('stage', ['won', 'lost']);
    }

    public function getStageColorAttribute(): string
    {
        $colors = [
            'lead' => 'gray',
            'contacted' => 'blue',
            'qualified' => 'yellow',
            'proposal' => 'orange',
            'negotiation' => 'purple',
            'won' => 'green',
            'lost' => 'red',
        ];
        return $colors[$this->stage] ?? 'gray';
    }

    public function getWeightedValueAttribute(): float
    {
        return ($this->value * $this->probability) / 100;
    }

    public function isOpen(): bool
    {
        return !in_array($this->stage, ['won', 'lost']);
    }
}