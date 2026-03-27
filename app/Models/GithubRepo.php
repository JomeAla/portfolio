<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GithubRepo extends Model
{
    use HasFactory;

    protected $fillable = [
        'repo_name', 'description', 'language', 'stars', 'forks',
        'url', 'last_updated', 'is_displayed'
    ];

    protected $casts = [
        'last_updated' => 'datetime',
        'is_displayed' => 'boolean',
    ];
}