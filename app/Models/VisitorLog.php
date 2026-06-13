<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip_address', 'user_agent', 'url', 'referer', 'session_id', 'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    public static function logVisit()
    {
        try {
            $ip = request()->ip();
            $sessionId = session()->getId();

            $recent = self::where('session_id', $sessionId)
                ->where('visited_at', '>=', now()->subMinutes(5))
                ->first();

            if ($recent) {
                return;
            }

            self::create([
                'ip_address' => $ip,
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
                'referer' => request()->header('referer'),
                'session_id' => $sessionId,
                'visited_at' => now(),
            ]);
        } catch (\Exception $e) {
        }
    }
}
