<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Secure API - plans (catalog; prices in NGN)
        Schema::create('secure_api_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();
            $table->string('name');
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->bigInteger('quota_monthly')->default(100);       // -1 = unlimited
            $table->integer('rate_rpm')->default(60);
            $table->integer('service_accounts')->default(3);         // -1 = unlimited
            $table->json('features')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('trial_days')->default(0);
            $table->integer('grace_days')->default(0);
            $table->timestamps();
        });

        // Secure API - licenses (mirrors the WP plugin schema)
        Schema::create('secure_api_licenses', function (Blueprint $table) {
            $table->id();
            $table->char('license_hash', 64)->unique();
            $table->char('key_suffix', 8);
            $table->string('type', 20)->default('subscription');     // subscription|lifetime|agency|enterprise|client
            $table->string('plan', 20)->default('pro');
            $table->string('status', 20)->default('active');         // active|suspended|expired|revoked
            $table->string('domain')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_activations')->default(1);          // 0 = unlimited
            $table->integer('activations_count')->default(0);
            $table->json('meta')->nullable();                        // {subject, email}
            $table->timestamps();
            $table->index('status');
            $table->index('key_suffix');
        });

        // Secure API - agency client links
        Schema::create('secure_api_agency_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id')->unique();
            $table->unsignedBigInteger('parent_license_id')->index();
            $table->string('email')->default('');
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        // Secure API - webhook idempotency ledger
        Schema::create('secure_api_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 20);
            $table->string('event_id', 191);
            $table->string('event_type', 100)->default('');
            $table->string('subject', 191)->nullable();
            $table->string('status', 20)->default('received');       // received|processed|failed
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'event_id']);
            $table->index('status');
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secure_api_webhook_events');
        Schema::dropIfExists('secure_api_agency_clients');
        Schema::dropIfExists('secure_api_licenses');
        Schema::dropIfExists('secure_api_plans');
    }
};
