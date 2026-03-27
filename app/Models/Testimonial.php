<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'company', 'role', 'content', 'avatar', 'rating', 'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}