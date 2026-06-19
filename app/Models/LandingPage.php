<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\SlugGenerator;

class LandingPage extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'title';
    protected $fillable = [
        'title',
        'slug',
        'custom_html',
        'sequence_id',
        'funnel_id',
        'is_active',
        'countdown_end',
        'countdown_message',
        'show_popup',
        'popup_delay',
        'popup_title',
        'popup_html',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_popup' => 'boolean',
        'countdown_end' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Sequence::class, 'sequence_id');
    }

    public function funnel(): BelongsTo
    {
        return $this->belongsTo(Funnel::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'landing_page_id');
    }
}