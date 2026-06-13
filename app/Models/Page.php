<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SlugGenerator;

class Page extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'title';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'content' => 'array',
    ];
}
