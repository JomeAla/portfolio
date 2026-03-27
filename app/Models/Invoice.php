<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'client_name', 'client_email', 'client_phone',
        'amount', 'amount_paid', 'description', 'status',
        'payment_reference', 'paid_at', 'expires_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (empty($invoice->expires_at)) {
                $invoice->expires_at = now()->addHours(24);
            }
        });
    }

    public static function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $lastInvoice = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -3) + 1 : 1;
        return 'INV-' . $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function isExpired()
    {
        return $this->status === 'pending' && now()->greaterThan($this->expires_at);
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isPartial()
    {
        return $this->status === 'partial';
    }

    public function getBalanceDue()
    {
        return $this->amount - $this->amount_paid;
    }

    public function getPaymentUrl()
    {
        return route('invoices.show', $this->invoice_number);
    }

    public function markAsPaid($amount = null)
    {
        $paidAmount = $amount ?? $this->getBalanceDue();
        $this->amount_paid += $paidAmount;
        
        if ($this->amount_paid >= $this->amount) {
            $this->status = 'paid';
            $this->paid_at = now();
        } else {
            $this->status = 'partial';
        }
        
        $this->save();
    }

    public function checkExpiry()
    {
        if ($this->status === 'pending' && now()->greaterThan($this->expires_at)) {
            $this->status = 'expired';
            $this->save();
        }
    }
}
