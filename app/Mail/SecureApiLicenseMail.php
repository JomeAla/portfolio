<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * License delivery + payment-failed notifications for the WP Secure API
 * Gateway store (sent from the joala.com.ng issuer).
 */
class SecureApiLicenseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ?string $licenseKey,
        public string $plan,
        public float $amount,
        public string $reference = '',
    ) {
    }

    public function build(): static
    {
        if ($this->licenseKey === null) {
            if (str_starts_with($this->reference, 'expiring-')) {
                $days = (int) substr($this->reference, 9);

                return $this->subject("Your license expires in {$days} day(s) — renew now")
                    ->view('emails.secure-api-expiring', [
                        'days' => $days,
                        'plan' => ucfirst($this->plan),
                    ]);
            }

            return $this->subject('Payment failed — action needed')
                ->view('emails.secure-api-payment-failed', [
                    'reference' => $this->reference,
                ]);
        }

        return $this->subject('Your WP Secure API Gateway license key')
            ->view('emails.secure-api-license', [
                'key'    => $this->licenseKey,
                'plan'   => ucfirst($this->plan),
                'amount' => '₦' . number_format($this->amount, 2),
                'site'   => config('app.name', 'JoAla Ventures'),
                'url'    => config('app.url'),
            ]);
    }
}
