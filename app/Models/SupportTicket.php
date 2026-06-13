<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\SlugGenerator;

class SupportTicket extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'ticket_number';

    protected $fillable = [
        'ticket_number', 'user_id', 'name', 'email', 'phone', 'subject',
        'message', 'status', 'admin_response', 'responded_at'
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . strtoupper(Str::random(8));
            }
        });
    }

    public static function generateTicketNumber()
    {
        return 'TKT-' . strtoupper(Str::random(8));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function respond(string $response): void
    {
        $this->update([
            'admin_response' => $response,
            'responded_at' => now(),
            'status' => 'answered',
        ]);
    }

    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}
