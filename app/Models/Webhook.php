<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Traits\SlugGenerator;

class Webhook extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';
    
    protected $fillable = [
        'name',
        'url',
        'events',
        'is_active',
        'secret',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    public static function availableEvents(): array
    {
        return [
            'lead_created' => 'Lead Created',
            'lead_updated' => 'Lead Updated',
            'lead_tagged' => 'Lead Tagged',
            'email_sent' => 'Email Sent',
            'email_opened' => 'Email Opened',
            'email_clicked' => 'Email Clicked',
            'order_created' => 'Order Created',
            'order_completed' => 'Order Completed',
            'invoice_created' => 'Invoice Created',
            'invoice_paid' => 'Invoice Paid',
        ];
    }

    public function fire(string $event, array $data)
    {
        if (!$this->is_active) {
            return false;
        }

        if (!in_array($event, $this->events ?? [])) {
            return false;
        }

        $payload = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Secret' => $this->secret ?? '',
                    'X-Webhook-Event' => $event,
                ])
                ->post($this->url, $payload);

            $responseCode = $response->status();
            $responseBody = substr($response->body(), 0, 1000);
            $status = $response->successful() ? 'success' : 'failed';
            $errorMessage = null;
            $result = $response->successful();
        } catch (\Exception $e) {
            $responseCode = null;
            $responseBody = null;
            $status = 'failed';
            $errorMessage = $e->getMessage();
            Log::error('Webhook failed: ' . $e->getMessage(), [
                'webhook_id' => $this->id,
                'event' => $event,
            ]);
            $result = false;
        }
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        $leadId = $data['lead_id'] ?? $data['id'] ?? null;

        WebhookFiringHistory::create([
            'automation_rule_id' => null,
            'lead_id' => is_numeric($leadId) ? (int)$leadId : null,
            'event_type' => $event,
            'webhook_url' => $this->url,
            'payload' => $payload,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'status' => $status,
            'error_message' => $errorMessage,
            'response_time_ms' => $responseTime,
        ]);

        return $result;
    }
}