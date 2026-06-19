<?php

namespace App\Console\Commands;

use App\Models\WhatsAppBroadcast;
use App\Services\WhatsAppBroadcastService;
use Illuminate\Console\Command;

class ProcessWhatsAppBroadcasts extends Command
{
    protected $signature = 'whatsapp:process-broadcasts';
    protected $description = 'Send scheduled WhatsApp broadcasts that are due';

    public function handle(WhatsAppBroadcastService $whatsapp): int
    {
        $due = WhatsAppBroadcast::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled broadcasts due.');
            return self::SUCCESS;
        }

        foreach ($due as $broadcast) {
            $this->info("Processing broadcast #{$broadcast->id}: {$broadcast->name}");
            $result = $whatsapp->sendToAllLeads($broadcast);
            $this->line("  Sent: {$result['sent']}, Failed: {$result['failed']}");
        }

        return self::SUCCESS;
    }
}
