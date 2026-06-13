<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_stages', function (Blueprint $table) {
            if (!Schema::hasColumn('funnel_stages', 'sequence_id')) {
                $table->unsignedBigInteger('sequence_id')->nullable()->after('delay_days');
            }
            if (!Schema::hasColumn('funnel_stages', 'email_template')) {
                $table->text('email_template')->nullable()->after('sequence_id');
            }
            if (!Schema::hasColumn('funnel_stages', 'delay_hours')) {
                $table->integer('delay_hours')->default(0)->after('email_template');
            }
            if (!Schema::hasColumn('funnel_stages', 'condition_type')) {
                $table->string('condition_type')->nullable()->after('delay_hours');
            }
            if (!Schema::hasColumn('funnel_stages', 'condition_value')) {
                $table->json('condition_value')->nullable()->after('condition_type');
            }
            if (!Schema::hasColumn('funnel_stages', 'is_skippable')) {
                $table->boolean('is_skippable')->default(false)->after('condition_value');
            }
            if (!Schema::hasColumn('funnel_stages', 'action_on_complete')) {
                $table->string('action_on_complete')->default('advance')->after('is_skippable');
            }
            if (!Schema::hasColumn('funnel_stages', 'action_config')) {
                $table->json('action_config')->nullable()->after('action_on_complete');
            }
            if (!Schema::hasColumn('funnel_stages', 'points_to_award')) {
                $table->integer('points_to_award')->default(0)->after('action_config');
            }
            if (!Schema::hasColumn('funnel_stages', 'wait_duration_hours')) {
                $table->integer('wait_duration_hours')->default(0)->after('points_to_award');
            }
            if (!Schema::hasColumn('funnel_stages', 'wait_until_type')) {
                $table->string('wait_until_type')->nullable()->after('wait_duration_hours');
            }
            if (!Schema::hasColumn('funnel_stages', 'wait_until_value')) {
                $table->json('wait_until_value')->nullable()->after('wait_until_type');
            }
            if (!Schema::hasColumn('funnel_stages', 'redirect_type')) {
                $table->string('redirect_type')->nullable()->after('wait_until_value');
            }
            if (!Schema::hasColumn('funnel_stages', 'conditional_stages')) {
                $table->json('conditional_stages')->nullable()->after('redirect_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnel_stages', function (Blueprint $table) {
            $columns = [
                'sequence_id',
                'email_template',
                'delay_hours',
                'condition_type',
                'condition_value',
                'is_skippable',
                'action_on_complete',
                'action_config',
                'points_to_award',
                'wait_duration_hours',
                'wait_until_type',
                'wait_until_value',
                'redirect_type',
                'conditional_stages',
            ];
            $existingColumns = array_filter($columns, fn($col) => Schema::hasColumn('funnel_stages', $col));
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};