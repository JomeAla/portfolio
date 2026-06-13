<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnels', function (Blueprint $table) {
            if (!Schema::hasColumn('funnels', 'environment')) {
                $table->enum('environment', ['staging', 'production'])->default('staging')->after('is_active');
            }
            if (!Schema::hasColumn('funnels', 'deployed_at')) {
                $table->timestamp('deployed_at')->nullable()->after('environment');
            }
            if (!Schema::hasColumn('funnels', 'deployment_history')) {
                $table->json('deployment_history')->nullable()->after('deployed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnels', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('funnels', 'environment')) $columns[] = 'environment';
            if (Schema::hasColumn('funnels', 'deployed_at')) $columns[] = 'deployed_at';
            if (Schema::hasColumn('funnels', 'deployment_history')) $columns[] = 'deployment_history';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};