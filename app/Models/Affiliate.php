<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\SlugGenerator;

class Affiliate extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'referral_code',
        'status',
        'total_earned',
        'total_paid',
    ];

    protected $casts = [
        'total_earned' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }

    public function getAvailableBalanceAttribute(): float
    {
        return $this->total_earned - $this->total_paid;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
