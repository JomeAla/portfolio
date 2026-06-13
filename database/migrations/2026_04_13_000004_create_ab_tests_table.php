<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ab_tests')) {
            Schema::create('ab_tests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('subject_a');
                $table->string('subject_b');
                $table->longText('body_a')->nullable();
                $table->longText('body_b')->nullable();
                $table->unsignedBigInteger('sequence_step_id')->nullable();
                $table->foreign('sequence_step_id')->references('id')->on('sequence_steps')->onDelete('set null');
                $table->enum('status', ['draft', 'running', 'completed'])->default('draft');
                $table->enum('winner', ['a', 'b'])->nullable();
                $table->integer('sent_a')->default(0);
                $table->integer('sent_b')->default(0);
                $table->integer('opens_a')->default(0);
                $table->integer('opens_b')->default(0);
                $table->integer('clicks_a')->default(0);
                $table->integer('clicks_b')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_tests');
    }
};