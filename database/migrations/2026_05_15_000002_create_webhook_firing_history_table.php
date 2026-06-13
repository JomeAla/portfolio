<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_firing_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('automation_rule_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->string('event_type', 100);
            $table->string('webhook_url', 500);
            $table->text('payload')->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->string('status', 50)->default('success');
            $table->text('error_message')->nullable();
            $table->decimal('response_time_ms', 10, 2)->nullable();
            $table->timestamps();

            $table->index('automation_rule_id');
            $table->index('lead_id');
            $table->index('event_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_firing_history');
    }
};