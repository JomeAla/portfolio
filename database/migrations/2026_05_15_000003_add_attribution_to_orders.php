<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable()->after('coupon_code');
            $table->unsignedBigInteger('lead_id')->nullable()->after('campaign_id');
            $table->decimal('lead_attribution_score', 8, 2)->nullable()->after('lead_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['campaign_id', 'lead_id', 'lead_attribution_score']);
        });
    }
};