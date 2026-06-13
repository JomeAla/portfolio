<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tweet_queues', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->unsignedBigInteger('blog_post_id')->nullable();
            $table->timestamp('scheduled_send_time')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'sent', 'failed'])->default('pending');
            $table->text('twitter_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->foreign('blog_post_id')->references('id')->on('blog_posts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tweet_queues');
    }
};
