<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CartAbandonmentService;

class ProcessCartAbandonment extends Command
{
    protected $signature = 'cart:process-abandonment {--hours=1 : Hours threshold for cart abandonment} {--checkout-hours=30 : Minutes threshold for checkout abandonment}';
    protected $description = 'Detect abandoned carts and checkouts, enroll in recovery sequence';

    public function handle()
    {
        $this->info('Starting cart abandonment processing...');

        $service = app(CartAbandonmentService::class);

        $cartHours = (int)$this->option('hours');
        $checkoutMinutes = (int)$this->option('checkout-hours');

        $this->line("Checking carts older than {$cartHours} hour(s)...");
        $cartResult = $service->detectAbandonedCarts($cartHours);
        $this->info("Carts marked as abandoned: {$cartResult['marked']}");

        $this->line("Checking checkouts older than {$checkoutMinutes} minute(s)...");
        $checkoutResult = $service->detectAbandonedCheckouts($checkoutMinutes);
        $this->info("Checkouts marked as abandoned: {$checkoutResult['marked']}");

        $this->info('Cart abandonment processing complete.');

        return Command::SUCCESS;
    }
}