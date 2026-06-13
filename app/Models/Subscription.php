<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_email', 'subscription_plan_id', 'paystack_subscription_code',
        'paystack_email_token', 'status', 'starts_at', 'ends_at',
        'trial_ends_at', 'cancelled_at', 'next_billing_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_billing_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'active' && $this->trial_ends_at && now()->lessThan($this->trial_ends_at);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->ends_at && now()->greaterThan($this->ends_at));
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
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();
    }
}