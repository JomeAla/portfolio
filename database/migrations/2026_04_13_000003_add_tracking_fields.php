<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('email_queue', 'opened')) {
            Schema::table('email_queue', function ($table) {
                $table->boolean('opened')->default(false)->after('status');
                $table->boolean('clicked')->default(false)->after('opened');
                $table->string('clicked_url')->nullable()->after('clicked');
            });
        }
        
        if (!Schema::hasColumn('email_opens', 'lead_id')) {
            Schema::table('email_opens', function ($table) {
                $table->unsignedBigInteger('lead_id')->nullable()->after('email_queue_id');
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('email_opens', function ($table) {
            $table->dropForeign(['lead_id']);
            $table->dropColumn('lead_id');
        });
        
        Schema::table('email_queue', function ($table) {
            $table->dropColumn('clicked_url', 'clicked', 'opened');
        });
    }
};