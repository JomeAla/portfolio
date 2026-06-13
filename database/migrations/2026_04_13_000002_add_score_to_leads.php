<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leads', 'score')) {
            Schema::table('leads', function ($table) {
                $table->integer('score')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('leads', function ($table) {
            $table->dropColumn('score');
        });
    }
};