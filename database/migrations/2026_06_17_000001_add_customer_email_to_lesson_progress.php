<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
        } elseif (!Schema::hasColumn('lesson_progress', 'customer_email')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->string('customer_email')->after('id');
                $table->index('customer_email');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('lesson_progress', 'customer_email')) {
            Schema::table('lesson_progress', function (Blueprint $table) {
                $table->dropColumn('customer_email');
            });
        }
    }
};
