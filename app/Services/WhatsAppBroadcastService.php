<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppContact;
use Illuminate\Support\Facades\Log;

class WhatsAppBroadcastService
{
    public function sendBroadcast(WhatsAppBroadcast $broadcast, ?array $phoneNumbers = null): array
    {
        $broadcast->update(['status' => 'sending']);

        $contacts = $phoneNumbers
            ? WhatsAppContact::whereIn('phone', $phoneNumbers)->where('opted_in', true)->get()
            : WhatsAppContact::where('opted_in', true)->get();

        if ($contacts->isEmpty()) {
            $broadcast->update(['status' => 'failed', 'log' => [['error' => 'No opted-in contacts found']]]);
            return ['sent' => 0, 'failed' => 0, 'errors' => ['No opted-in contacts found']];
        }

        $broadcast->update(['total_recipients' => $contacts->count()]);

        $sent = 0;
        $failed = 0;
        $errors = [];
        $log = [];

        foreach ($contacts as $contact) {
            try {
                $this->sendSingleMessage($contact->phone, $broadcast->message, $contact->lead);
                $sent++;
                $contact->update(['last_sent_at' => now()]);
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Failed to send to {$contact->phone}: " . $e->getMessage();
                Log::error("WhatsApp broadcast failed for {$contact->phone}: " . $e->getMessage());
                $log[] = ['phone' => $contact->phone, 'error' => $e->getMessage()];
            }
        }

        $broadcast->update([
            'status' => $failed === 0 ? 'sent' : ($sent > 0 ? 'sent' : 'failed'),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'log' => $log,
        ]);

        Log::info("WhatsApp broadcast #{$broadcast->id} completed: {$sent} sent, {$failed} failed");

        return compact('sent', 'failed', 'errors');
    }

    public function sendToSegment(int $segmentId, WhatsAppBroadcast $broadcast): array
    {
        $leadIds = \DB::table('segment_leads')
            ->where('segment_id', $segmentId)
            ->pluck('lead_id')
            ->toArray();

        $phones = WhatsAppContact::whereIn('lead_id', $leadIds)
            ->where('opted_in', true)
            ->pluck('phone')
            ->toArray();

        if (empty($phones)) {
            return ['sent' => 0, 'failed' => 0, 'errors' => ['No opted-in contacts in this segment']];
        }

        return $this->sendBroadcast($broadcast, $phones);
    }

    public function sendToAllLeads(WhatsAppBroadcast $broadcast): array
    {
        $phones = WhatsAppContact::where('opted_in', true)->pluck('phone')->toArray();
        return $this->sendBroadcast($broadcast, $phones);
    }

    public function getOptedInCount(): int
    {
        return WhatsAppContact::where('opted_in', true)->count();
    }

    public function syncLeadPhone(int $leadId, string $phone): WhatsAppContact
    {
        return WhatsAppContact::updateOrCreate(
            ['lead_id' => $leadId],
            ['phone' => $phone, 'opted_in' => true]
        );
    }

    private function sendSingleMessage(string $phone, string $message, ?Lead $lead = null): void
    {
        $processedMessage = $this->processTemplate($message, $lead);

        $whatsappNumber = Setting::get('whatsapp_number', '');
        $apiEndpoint = Setting::get('whatsapp_api_endpoint', '');

        if (!empty($apiEndpoint)) {
            $this->sendViaApi($apiEndpoint, $phone, $processedMessage, $whatsappNumber);
        } else {
            $this->logSimulatedSend($phone, $processedMessage);
        }
    }

    private function sendViaApi(string $endpoint, string $phone, string $message, string $from): void
    {
        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $phone,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException("API returned HTTP {$httpCode}: {$response}");
        }
    }

    private function logSimulatedSend(string $phone, string $message): void
    {
        Log::info("[WhatsApp Simulated] To: {$phone} | Message: " . substr($message, 0, 100) . "...");
    }

    private function processTemplate(string $message, ?Lead $lead): string
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        $replacements = [
            '{{name}}' => $lead?->name ?? 'Valued Customer',
            '{{first_name}}' => $lead ? explode(' ', $lead->name ?? '')[0] : 'Valued Customer',
            '{{phone}}' => $lead?->phone ?? '',
            '{{email}}' => $lead?->email ?? '',
            '{{site_name}}' => $settings['site_name'] ?? 'Joala Ventures',
            '{{site_url}}' => url('/'),
            '{{year}}' => date('Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }
}
