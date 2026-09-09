<?php

namespace App\Console\Commands;

use App\Mail\SecureApiLicenseMail;
use App\Services\SecureApi\LicenseService;
use App\Traits\HandlesMailConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SecureApiExpiryReminders extends Command
{
    use HandlesMailConfig;

    protected $signature = 'secure-api:expiry-reminders';

    protected $description = 'Email customers whose Secure API licenses expire in 7/3/1 days';

    protected function applyMailSettings(): void
    {
        if (\App\Models\Setting::get('mail_mailer') === null && \App\Models\Setting::get('mail_host') === null) {
            \Illuminate\Support\Facades\Config::set('mail.default', 'log');
            \Illuminate\Support\Facades\Config::set('mail.mailers.log', ['transport' => 'log']);
            return;
        }
        $this->applyMailConfig();
    }

    public function handle(LicenseService $licenses): int
    {
        $this->applyMailSettings();
        $sent = 0;

        foreach ($licenses->expiringSoon() as $license) {
            $days = (int) ceil(now()->diffInSeconds($license->expires_at, false) / 86400);
            $email = (string) ($license->meta['email'] ?? '');
            if ($email === '') {
                continue;
            }

            // One email per license per milestone (cache flag, 2 days).
            $flag = "secure_api_expiring_{$license->id}_{$days}";
            if (Cache::get($flag)) {
                continue;
            }

            Mail::to($email)->queue(new SecureApiLicenseMail(
                null,
                $license->plan,
                0,
                "expiring-{$days}"
            ));

            Cache::put($flag, true, now()->addDays(2));
            $sent++;
        }

        $this->info("Expiry reminders queued: {$sent}");

        return self::SUCCESS;
    }
}
