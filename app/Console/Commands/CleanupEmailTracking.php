<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailQueue;
use App\Models\EmailOpen;
use Illuminate\Support\Facades\DB;

class CleanupEmailTracking extends Command
{
    protected $signature = 'email:cleanup {--days=30 : Number of days to keep}';
    protected $description = 'Clean up old email tracking records';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Cleaning up email tracking records older than {$days} days...");

        // Delete old email opens
        try {
            $deletedOpens = EmailOpen::where('opened_at', '<', $cutoffDate)->delete();
            $this->info("Deleted {$deletedOpens} old email open records.");
        } catch (\Exception $e) {
            $this->warn("Could not delete email opens: " . $e->getMessage());
        }

// Mark old sent emails as cleaned
        try {
            $cleaned = EmailQueue::where('status', 'sent')
                ->where('sent_at', '<', $cutoffDate)
                ->update(['status' => 'cleaned']);
            $this->info("Marked {$cleaned} old sent emails as cleaned.");
        } catch (\Exception $e) {
            $this->warn("Could not update email status: " . $e->getMessage());
        }

        // Delete failed emails older than 7 days
        $failedCutoff = now()->subDays(7);
        $deletedFailed = EmailQueue::where('status', 'failed')
            ->where('created_at', '<', $failedCutoff)
            ->delete();

        $this->info("Deleted {$deletedFailed} old failed email records.");

        $this->info("Cleanup complete!");
        return 0;
    }
}