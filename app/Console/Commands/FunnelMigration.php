<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FunnelMigration extends Command
{
    protected $signature = 'funnel:migrate';
    protected $description = 'Run funnel enhancements migration';

    public function handle()
    {
        $host = 'localhost';
        $dbname = 'joalacom_joala';
        $user = 'joalacom_joala';
        $pass = 'joala@2025@';
        
        try {
            $pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $this->info("Connected to database.");
            
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            if (!in_array('funnel_leads', $tables)) {
                $this->info("Creating funnel_leads table...");
                $pdo->exec("CREATE TABLE IF NOT EXISTS `funnel_leads` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `funnel_id` bigint(20) UNSIGNED NULL,
                    `lead_id` bigint(20) UNSIGNED NULL,
                    `stage_id` bigint(20) UNSIGNED NULL,
                    `email` varchar(255) NULL,
                    `source` varchar(255) NULL,
                    `converted` tinyint(1) DEFAULT 0,
                    `entered_at` datetime NULL,
                    `exited_at` datetime NULL,
                    `score` int DEFAULT 0,
                    `last_activity` datetime NULL,
                    `times_visited` int DEFAULT 0,
                    `pages_viewed` int DEFAULT 0,
                    `email_opens` int DEFAULT 0,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $this->info("Created funnel_leads table.");
            }
            
            $funnelColumns = [
                'order_bumps' => 'JSON NULL',
                'refund_policy' => "VARCHAR(50) DEFAULT 'days'",
                'refund_period_days' => 'INT DEFAULT 30',
                'affiliate_enabled' => 'TINYINT(1) DEFAULT 0',
                'affiliate_commission' => 'DECIMAL(5,2) DEFAULT 20.00',
                'affiliate_cookie_days' => 'INT DEFAULT 30',
                'score_per_page' => 'INT DEFAULT 5',
                'score_per_email' => 'INT DEFAULT 10',
                'score_per_checkout' => 'INT DEFAULT 20',
                'score_hot_threshold' => 'INT DEFAULT 100',
            ];
            
            $this->info("Adding columns to funnels table...");
            foreach ($funnelColumns as $col => $def) {
                try {
                    $pdo->exec("ALTER TABLE `funnels` ADD COLUMN `$col` $def");
                    $this->info("Added $col");
                } catch (\PDOException $e) {
                    if (str_contains($e->getMessage(), 'Duplicate')) {
                        $this->line("Column $col already exists, skipping.");
                    }
                }
            }
            
            $leadColumns = [
                'score' => 'INT DEFAULT 0',
                'last_activity' => 'DATETIME NULL',
                'times_visited' => 'INT DEFAULT 0',
                'pages_viewed' => 'INT DEFAULT 0',
                'email_opens' => 'INT DEFAULT 0',
            ];
            
            $this->info("Adding columns to funnel_leads table...");
            foreach ($leadColumns as $col => $def) {
                try {
                    $pdo->exec("ALTER TABLE `funnel_leads` ADD COLUMN `$col` $def");
                    $this->info("Added $col");
                } catch (\PDOException $e) {
                    if (str_contains($e->getMessage(), 'Duplicate')) {
                        $this->line("Column $col already exists, skipping.");
                    }
                }
            }
            
            $this->info("Migration complete!");
            
        } catch (\PDOException $e) {
            $this->error("Database error: " . $e->getMessage());
        }
        
        return 0;
    }
}