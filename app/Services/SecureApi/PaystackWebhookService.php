<?php

namespace App\Services\SecureApi;

use App\Models\SecureApiWebhookEvent;
use App\Models\Setting;
use App\Traits\HandlesMailConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecureApiLicenseMail;

/**
 * Paystack webhook handler for Secure API license sales.
 * HMAC-SHA512 verified upstream; idempotent per (provider, event_id).
 *
 * Metadata expected on charges: {plan, subject(email), affiliate_id, days}
 */
class PaystackWebhookService
{
    use HandlesMailConfig;

    public function __construct(
        private LicenseService $licenses,
    ) {
    }

    public function secretKey(): string
    {
        return Setting::get('paystack_secret_key') ?? config('services.paystack.secret', '');
    }

    /**
     * Apply the site's dynamic mail config when it exists; otherwise fall
     * back to the .env mailer (log in local/dev) so license emails never
     * crash the webhook on partially-configured environments.
     */
    protected function applyMailSettings(): void
    {
        if (Setting::get('mail_mailer') === null && Setting::get('mail_host') === null) {
            \Illuminate\Support\Facades\Config::set('mail.default', 'log');
            \Illuminate\Support\Facades\Config::set('mail.mailers.log', ['transport' => 'log']);

            return;
        }

        $this->applyMailSettings();
    }

    public function verify(string $signature, string $rawBody): bool
    {
        $secret = $this->secretKey();

        if ($secret === '' || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $rawBody, $secret), strtolower($signature));
    }

    /**
     * Process a verified Paystack event. Returns a result array.
     */
    public function handle(array $event, string $rawBody): array
    {
        $type = (string) ($event['event'] ?? '');
        $data = (array) ($event['data'] ?? []);
        $meta = (array) ($data['metadata'] ?? []);

        $eventId = $type . ':' . ((string) ($data['id'] ?? $data['subscription_code'] ?? $data['invoice_code'] ?? 'no-ref'));
        $subject = (string) ($meta['subject'] ?? $meta['email'] ?? '');
        $email   = (string) ($meta['email'] ?? $meta['subject'] ?? '');

        $ledger = SecureApiWebhookEvent::where('provider', 'paystack')
            ->where('event_id', $eventId)
            ->first();

        if ($ledger) {
            return ['status' => 'ok', 'message' => 'Duplicate event ignored'];
        }

        $record = SecureApiWebhookEvent::create([
            'provider'     => 'paystack',
            'event_id'     => $eventId,
            'event_type'   => $type,
            'subject'      => $subject !== '' ? $subject : null,
            'status'       => 'received',
            'processed_at' => now(),
        ]);

        try {
            switch ($type) {
                case 'charge.success':
                    $this->handleCharge($data, $meta);
                    break;

                case 'invoice.update':
                    $this->handleRenewal($data, $meta);
                    break;

                case 'subscription.disable':
                    $this->handleCancellation($data, $meta);
                    break;

                case 'subscription.enable':
                    $this->handleResubscribe($data, $meta);
                    break;

                case 'charge.failed':
                    $this->handleFailed($data, $meta);
                    break;

                default:
                    Log::info('Secure API: unhandled Paystack event acknowledged', ['event' => $type, 'event_id' => $eventId]);
            }

            $record->update(['status' => 'processed']);
        } catch (\Throwable $e) {
            $record->update(['status' => 'failed']);
            Log::error('Secure API webhook failed', ['event' => $type, 'error' => $e->getMessage()]);

            return ['status' => 'error', 'message' => 'Webhook processing failed'];
        }

        return ['status' => 'ok', 'message' => 'Webhook processed successfully'];
    }

    protected function handleCharge(array $data, array $meta): void
    {
        $email = (string) ($meta['email'] ?? $meta['subject'] ?? '');
        if ($email === '') {
            return;
        }

        $plan = (string) ($meta['plan'] ?? 'pro');
        $amount = ((int) ($data['amount'] ?? 0)) / 100;
        $days = (int) ($meta['days'] ?? $this->daysForPlan($plan));

        $this->applyMailSettings();

        if ($plan === 'lifetime') {
            $result = $this->licenses->issue($email, 'pro', 'lifetime', 0, 1);
        } else {
            $result = $this->licenses->issueOrReuse($email, $plan, $days);
        }

        if (!empty($result['key'])) {
            Mail::to($email)->queue(new SecureApiLicenseMail($result['key'], $result['plan'], $amount));
        } elseif ($result['reused'] ?? false) {
            Log::info('Secure API: license reused for returning customer', ['email' => $email, 'plan' => $plan]);
        }

        // Wire the sale into JoAla's existing affiliate system (referral
        // code travels in the checkout metadata as affiliate_id).
        $affiliateCode = (string) ($meta['affiliate_id'] ?? $meta['referral_code'] ?? '');

        if ($affiliateCode !== '') {
            try {
                app(\App\Services\AffiliateCommissionService::class)
                    ->processReferral($affiliateCode, $email, null, $amount);
                Log::info('Secure API: affiliate commission recorded', [
                    'code' => $affiliateCode, 'email' => $email, 'amount' => $amount,
                ]);
            } catch (\Throwable $e) {
                Log::error('Secure API: affiliate commission failed', ['error' => $e->getMessage()]);
            }
        }

        Log::info('Secure API: license provisioned', [
            'email' => $email, 'plan' => $plan, 'amount' => $amount,
            'affiliate_id' => $affiliateCode !== '' ? $affiliateCode : null,
        ]);
    }

    protected function handleRenewal(array $data, array $meta): void
    {
        $email = (string) ($meta['email'] ?? $meta['subject'] ?? '');
        if ($email === '') {
            return;
        }

        $plan = (string) ($meta['plan'] ?? 'pro');
        $this->licenses->renewForEmail($email, $plan, $this->daysForPlan($plan));

        Log::info('Secure API: renewal applied', ['email' => $email, 'plan' => $plan]);
    }

    protected function handleCancellation(array $data, array $meta): void
    {
        $email = (string) ($meta['email'] ?? $meta['subject'] ?? '');
        if ($email === '') {
            return;
        }

        $this->licenses->suspendForEmail($email);
        Log::info('Secure API: subscription cancelled - license suspended', ['email' => $email]);
    }

    protected function handleResubscribe(array $data, array $meta): void
    {
        $email = (string) ($meta['email'] ?? $meta['subject'] ?? '');
        if ($email === '') {
            return;
        }

        $plan = (string) ($meta['plan'] ?? 'pro');
        $this->licenses->renewForEmail($email, $plan, $this->daysForPlan($plan));
        Log::info('Secure API: subscription re-enabled', ['email' => $email]);
    }

    protected function handleFailed(array $data, array $meta): void
    {
        $email = (string) ($meta['email'] ?? $meta['subject'] ?? '');
        if ($email === '') {
            return;
        }

        $this->applyMailSettings();
        Mail::to($email)->queue(new SecureApiLicenseMail(null, 'failed', 0, (string) ($data['reference'] ?? '')));

        Log::warning('Secure API: charge failed', ['email' => $email, 'reference' => $data['reference'] ?? null]);
    }

    protected function daysForPlan(string $plan): int
    {
        return match ($plan) {
            'agency'     => 31,
            'enterprise' => 31,
            'lifetime'   => 0,
            default      => 31, // pro: monthly billing
        };
    }
}
