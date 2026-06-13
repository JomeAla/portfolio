<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('segment_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('segment_id');
            $table->unsignedBigInteger('lead_id');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['segment_id', 'lead_id']);
            $table->index('lead_id');
            $table->index('segment_id');

            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('cascade');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('segment_leads');
    }
};