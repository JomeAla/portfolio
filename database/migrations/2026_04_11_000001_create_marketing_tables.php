<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Blog Posts
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('post_to_twitter')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // Email Sequences
        Schema::create('email_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Sequences (alias for email_sequences)
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Landing Pages
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('custom_html');
            $table->unsignedBigInteger('sequence_id')->nullable();
            $table->foreign('sequence_id')->references('id')->on('sequences')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('landing_page_id')->nullable();
            $table->foreign('landing_page_id')->references('id')->on('landing_pages')->onDelete('set null');
            $table->unsignedBigInteger('sequence_id')->nullable();
            $table->foreign('sequence_id')->references('id')->on('sequences')->onDelete('set null');
            $table->enum('status', ['active', 'unsubscribed'])->default('active');
            $table->timestamps();
        });

        // Sequence Steps
        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sequence_id');
            $table->foreign('sequence_id')->references('id')->on('email_sequences')->onDelete('cascade');
            $table->string('subject');
            $table->text('body');
            $table->integer('delay_days')->default(0);
            $table->integer('step_order')->default(0);
            $table->timestamps();
        });

        // Email Queue
        Schema::create('email_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->unsignedBigInteger('sequence_step_id');
            $table->foreign('sequence_step_id')->references('id')->on('sequence_steps')->onDelete('cascade');
            $table->timestamp('scheduled_send_time');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->boolean('opened')->default(false);
            $table->boolean('clicked')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();
        });

        // Tweet Queue
        Schema::create('tweet_queue', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->unsignedBigInteger('blog_post_id')->nullable();
            $table->foreign('blog_post_id')->references('id')->on('blog_posts')->onDelete('set null');
            $table->timestamp('scheduled_send_time')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sent', 'failed'])->default('draft');
            $table->text('twitter_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // Email Open Tracking
        Schema::create('email_opens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_queue_id');
            $table->foreign('email_queue_id')->references('id')->on('email_queue')->onDelete('cascade');
            $table->timestamp('opened_at');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
        });

        // Twitter Settings
        Schema::create('twitter_settings', function (Blueprint $table) {
            $table->id();
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('token_type')->nullable();
            $table->integer('expires_at')->nullable();
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->string('oauth_token')->nullable();
            $table->string('oauth_token_secret')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_opens');
        Schema::dropIfExists('tweet_queue');
        Schema::dropIfExists('email_queue');
        Schema::dropIfExists('sequence_steps');
        Schema::dropIfExists('email_sequences');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('landing_pages');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('twitter_settings');
    }
};