<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;
use App\Models\Lead;
use App\Models\EmailQueue;
use App\Models\EmailTemplate;

class EmailBroadcastService
{
    public function sendBroadcast(string $subject, string $body, array $recipientEmails, ?string $fromName = null, ?string $fromEmail = null): array
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $fromName = $fromName ?? ($settings['site_name'] ?? 'Joala Ventures');
        $fromEmail = $fromEmail ?? ($settings['email_from'] ?? 'noreply@joala.com.ng');

        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($recipientEmails as $email) {
            try {
                $this->sendSingleEmail($email, $subject, $body, $fromName, $fromEmail);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Failed to send to {$email}: " . $e->getMessage();
                Log::error("Broadcast email failed for {$email}: " . $e->getMessage());
            }
        }

        Log::info("Broadcast completed: {$sent} sent, {$failed} failed");

        return [
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function sendBroadcastViaQueue(string $subject, string $body, array $recipientEmails): int
    {
        $queued = 0;

        foreach ($recipientEmails as $email) {
            $lead = Lead::where('email', $email)->first();

            EmailQueue::create([
                'lead_id' => $lead?->id,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
                'scheduled_send_time' => now()->addSeconds($queued * 2),
            ]);

            $queued++;
        }

        Log::info("Queued {$queued} broadcast emails");

        return $queued;
    }

    public function sendToSegment(int $segmentId, string $subject, string $body): array
    {
        $leads = \DB::table('segment_leads')
            ->where('segment_id', $segmentId)
            ->join('leads', 'leads.id', '=', 'segment_leads.lead_id')
            ->where('leads.status', 'active')
            ->where('leads.confirmed', true)
            ->pluck('leads.email')
            ->toArray();

        return $this->sendBroadcast($subject, $body, $leads);
    }

    public function sendToNewsletterSubscribers(string $subject, string $body): array
    {
        $emails = Lead::where('is_newsletter', true)
            ->where('status', 'active')
            ->where('confirmed', true)
            ->pluck('email')
            ->toArray();

        return $this->sendBroadcast($subject, $body, $emails);
    }

    private function sendSingleEmail(string $to, string $subject, string $body, string $fromName, string $fromEmail): void
    {
        $processedBody = $this->processTemplate($body, $to);
        $processedSubject = $this->processSubject($subject, $to);

        $unsubscribeUrl = url('/newsletter/unsubscribe?email=' . urlencode($to));
        $processedBody = str_replace('{{unsubscribe_url}}', $unsubscribeUrl, $processedBody);

        if (!str_contains($processedBody, 'unsubscribe')) {
            $processedBody .= '<p style="text-align:center;font-size:12px;color:#999;margin-top:30px;">
                <a href="' . $unsubscribeUrl . '" style="color:#999;">Unsubscribe</a>
            </p>';
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "List-Unsubscribe: <{$unsubscribeUrl}>\r\n";

        $sent = @mail($to, $processedSubject, $processedBody, $headers);

        if (!$sent) {
            throw new \RuntimeException("mail() function returned false for {$to}");
        }
    }

    private function processTemplate(string $body, string $email): string
    {
        $lead = Lead::where('email', $email)->first();

        $replacements = [
            '{{name}}' => $lead?->name ?? explode('@', $email)[0],
            '{{email}}' => $email,
            '{{site_url}}' => url('/'),
            '{{site_name}}' => Setting::get('site_name') ?? 'Joala Ventures',
            '{{year}}' => date('Y'),
            '{{date}}' => date('F j, Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }

    private function processSubject(string $subject, string $email): string
    {
        $lead = Lead::where('email', $email)->first();
        return str_replace('{{name}}', $lead?->name ?? explode('@', $email)[0], $subject);
    }
}