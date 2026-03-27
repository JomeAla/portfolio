<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'product_id', 'customer_name', 'customer_email',
        'customer_phone', 'amount', 'discount', 'final_amount', 'coupon_code',
        'payment_status', 'payment_reference', 'download_token', 'download_expires_at'
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isDownloadExpired()
    {
        return $this->download_expires_at && now()->greaterThan($this->download_expires_at);
    }

    public function canDownload()
    {
        return $this->payment_status === 'success' && 
               !$this->isDownloadExpired() && 
               $this->product && 
               $this->product->file_path;
    }
}
