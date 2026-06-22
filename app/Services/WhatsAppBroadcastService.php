<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\WhatsAppBroadcast;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppConversationLog;
use App\Models\WhatsAppFlow;
use App\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\Log;

class WhatsAppBroadcastService
{
    protected ?string $apiEndpoint;
    protected ?string $apiToken;
    protected ?string $phoneNumberId;

    public function __construct()
    {
        $this->apiEndpoint = Setting::get('whatsapp_api_endpoint', '');
        $this->apiToken = Setting::get('whatsapp_api_token', '');
        $this->phoneNumberId = Setting::get('whatsapp_number', '');
    }

    // ─── Broadcast Methods ───────────────────────────────────────────

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

        $payload = $broadcast->payload ?: $this->buildTextPayload($broadcast->message);
        $isSimulated = empty($this->apiEndpoint);

        foreach ($contacts as $contact) {
            try {
                $this->sendPayload($contact->phone, $payload, $contact->lead);
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

        Log::info("WhatsApp broadcast #{$broadcast->id} completed: {$sent} sent, {$failed} failed" . ($isSimulated ? ' (SIMULATED)' : ''));

        if ($isSimulated) {
            $errors[] = 'WhatsApp API endpoint is not configured. Messages were simulated (logged only).';
        }

        return compact('sent', 'failed', 'errors');
    }

    public function sendToSegment(?int $segmentId, WhatsAppBroadcast $broadcast): array
    {
        if (!$segmentId) {
            return ['sent' => 0, 'failed' => 0, 'errors' => ['No segment selected.']];
        }
        $leadIds = \DB::table('segment_leads')->where('segment_id', $segmentId)->pluck('lead_id')->toArray();
        $phones = WhatsAppContact::whereIn('lead_id', $leadIds)->where('opted_in', true)->pluck('phone')->toArray();
        if (empty($phones)) return ['sent' => 0, 'failed' => 0, 'errors' => ['No opted-in contacts in this segment']];
        return $this->sendBroadcast($broadcast, $phones);
    }

    public function sendToAllLeads(WhatsAppBroadcast $broadcast): array
    {
        $phones = WhatsAppContact::where('opted_in', true)->pluck('phone')->toArray();
        Log::info("WhatsApp sendToAllLeads: found " . count($phones) . " opted-in contacts");
        return $this->sendBroadcast($broadcast, $phones);
    }

    public function sendToGroup(WhatsAppBroadcast $broadcast): array
    {
        $groupJid = $broadcast->group_jid;
        if (empty($groupJid)) {
            return ['sent' => 0, 'failed' => 1, 'errors' => ['No group JID set on broadcast']];
        }

        $broadcast->update(['status' => 'sending', 'total_recipients' => 1]);

        $payload = $broadcast->payload ?: $this->buildTextPayload($broadcast->message);

        try {
            $this->sendPayload($groupJid, $payload);
            $broadcast->update(['status' => 'sent', 'sent_count' => 1, 'failed_count' => 0]);
            return ['sent' => 1, 'failed' => 0, 'errors' => []];
        } catch (\Exception $e) {
            $broadcast->update(['status' => 'failed', 'sent_count' => 0, 'failed_count' => 1, 'log' => [['error' => $e->getMessage()]]]);
            return ['sent' => 0, 'failed' => 1, 'errors' => [$e->getMessage()]];
        }
    }

    // ─── Template-Based Sending ──────────────────────────────────────

    public function sendTemplate(string $phone, WhatsAppTemplate $template, ?Lead $lead = null): void
    {
        $payload = $this->buildTemplatePayload($template, $lead);
        $this->sendPayload($phone, $payload, $lead);
    }

    public function sendTemplateBroadcast(WhatsAppTemplate $template, ?array $phoneNumbers = null): array
    {
        $contacts = $phoneNumbers
            ? WhatsAppContact::whereIn('phone', $phoneNumbers)->where('opted_in', true)->get()
            : WhatsAppContact::where('opted_in', true)->get();

        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($contacts as $contact) {
            try {
                $this->sendTemplate($contact->phone, $template, $contact->lead);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Failed to send template to {$contact->phone}: " . $e->getMessage();
            }
        }

        return compact('sent', 'failed', 'errors');
    }

    // ─── Flow Sending ────────────────────────────────────────────────

    public function sendFlow(string $phone, WhatsAppFlow $flow, ?Lead $lead = null): void
    {
        $token = $flow->flow_id;
        $payload = [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'flow',
                'body' => ['text' => $flow->description ?? 'Complete this form'],
                'action' => [
                    'name' => 'flow',
                    'parameters' => [
                        'flow_message_version' => '3',
                        'flow_token' => $token ?: 'flow_' . $flow->id,
                        'flow_id' => $token,
                        'flow_cta' => 'Open Form',
                        'flow_action' => 'navigate',
                        'flow_action_payload' => [
                            'screen' => 'FORM',
                            'data' => $flow->flow_data ?? new \stdClass,
                        ],
                    ],
                ],
            ],
        ];

        $this->sendRawPayload($phone, $payload, $lead);
    }

