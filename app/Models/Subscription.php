<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_email', 'plan_id', 'paystack_subscription_code',
        'paystack_email_token', 'status', 'started_at', 'current_period_end',
        'trial_end_date', 'cancelled_at', 'next_billing_date', 'reference',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_end_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_billing_date' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'active' && $this->trial_end_date && now()->lessThan($this->trial_end_date);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->current_period_end && now()->greaterThan($this->current_period_end));
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function getActiveForEmail(string $email): ?self
    {
        return self::where('customer_email', $email)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('current_period_end')->orWhere('current_period_end', '>', now());
            })
            ->latest()
            ->first();
    }
}