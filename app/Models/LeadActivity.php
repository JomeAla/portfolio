<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $table = 'lead_activities';
    
    protected $fillable = [
        'lead_id',
        'type',
        'description',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public static function activityTypes(): array
    {
        return [
            'note' => 'Note Added',
            'call' => 'Phone Call',
            'email_sent' => 'Email Sent',
            'email_opened' => 'Email Opened',
            'email_clicked' => 'Email Clicked',
            'order_placed' => 'Order Placed',
            'invoice_sent' => 'Invoice Sent',
            'invoice_paid' => 'Invoice Paid',
            'meeting_scheduled' => 'Meeting Scheduled',
            'status_changed' => 'Status Changed',
            'tag_added' => 'Tag Added',
            'tag_removed' => 'Tag Removed',
            'score_changed' => 'Score Changed',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}