    // ─── Multi-Step Conversations ────────────────────────────────────

    public function startConversation(WhatsAppConversation $conv, WhatsAppContact $contact): void
    {
        WhatsAppConversationLog::updateOrCreate(
            ['conversation_id' => $conv->id, 'contact_id' => $contact->id],
            ['current_step' => 0, 'status' => 'active', 'last_step_at' => now()]
        );

        $this->processConversationStep($conv, $contact, 0);
    }

    public function processConversationStep(WhatsAppConversation $conv, WhatsAppContact $contact, int $stepIndex): void
    {
        $steps = $conv->steps ?? [];

        if (!isset($steps[$stepIndex])) {
            $log = WhatsAppConversationLog::where('conversation_id', $conv->id)
                ->where('contact_id', $contact->id)->first();
            if ($log) $log->update(['status' => 'completed']);
            return;
        }

        $step = $steps[$stepIndex];
        $lead = $contact->lead;

        if (isset($step['template_id'])) {
            $template = WhatsAppTemplate::find($step['template_id']);
            if ($template) {
                $payload = $this->buildTemplatePayload($template, $lead);
                $this->sendPayload($contact->phone, $payload, $lead);
            }
        } elseif (isset($step['message'])) {
            $processed = $this->processPlaceholders($step['message'], $lead);
            $payload = $this->buildTextPayload($processed);
            $this->sendPayload($contact->phone, $payload, $lead);
        }

        $log = WhatsAppConversationLog::where('conversation_id', $conv->id)
            ->where('contact_id', $contact->id)->first();
        if ($log) {
            $log->update(['current_step' => $stepIndex, 'last_step_at' => now()]);
        }
    }

    public function handleReply(WhatsAppContact $contact, string $reply, string $conversationId = null): ?array
    {
        if ($conversationId) {
            $log = WhatsAppConversationLog::where('conversation_id', $conversationId)
                ->where('contact_id', $contact->id)->where('status', 'active')->first();
        } else {
            $log = WhatsAppConversationLog::where('contact_id', $contact->id)
                ->where('status', 'active')->latest()->first();
        }

        if (!$log) return null;

        $conv = WhatsAppConversation::find($log->conversation_id);
        if (!$conv || !$conv->is_active) return null;

        $log->update(['last_response' => $reply]);

        $steps = $conv->steps ?? [];
        $nextStep = $log->current_step + 1;

        if (isset($steps[$log->current_step]['conditions'])) {
            foreach ($steps[$log->current_step]['conditions'] as $cond) {
                if ($this->evaluateCondition($reply, $cond)) {
                    $nextStep = $cond['next_step'] ?? $nextStep;
                    break;
                }
            }
        }

        if (isset($steps[$nextStep])) {
            $this->processConversationStep($conv, $contact, $nextStep);
        } else {
            $log->update(['status' => 'completed']);
        }

        return ['conversation' => $conv->name, 'next_step' => $nextStep];
    }

