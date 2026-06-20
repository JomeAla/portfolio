<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $pdo = DB::connection()->getPdo();

        try {
            $pdo->exec("ALTER TABLE membership_tiers ADD COLUMN discount_percent DECIMAL(5,2) DEFAULT 0 AFTER features");
            echo "Added discount_percent to membership_tiers\n";
        } catch (\Exception $e) {
            echo "discount_percent column may already exist: " . $e->getMessage() . "\n";
        }

        try {
            $pdo->exec("ALTER TABLE courses ADD COLUMN required_tier_id INT DEFAULT NULL AFTER is_published");
            echo "Added required_tier_id to courses\n";
        } catch (\Exception $e) {
            echo "required_tier_id column may already exist: " . $e->getMessage() . "\n";
        }

        // Update existing tiers with correct data
        try {
            $pdo->exec("UPDATE membership_tiers SET discount_percent = 5, price = 5000, billing_period = 'monthly' WHERE LOWER(name) LIKE '%basic%'");
            $pdo->exec("UPDATE membership_tiers SET discount_percent = 10, price = 15000, billing_period = 'monthly' WHERE LOWER(name) LIKE '%pro%'");
            $pdo->exec("UPDATE membership_tiers SET discount_percent = 20, price = 50000, billing_period = 'monthly' WHERE LOWER(name) LIKE '%vip%'");
            echo "Updated existing tiers with discounts and prices\n";
        } catch (\Exception $e) {
            echo "Could not update tiers: " . $e->getMessage() . "\n";
        }
    }

    public function down()
    {
        $pdo = DB::connection()->getPdo();
        try { $pdo->exec("ALTER TABLE membership_tiers DROP COLUMN discount_percent"); } catch (\Exception $e) {}
        try { $pdo->exec("ALTER TABLE courses DROP COLUMN required_tier_id"); } catch (\Exception $e) {}
    }
};
