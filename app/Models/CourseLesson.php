<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'title', 'description', 'content',
        'video_url', 'attachment_path', 'lesson_order',
        'duration_minutes', 'is_published', 'is_free_preview',
        'drip_delay_days',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_free_preview' => 'boolean',
        'drip_delay_days' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function isAccessibleTo(string $email): bool
    {
        if ($this->is_free_preview) {
            return true;
        }

        $enrollment = \DB::table('course_enrollments')
            ->where('customer_email', $email)
            ->where('course_id', $this->course_id)
            ->first();

        if (!$enrollment) {
            return false;
        }

        if (!$this->course->is_drip) {
            return true;
        }

        $availableDays = now()->diffInDays($enrollment->enrolled_at);
        return $availableDays >= ($this->drip_delay_days ?? 0);
    }

    public function getProgressForEmail(string $email)
    {
        return \DB::table('lesson_progress')
            ->where('customer_email', $email)
            ->where('lesson_id', $this->id)
            ->first();
    }

    public function nextLesson()
    {
        return self::where('course_id', $this->course_id)
            ->where('lesson_order', '>', $this->lesson_order)
            ->where('is_published', true)
            ->orderBy('lesson_order')
            ->first();
    }

    public function previousLesson()
    {
        return self::where('course_id', $this->course_id)
            ->where('lesson_order', '<', $this->lesson_order)
            ->where('is_published', true)
            ->orderByDesc('lesson_order')
            ->first();
    }
}