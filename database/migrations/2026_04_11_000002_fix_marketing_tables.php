<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE leads ADD COLUMN status ENUM('active', 'unsubscribed') DEFAULT 'active'");
        } catch (\Exception $e) {
            // Column may already exist
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE leads DROP COLUMN status");
        } catch (\Exception $e) {
            // Ignore
        }
    }
};