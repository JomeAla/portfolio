<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price', 'interval',
        'trial_days', 'features', 'paystack_plan_code',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    public function isMonthly(): bool
    {
        return $this->interval === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->interval === 'yearly';
    }

    public function getFormattedPriceAttribute(): string
    {
        return '₦' . number_format($this->price);
    }

    public function getIntervalLabelAttribute(): string
    {
        return match($this->interval) {
            'monthly' => '/month',
            'yearly' => '/year',
            'quarterly' => '/quarter',
            'weekly' => '/week',
            default => '/' . $this->interval,
        };
    }
}