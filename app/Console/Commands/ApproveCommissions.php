<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AffiliateCommissionService;

class ApproveCommissions extends Command
{
    protected $signature = 'affiliates:approve-commissions {--days=30 : Approve commissions for orders older than this many days}';
    protected $description = 'Auto-approve pending affiliate commissions after cooling period';

    public function handle()
    {
        $this->info('Starting commission approval process...');

        $service = app(AffiliateCommissionService::class);
        $days = (int)$this->option('days');

        $result = $service->approvePendingCommissions($days);

        $this->info("Approved: {$result['approved']}");
        $this->info("Skipped: {$result['skipped']}");

        $this->info('Commission approval complete.');

        return Command::SUCCESS;
    }
}