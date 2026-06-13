<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\Marketing\MarketingService;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
$schedule->command('email:process --limit=50')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('tweets:process')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('automation:execute')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('leads:score --all')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('email:cleanup --days=30')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('abtests:process')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('funnel:process-stages --limit=500')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('segments:sync')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('cart:process-abandonment')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('affiliates:approve-commissions --days=30')->daily()->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
