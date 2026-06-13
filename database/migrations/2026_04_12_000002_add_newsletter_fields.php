<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'is_newsletter')) {
                $table->boolean('is_newsletter')->default(false)->after('status');
            }
            if (!Schema::hasColumn('leads', 'confirmed')) {
                $table->boolean('confirmed')->default(false)->after('is_newsletter');
            }
            if (!Schema::hasColumn('leads', 'confirmation_token')) {
                $table->string('confirmation_token')->nullable()->unique()->after('confirmed');
            }
            if (!Schema::hasColumn('leads', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('confirmation_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('leads', 'is_newsletter')) $columns[] = 'is_newsletter';
            if (Schema::hasColumn('leads', 'confirmed')) $columns[] = 'confirmed';
            if (Schema::hasColumn('leads', 'confirmation_token')) $columns[] = 'confirmation_token';
            if (Schema::hasColumn('leads', 'confirmed_at')) $columns[] = 'confirmed_at';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};