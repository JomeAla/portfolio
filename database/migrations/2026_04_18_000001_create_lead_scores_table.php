<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lead_scores')) {
            Schema::create('lead_scores', function ($table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->string('event_type');
                $table->integer('points')->default(0);
                $table->string('description')->nullable();
                $table->timestamps();
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            });
        }
        
        if (!Schema::hasColumn('leads', 'lead_score')) {
            Schema::table('leads', function ($table) {
                $table->integer('lead_score')->default(0)->after('score');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leads', 'lead_score')) {
            Schema::table('leads', function ($table) {
                $table->dropColumn('lead_score');
            });
        }
        Schema::dropIfExists('lead_scores');
    }
};