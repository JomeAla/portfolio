<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'thumbnail',
        'difficulty', 'instructor', 'is_published',
        'is_drip', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_drip' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class)->where('is_published', true)->orderBy('lesson_order');
    }

}