    // ─── Payload Builders ────────────────────────────────────────────

    public function buildTextPayload(string $message): array
    {
        return ['type' => 'text', 'text' => ['body' => $message]];
    }

    public function buildInteractivePayload(string $interactiveType, array $data, ?string $body = null, ?string $footer = null): array
    {
        $payload = [
            'type' => 'interactive',
            'interactive' => array_merge([
                'type' => $interactiveType,
                'body' => ['text' => $body ?? ''],
            ], $data),
        ];
        if ($footer) $payload['interactive']['footer'] = ['text' => $footer];
        return $payload;
    }

    public function buildButtonPayload(string $body, array $buttons, ?string $header = null, ?string $footer = null): array
    {
        $formatted = [];
        foreach ($buttons as $b) {
            if (($b['type'] ?? 'quick_reply') === 'cta_url') {
                $formatted[] = ['type' => 'url', 'url' => $b['url'] ?? '#', 'title' => $b['title'] ?? 'Visit'];
            } elseif (($b['type'] ?? 'quick_reply') === 'cta_phone') {
                $formatted[] = ['type' => 'phone_number', 'phone_number' => $b['phone'] ?? '', 'title' => $b['title'] ?? 'Call'];
            } else {
                $formatted[] = ['type' => 'reply', 'reply' => ['id' => $b['id'] ?? 'btn_' . uniqid(), 'title' => mb_substr($b['title'] ?? 'Reply', 0, 20)]];
            }
        }

        $data = [
            'action' => ['buttons' => $formatted],
        ];
        if ($header) $data['header'] = ['type' => 'text', 'text' => $header];

        return $this->buildInteractivePayload('button', $data, $body, $footer);
    }

    public function buildListPayload(string $body, string $listTitle, array $sections, ?string $footer = null): array
    {
        return $this->buildInteractivePayload('list', [
            'action' => ['button' => mb_substr($listTitle, 0, 20), 'sections' => $sections],
        ], $body, $footer);
    }

    public function buildMediaPayload(string $mediaType, string $mediaUrl, ?string $caption = null): array
    {
        $key = match ($mediaType) {
            'image' => 'image', 'document' => 'document', 'audio' => 'audio',
            'video' => 'video', 'sticker' => 'sticker',
            default => 'document',
        };
        $payload = ['type' => $key, $key => ['link' => $mediaUrl]];
        if ($caption && $key !== 'audio' && $key !== 'sticker') {
            $payload[$key]['caption'] = $caption;
        }
        return $payload;
    }

