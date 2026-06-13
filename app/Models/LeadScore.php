<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadScore extends Model
{
    protected $table = 'lead_scores';
    
    protected $fillable = [
        'lead_id',
        'event_type',
        'points',
        'description',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public static function scoringRules(): array
    {
        return [
            'email_opened' => 5,
            'email_clicked' => 10,
            'website_visited' => 3,
            'landing_page_submitted' => 20,
            'order_placed' => 50,
            'invoice_paid' => 30,
            'unsubscribed' => -10,
            'inactive_30_days' => -5,
            'inactive_60_days' => -15,
        ];
    }
}