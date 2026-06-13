<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('sequence_ids')->nullable();
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'lead_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'campaign_id')) {
                $table->unsignedBigInteger('campaign_id')->nullable()->after('sequence_id');
            }
            if (!Schema::hasColumn('leads', 'source')) {
                $table->string('source')->nullable();
            }
            if (!Schema::hasColumn('leads', 'enrolled_at')) {
                $table->timestamp('enrolled_at')->nullable();
            }
            if (Schema::hasColumn('leads', 'campaign_id')) {
                $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('leads', 'campaign_id')) {
                $table->dropForeign(['campaign_id']);
                $columns[] = 'campaign_id';
            }
            if (Schema::hasColumn('leads', 'source')) $columns[] = 'source';
            if (Schema::hasColumn('leads', 'enrolled_at')) $columns[] = 'enrolled_at';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
        Schema::dropIfExists('campaign_leads');
        Schema::dropIfExists('campaigns');
    }
};