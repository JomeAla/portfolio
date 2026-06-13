<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lead_activities')) {
            Schema::create('lead_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->string('type');
                $table->text('description');
                $table->json('metadata')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('deals')) {
            Schema::create('deals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->string('title');
                $table->decimal('value', 10, 2)->default(0);
                $table->string('stage')->default('lead');
                $table->integer('probability')->default(10);
                $table->date('expected_close_date')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamps();
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('lead_tasks')) {
            Schema::create('lead_tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('due_date')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->string('status')->default('pending');
                $table->string('priority')->default('medium');
                $table->timestamps();
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_tasks');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('lead_activities');
    }
};