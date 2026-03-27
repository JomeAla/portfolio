<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromoBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'message', 'link', 'image', 'button_text',
        'background_color', 'text_color', 'is_active',
        'show_from', 'show_until', 'order'
    ];

    protected $casts = [
        'show_from' => 'datetime',
        'show_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isActive()
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->show_from && $now->lessThan($this->show_from)) {
            return false;
        }

        if ($this->show_until && $now->greaterThan($this->show_until)) {
            return false;
        }

        return true;
    }

    public static function activeBanners()
    {
        return self::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('show_from')
                    ->orWhere('show_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('show_until')
                    ->orWhere('show_until', '>=', now());
            })
            ->orderBy('order')
            ->get();
    }
}
