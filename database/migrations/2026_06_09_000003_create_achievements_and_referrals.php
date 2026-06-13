<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('fa-trophy');
            $table->string('badge_color')->default('blue');
            $table->string('trigger_type')->nullable();
            $table->string('trigger_value')->nullable();
            $table->integer('points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        if (!Schema::hasTable('customer_achievements')) {
            Schema::create('customer_achievements', function (Blueprint $table) {
                $table->id();
                $table->string('customer_email');
                $table->foreignId('achievement_id')->constrained()->onDelete('cascade');
                $table->boolean('awarded')->default(false);
                $table->timestamp('awarded_at')->nullable();
                $table->timestamps();

                $table->unique(['customer_email', 'achievement_id']);
                $table->index('customer_email');
            });
        }

        if (!Schema::hasTable('customer_referrals')) {
            Schema::create('customer_referrals', function (Blueprint $table) {
                $table->id();
                $table->string('customer_email');
                $table->string('referral_code')->unique();
                $table->integer('total_referrals')->default(0);
                $table->decimal('total_credits', 10, 2)->default(0);
                $table->decimal('credit_per_referral', 10, 2)->default(1000.00);
                $table->timestamps();

                $table->index('customer_email');
            });
        }

        Schema::create('referral_uses', function (Blueprint $table) {
            $table->id();
            $table->string('referral_code');
            $table->string('referred_email');
            $table->string('referred_name')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('order_amount', 10, 2)->default(0);
            $table->decimal('credit_earned', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('referral_code');
            $table->index('referred_email');
        });
    }

    public function down()
    {
        Schema::dropIfExists('referral_uses');
        Schema::dropIfExists('customer_referrals');
        Schema::dropIfExists('customer_achievements');
        Schema::dropIfExists('achievements');
    }
};