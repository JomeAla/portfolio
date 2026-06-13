<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('landing_pages', 'countdown_end')) {
                $table->timestamp('countdown_end')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('landing_pages', 'countdown_message')) {
                $table->string('countdown_message')->nullable()->after('countdown_end');
            }
            if (!Schema::hasColumn('landing_pages', 'show_popup')) {
                $table->boolean('show_popup')->default(false)->after('countdown_message');
            }
            if (!Schema::hasColumn('landing_pages', 'popup_delay')) {
                $table->integer('popup_delay')->default(5)->after('show_popup');
            }
            if (!Schema::hasColumn('landing_pages', 'popup_title')) {
                $table->string('popup_title')->nullable()->after('popup_delay');
            }
            if (!Schema::hasColumn('landing_pages', 'popup_html')) {
                $table->text('popup_html')->nullable()->after('popup_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('landing_pages', 'countdown_end')) $columns[] = 'countdown_end';
            if (Schema::hasColumn('landing_pages', 'countdown_message')) $columns[] = 'countdown_message';
            if (Schema::hasColumn('landing_pages', 'show_popup')) $columns[] = 'show_popup';
            if (Schema::hasColumn('landing_pages', 'popup_delay')) $columns[] = 'popup_delay';
            if (Schema::hasColumn('landing_pages', 'popup_title')) $columns[] = 'popup_title';
            if (Schema::hasColumn('landing_pages', 'popup_html')) $columns[] = 'popup_html';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};