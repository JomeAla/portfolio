<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait HandlesMailConfig
{
    /**
     * Dynamically apply mail configurations from the database.
     * This overrides the .env settings.
     */
    protected function applyMailConfig()
    {
        try {
            $settings = Setting::pluck('value', 'key');
            
            $mailer = $settings->get('mail_mailer', config('mail.default', 'smtp'));
            
            // Set the default mailer
            Config::set('mail.default', $mailer);
            
            // Apply mailer specific configuration if it's SMTP
            if ($mailer === 'smtp') {
                Config::set('mail.mailers.smtp', [
                    'transport' => 'smtp',
                    'host' => $settings->get('mail_host', config('mail.mailers.smtp.host')),
                    'port' => (int) $settings->get('mail_port', config('mail.mailers.smtp.port', 587)),
                    'encryption' => $settings->get('mail_encryption', config('mail.mailers.smtp.encryption')),
                    'username' => $settings->get('mail_username', config('mail.mailers.smtp.username')),
                    'password' => $settings->get('mail_password', config('mail.mailers.smtp.password')),
                    'timeout' => null,
                    'local_domain' => env('MAIL_EHLO_DOMAIN'),
                ]);
            }
            
            // Apply global "from" address and name
            Config::set('mail.from.address', $settings->get('mail_from_address', config('mail.from.address')));
            Config::set('mail.from.name', $settings->get('mail_from_name', config('mail.from.name')));
            
            Log::info('Mail configuration dynamically applied from settings. Mailer: ' . $mailer);
        } catch (\Exception $e) {
            Log::error('Failed to apply dynamic mail configuration: ' . $e->getMessage());
        }
    }
}
