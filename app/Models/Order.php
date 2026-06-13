<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\SlugGenerator;

class Order extends Model
{
    use HasFactory, SlugGenerator;

    protected static $slugSourceField = 'order_number';

    protected $fillable = [
        'order_number', 'product_id', 'customer_name', 'customer_email',
        'customer_phone', 'amount', 'discount', 'final_amount', 'coupon_code',
        'payment_status', 'payment_reference', 'download_token', 'download_expires_at',
        'cart_started_at', 'cart_abandoned_at', 'cart_recovered_at', 'is_cart_abandoned',
        'checkout_started_at', 'checkout_abandoned_at', 'campaign_id', 'lead_id',
        'lead_attribution_score',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'download_expires_at' => 'datetime',
    ];

    public static function generateOrderNumber()
    {
        return 'ORD-' . strtoupper(uniqid()) . '-' . date('Ymd');
    }

    public static function generateDownloadToken()
    {
        return Str::random(64);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Campaign::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isDownloadExpired(): bool
    {
        return $this->download_expires_at && now()->greaterThan($this->download_expires_at);
    }

    public function canDownload(): bool
    {
        return $this->payment_status === 'success' && 
               !$this->isDownloadExpired() && 
               $this->product && 
               $this->product->file_path;
    }

    public function isSuccessful(): bool
    {
        return $this->payment_status === 'success';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedFinalAmountAttribute(): string
    {
        return number_format($this->final_amount, 2);
    }

    // === Cart Abandonment Methods ===

    public function startCart()
    {
        $this->update([
            'cart_started_at' => now(),
            'is_cart_abandoned' => false,
        ]);
        return $this;
    }

    public function markCartAbandoned()
    {
        $this->update([
            'cart_abandoned_at' => now(),
            'is_cart_abandoned' => true,
        ]);
        return $this;
    }

    public function markCartRecovered()
    {
        $this->update([
            'cart_recovered_at' => now(),
            'is_cart_abandoned' => false,
            'payment_status' => 'success',
        ]);
        return $this;
    }

    public function startCheckout()
    {
        $this->update([
            'checkout_started_at' => now(),
        ]);
        return $this;
    }

    public function markCheckoutAbandoned()
    {
        $this->update([
            'checkout_abandoned_at' => now(),
        ]);
        return $this;
    }

    public function isCartAbandoned(): bool
    {
        return $this->is_cart_abandoned === true;
    }

    public function getCartAgeMinutes(): int
    {
        if (!$this->cart_started_at) return 0;
        return $this->cart_started_at->diffInMinutes(now());
    }

    public function getCheckoutAgeMinutes(): int
    {
        if (!$this->checkout_started_at) return 0;
        return $this->checkout_started_at->diffInMinutes(now());
    }

    public function isCartAbandonment(): bool
    {
        // Cart is abandoned if started > 1 hour ago and no purchase
        if ($this->payment_status !== 'success' && $this->cart_started_at) {
            return $this->getCartAgeMinutes() >= 60;
        }
        return false;
    }

    public function isCheckoutAbandonment(): bool
    {
        // Checkout is abandoned if started > 30 min ago and no purchase
        if ($this->payment_status !== 'success' && $this->checkout_started_at) {
            return $this->getCheckoutAgeMinutes() >= 30;
        }
        return false;
    }

    public static function getAbandonedCarts($hours = 24)
    {
        return self::whereNull('payment_status')
            ->whereNotNull('cart_started_at')
            ->where('is_cart_abandoned', false)
            ->where('cart_started_at', '<', now()->subHours($hours))
            ->get();
    }

    public static function getCheckoutAbandonments($hours = 24)
    {
        return self::whereNull('payment_status')
            ->whereNotNull('checkout_started_at')
            ->where('checkout_abandoned_at', null)
            ->where('checkout_started_at', '<', now()->subHours($hours))
            ->get();
    }
}
