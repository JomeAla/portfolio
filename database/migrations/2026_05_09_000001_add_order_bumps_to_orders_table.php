<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_bumps')) {
                $table->json('order_bumps')->nullable()->after('checkout_abandoned_at');
            }
            if (!Schema::hasColumn('orders', 'order_bumps_total')) {
                $table->decimal('order_bumps_total', 10, 2)->nullable()->after('order_bumps');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('orders', 'order_bumps')) $columns[] = 'order_bumps';
            if (Schema::hasColumn('orders', 'order_bumps_total')) $columns[] = 'order_bumps_total';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};