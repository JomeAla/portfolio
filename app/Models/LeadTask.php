<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadTask extends Model
{
    protected $table = 'lead_tasks';
    
    protected $fillable = [
        'lead_id',
        'title',
        'description',
        'due_date',
        'assigned_to',
        'status',
        'priority',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public static function statuses(): array
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function priorities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', '!=', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->where('status', '!=', 'completed');
    }
}