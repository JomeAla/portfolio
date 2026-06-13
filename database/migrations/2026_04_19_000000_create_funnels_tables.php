<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('funnel_type')->nullable();
            $table->string('goal')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('automation_enabled')->default(false);
            $table->unsignedBigInteger('welcome_sequence_id')->nullable();
            $table->unsignedBigInteger('followup_sequence_id')->nullable();
            $table->text('webhook_url')->nullable();
            $table->boolean('webhook_enabled')->default(false);
            $table->string('notify_email')->nullable();
            $table->boolean('upsell_enabled')->default(false);
            $table->unsignedBigInteger('upsell_product_id')->nullable();
            $table->decimal('upsell_discount', 5, 2)->nullable();
            $table->integer('upsell_timer')->nullable();
            $table->text('facebook_pixel')->nullable();
            $table->text('google_pixel')->nullable();
            $table->boolean('countdown_enabled')->default(false);
            $table->integer('countdown_hours')->nullable();
            $table->string('thank_you_title')->nullable();
            $table->text('thank_you_message')->nullable();
            $table->string('thank_you_video')->nullable();
            $table->string('upsell_button_text')->nullable();
            $table->boolean('exit_popup_enabled')->default(false);
            $table->text('exit_popup_offer')->nullable();
            $table->decimal('exit_popup_discount', 5, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('order_bumps')->nullable();
            $table->string('refund_policy', 50)->nullable()->default('days');
            $table->integer('refund_period_days')->nullable()->default(30);
            $table->boolean('affiliate_enabled')->nullable()->default(false);
            $table->decimal('affiliate_commission', 5, 2)->nullable()->default(20.00);
            $table->integer('affiliate_cookie_days')->nullable()->default(30);
            $table->integer('score_per_page')->nullable()->default(5);
            $table->integer('score_per_email')->nullable()->default(10);
            $table->integer('score_per_checkout')->nullable()->default(20);
            $table->integer('score_hot_threshold')->nullable()->default(100);
            $table->string('hot_lead_tag', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('funnel_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funnel_id');
            $table->foreign('funnel_id')->references('id')->on('funnels')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable();
            $table->longText('content')->nullable();
            $table->integer('order')->default(0);
            $table->integer('delay_days')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        Schema::create('funnel_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funnel_id');
            $table->foreign('funnel_id')->references('id')->on('funnels')->onDelete('cascade');
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->string('email')->nullable();
            $table->string('source')->nullable();
            $table->boolean('converted')->default(false);
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->integer('score')->nullable()->default(0);
            $table->datetime('last_activity')->nullable();
            $table->integer('times_visited')->nullable()->default(0);
            $table->integer('pages_viewed')->nullable()->default(0);
            $table->integer('email_opens')->nullable()->default(0);
            $table->boolean('is_tagged_hot')->nullable()->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_leads');
        Schema::dropIfExists('funnel_stages');
        Schema::dropIfExists('funnels');
    }
};
