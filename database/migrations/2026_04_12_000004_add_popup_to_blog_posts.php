<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'show_popup')) {
                $table->boolean('show_popup')->default(false)->after('is_published');
            }
            if (!Schema::hasColumn('blog_posts', 'popup_delay')) {
                $table->integer('popup_delay')->default(10)->after('show_popup');
            }
            if (!Schema::hasColumn('blog_posts', 'popup_title')) {
                $table->string('popup_title')->nullable()->after('popup_delay');
            }
            if (!Schema::hasColumn('blog_posts', 'popup_html')) {
                $table->text('popup_html')->nullable()->after('popup_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('blog_posts', 'show_popup')) $columns[] = 'show_popup';
            if (Schema::hasColumn('blog_posts', 'popup_delay')) $columns[] = 'popup_delay';
            if (Schema::hasColumn('blog_posts', 'popup_title')) $columns[] = 'popup_title';
            if (Schema::hasColumn('blog_posts', 'popup_html')) $columns[] = 'popup_html';
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};