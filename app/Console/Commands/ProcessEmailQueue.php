<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailQueue;
use App\Models\Lead;
use App\Models\SequenceStep;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Services\EmailFormatterService;

class ProcessEmailQueue extends Command
{
    protected $signature = 'email:process {--limit=50 : Maximum emails to process}';
    protected $description = 'Process pending emails in the queue via Brevo API';

    private function wrapLinksForTracking($html, $emailQueueId)
    {
        return preg_replace_callback('/href=["\']([^"\']+)["\']/i', function ($matches) use ($emailQueueId) {
            $originalUrl = $matches[1];
            $scheme = parse_url($originalUrl, PHP_URL_SCHEME);
            if (in_array($scheme, ['http', 'https']) && !str_contains($originalUrl, '/click/') && !str_contains($originalUrl, '/mc/')) {
                $encodedUrl = urlencode($originalUrl);
                return 'href="' . url('/click/' . $emailQueueId . '?url=' . $encodedUrl) . '"';
            }
            return $matches[0];
        }, $html);
    }

    private function processTemplate($template, $lead)
    {
        $formatter = app(EmailFormatterService::class);
        return $formatter->formatEmailBody($template, [
            'name' => (!empty($lead->name) ? $lead->name : explode('@', $lead->email)[0]),
            'email' => $lead->email,
        ]);
    }

    public function handle()
    {
        $apiKey = Setting::get('brevo_api_key');
        if (empty($apiKey)) {
            $this->error('Brevo API key not configured. Set it in admin settings.');
            return 1;
        }

        $fromEmail = Setting::get('mail_from_address', 'campaigns@joala.com.ng');
        $fromName = Setting::get('mail_from_name', 'JoAla');

        $limit = (int) $this->option('limit');

        $emails = EmailQueue::where('status', 'pending')
            ->where('scheduled_send_time', '<=', now())
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        $this->info("Processing {$emails->count()} emails...");

        foreach ($emails as $email) {
            try {
                $lead = Lead::find($email->lead_id);

                if (!$lead) {
                    $email->update(['status' => 'failed', 'error_message' => 'Lead not found']);
                    continue;
                }

                $step = SequenceStep::find($email->sequence_step_id);
                if (!$step) {
                    $email->update(['status' => 'failed', 'error_message' => 'Step not found']);
                    continue;
                }

                $subject = $step->subject ?? $email->subject;
                $body = $this->processTemplate($step->body, $lead);
                $body = $this->wrapLinksForTracking($body, $email->id);
                $body .= '<img src="' . url('/m/' . $email->id) . '" width="1" height="1" style="display:none;" alt="">';

                $this->info("Sending email to {$lead->email}...");

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "accept: application/json",
                    "api-key: $apiKey",
                    "content-type: application/json",
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    "sender" => ["name" => $fromName, "email" => $fromEmail],
                    "to" => [["email" => $lead->email, "name" => (!empty($lead->name) ? $lead->name : explode('@', $lead->email)[0])]],
                    "subject" => $subject,
                    "htmlContent" => $body,
                ]));
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 201 || $httpCode === 200) {
                    $email->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    $this->info("Email sent to {$lead->email}");
                } else {
                    throw new \Exception("Brevo API returned HTTP $httpCode: $response");
                }

            } catch (\Exception $e) {
                $this->error("Failed to send email {$email->id}: " . $e->getMessage());

                Log::error('Email send failed', [
                    'email_id' => $email->id,
                    'lead_id' => $email->lead_id,
                    'error' => $e->getMessage(),
                ]);

                $email->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Email processing complete!");

        return 0;
    }
}