    public function buildCatalogPayload(string $catalogId, array $productRetailerIds): array
    {
        return [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'catalog_message',
                'body' => ['text' => 'Check out our products'],
                'action' => [
                    'name' => 'catalog_message',
                    'parameters' => [
                        'thumbnail_product_retailer_id' => $productRetailerIds[0] ?? '',
                    ],
                ],
            ],
        ];
    }

    public function buildTemplatePayload(WhatsAppTemplate $template, ?Lead $lead = null): array
    {
        $body = $this->processPlaceholders($template->body, $lead);
        $footer = $template->footer ? $this->processPlaceholders($template->footer, $lead) : null;
        $header = $template->header_value ? $this->processPlaceholders($template->header_value, $lead) : null;

        return match ($template->message_type) {
            'interactive' => $this->buildInteractiveFromTemplate($template, $body, $header, $footer),
            'media' => $this->buildMediaPayload($template->header_type ?? 'image', $template->media_url ?? '', $body),
            'flow' => ['type' => 'text', 'text' => ['body' => $body]], // flows handled via sendFlow
            default => $this->buildTextPayload($body),
        };
    }

    protected function buildInteractiveFromTemplate(WhatsAppTemplate $template, string $body, ?string $header, ?string $footer): array
    {
        $buttons = $template->buttons ?? [];

        if (!empty($template->sections)) {
            return $this->buildListPayload($body, $template->name, $template->sections, $footer);
        }

        if (!empty($buttons)) {
            return $this->buildButtonPayload($body, $buttons, $header, $footer);
        }

        return $this->buildTextPayload($body);
    }

    // ─── HTTP Sending ────────────────────────────────────────────────

    protected function sendPayload(string $phone, array $payload, ?Lead $lead = null): void
    {
        if (!empty($payload['type']) && $payload['type'] === 'text' && !empty($payload['text']['body'])) {
            $payload['text']['body'] = $this->processPlaceholders($payload['text']['body'], $lead);
        }

        if (empty($this->apiEndpoint)) {
            $this->logSimulatedSend($phone, $payload);
            return;
        }

        $this->sendRawPayload($phone, $payload, $lead);
    }

    protected function sendRawPayload(string $phone, array $payload, ?Lead $lead = null): void
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('cURL extension is not installed or enabled.');
        }

        $body = array_merge(['messaging_product' => 'whatsapp', 'to' => $phone], $payload);

        $ch = curl_init($this->apiEndpoint);
        if ($ch === false) {
            throw new \RuntimeException('Failed to initialize cURL handle.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiToken,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("cURL error: {$curlError}");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException("API returned HTTP {$httpCode}: {$response}");
        }
    }

    public function testApiConnection(): array
    {
        if (empty($this->apiEndpoint)) {
            return ['success' => false, 'error' => 'API endpoint not configured.'];
        }
        if (empty($this->apiToken)) {
            return ['success' => false, 'error' => 'API token not configured.'];
        }
        if (!extension_loaded('curl')) {
            return ['success' => false, 'error' => 'cURL extension not installed.'];
        }

        // The /messages endpoint only accepts POST; strip it to GET the phone number ID object for auth check
        $testUrl = preg_replace('#/messages/?$#', '', $this->apiEndpoint);

        $ch = curl_init($testUrl);
        if ($ch === false) {
            return ['success' => false, 'error' => 'Failed to initialize cURL.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->apiToken],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => "cURL error: {$curlError}"];
        }

        $decoded = json_decode($response, true);

        if ($httpCode === 200) {
            $name = $decoded['name'] ?? ($decoded['display_phone_number'] ?? '');
            return ['success' => true, 'message' => "Authenticated. Phone number ID: {$name}", 'data' => $decoded];
        }

        $errorMsg = $decoded['error']['message'] ?? $response;
        return ['success' => false, 'error' => "HTTP {$httpCode}: {$errorMsg}"];
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    protected function processPlaceholders(string $text, ?Lead $lead): string
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return str_replace(
            ['{{name}}', '{{first_name}}', '{{phone}}', '{{email}}', '{{site_name}}', '{{site_url}}', '{{year}}'],
            [
                $lead?->name ?? 'Valued Customer',
                $lead ? explode(' ', $lead->name ?? '')[0] : 'Valued Customer',
                $lead?->phone ?? '',
                $lead?->email ?? '',
                $settings['site_name'] ?? 'Joala Ventures',
                url('/'),
                date('Y'),
            ],
            $text
        );
    }

    protected function evaluateCondition(string $reply, array $condition): bool
    {
        $field = $condition['field'] ?? 'message';
        $op = $condition['operator'] ?? 'contains';
        $value = $condition['value'] ?? '';

        $input = $field === 'message' ? $reply : '';
        $input = strtolower(trim($input));
        $value = strtolower(trim($value));

        return match ($op) {
            'equals' => $input === $value,
            'contains' => str_contains($input, $value),
            'starts_with' => str_starts_with($input, $value),
            'regex' => preg_match($value, $reply) === 1,
            default => false,
        };
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

    protected function logSimulatedSend(string $phone, array $payload): void
    {
        Log::info('[WhatsApp Simulated]', ['to' => $phone, 'payload' => $payload]);
    }
}
