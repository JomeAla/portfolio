<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('automation_rules')) {
            Schema::create('automation_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('trigger_type');
                $table->string('trigger_value')->nullable();
                $table->string('action_type');
                $table->unsignedBigInteger('action_sequence_id')->nullable();
                $table->foreign('action_sequence_id')->references('id')->on('email_sequences')->onDelete('set null');
                $table->json('action_config')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('times_triggered')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};