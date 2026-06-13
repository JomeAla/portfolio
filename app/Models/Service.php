<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\SlugGenerator;

class Service extends Model
{
    use HasFactory, SlugGenerator;

    protected static $slugSourceField = 'title';

    protected $fillable = [
        'title', 'slug', 'description', 'features', 'pricing', 'icon', 'order', 'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}