<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'problem_solved', 'solution',
        'technologies', 'category', 'industry', 'client_name', 'duration',
        'thumbnail', 'images', 'github_url', 'live_url', 'order', 'is_featured'
    ];

    protected $casts = [
        'technologies' => 'array',
        'images' => 'array',
        'is_featured' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}