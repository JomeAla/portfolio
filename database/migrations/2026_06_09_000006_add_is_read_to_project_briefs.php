<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('project_briefs', 'is_read')) {
            Schema::table('project_briefs', function (Blueprint $table) {
                $table->boolean('is_read')->default(false)->after('notes');
            });
        }
    }

    public function down(): void
    {
        Schema::table('project_briefs', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};
