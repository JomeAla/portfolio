<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\SlugGenerator;

class Testimonial extends Model
{
    use HasFactory, SlugGenerator;

    protected static $slugSourceField = 'name';

    protected $fillable = [
        'name', 'company', 'role', 'content', 'avatar', 'rating', 'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }
}