<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'funnel_id')) {
                $table->unsignedBigInteger('funnel_id')->nullable()->after('popup_html');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'funnel_id')) {
                $table->dropForeign(['funnel_id']);
                $table->dropColumn('funnel_id');
            }
        });
    }
};