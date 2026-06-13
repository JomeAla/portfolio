<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('landing_pages', 'funnel_id')) {
                $table->unsignedBigInteger('funnel_id')->nullable()->after('sequence_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            if (Schema::hasColumn('landing_pages', 'funnel_id')) {
                $table->dropForeign(['funnel_id']);
                $table->dropColumn('funnel_id');
            }
        });
    }
};