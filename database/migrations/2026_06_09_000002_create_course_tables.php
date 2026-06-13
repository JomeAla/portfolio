<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('courses')) {
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->longText('full_description')->nullable();
                $table->string('thumbnail')->nullable();
                $table->string('video_url')->nullable();
                $table->string('instructor')->nullable();
                $table->string('difficulty')->default('beginner');
                $table->decimal('price', 10, 2)->default(0);
                $table->integer('duration_hours')->default(0);
                $table->json('features')->nullable();
                $table->json('requirements')->nullable();
                $table->boolean('is_published')->default(false);
                $table->boolean('is_drip')->default(false);
                $table->integer('drip_days')->default(0);
                $table->string('product_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->string('attachment_path')->nullable();
            $table->integer('lesson_order')->default(0);
            $table->integer('duration_minutes')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_free_preview')->default(false);
            $table->integer('drip_delay_days')->default(0);
            $table->timestamps();
        });

        if (!Schema::hasTable('course_enrollments')) {
            Schema::create('course_enrollments', function (Blueprint $table) {
                $table->id();
                $table->string('customer_email');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('order_id')->nullable();
                $table->integer('progress')->default(0);
                $table->timestamp('enrolled_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_accessed_at')->nullable();
                $table->timestamps();

                $table->index('customer_email');
                $table->unique(['customer_email', 'course_id']);
            });
        }

        if (!Schema::hasTable('lesson_progress')) {
            Schema::create('lesson_progress', function (Blueprint $table) {
                $table->id();
                $table->string('customer_email');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->foreignId('lesson_id')->constrained('course_lessons')->onDelete('cascade');
                $table->boolean('is_completed')->default(false);
                $table->integer('progress_percent')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['customer_email', 'lesson_id']);
                $table->index('customer_email');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('course_lessons');
    }
};