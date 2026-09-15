<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secure_api_plans', function (Blueprint $table) {
            // Admin-only RPM override (null = use the plan default).
            $table->integer('rpm_override')->nullable()->after('rate_rpm');
        });
    }

    public function down(): void
    {
        Schema::table('secure_api_plans', function (Blueprint $table) {
            $table->dropColumn('rpm_override');
        });
    }
};
