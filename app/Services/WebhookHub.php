<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookHub
{
    const SLACK = 'slack';
    const DISCORD = 'discord';
    const TELEGRAM = 'telegram';

    public function send(Webhook $webhook, string $event, array $data): bool
    {
        $integration = $webhook->integration ?? 'custom';
        
        try {
            switch ($integration) {
                case self::SLACK:
                    return $this->sendToSlack($webhook->url, $event, $data);
                case self::DISCORD:
                    return $this->sendToDiscord($webhook->url, $event, $data);
                case self::TELEGRAM:
                    return $this->sendToTelegram($webhook->config ?? [], $event, $data);
                default:
                    return $this->sendCustom($webhook, $event, $data);
            }
        } catch (\Exception $e) {
            Log::error("Webhook failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendToSlack(string $url, string $event, array $data): bool
    {
        $payload = ['text' => "Event: {$event}\nData: " . json_encode($data)];
        $response = Http::timeout(30)->post($url, $payload);
        return $response->successful();
    }

    private function sendToDiscord(string $url, string $event, array $data): bool
    {
        $payload = ['embeds' => [['title' => $event, 'description' => json_encode($data), 'color' => 0x6366f1]]];
        $response = Http::timeout(30)->post($url, $payload);
        return $response->successful();
    }

    private function sendToTelegram(array $config, string $event, array $data): bool
    {
        $botToken = $config['bot_token'] ?? null;
        $chatId = $config['chat_id'] ?? null;
        if (!$botToken || !$chatId) return false;
        
        $text = "*{$event}*\n" . json_encode($data, JSON_PRETTY_PRINT);
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $response = Http::timeout(30)->post($url, ['chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'Markdown']);
        return $response->successful();
    }

    private function sendCustom(Webhook $webhook, string $event, array $data): bool
    {
        $payload = ['event' => $event, 'data' => $data, 'timestamp' => now()->toIso8601String()];
        $response = Http::timeout(30)->withHeaders(['Content-Type' => 'application/json'])->post($webhook->url, $payload);
        return $response->successful();
    }

    public static function fire(string $event, array $data = []): void
    {
        $webhooks = Webhook::where('is_active', true)->whereJsonContains('events', $event)->get();
        foreach ($webhooks as $webhook) {
            (new self())->send($webhook, $event, $data);
        }
    }
}