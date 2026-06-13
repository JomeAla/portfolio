<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\Lead;
use App\Models\Tag;
use App\Models\EmailSequence;
use App\Models\EmailQueue;
use App\Models\EmailOpen;
use App\Models\Setting;
use App\Models\WebhookFiringHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutomationService
{
    public function processEmailOpenedTrigger(EmailOpen $emailOpen)
    {
        $lead = $emailOpen->lead;
        $queueItem = $emailOpen->emailQueue;
        $step = $queueItem?->step;
        
        if (!$lead || !$step) return;

        $rules = AutomationRule::where('is_active', true)
            ->where('trigger_type', 'email_opened')
            ->get();

        foreach ($rules as $rule) {
            $this->executeRule($rule, $lead, [
                'email_open' => $emailOpen,
                'step' => $step,
                'queue_item' => $queueItem,
            ]);
        }
    }

    public function processLinkClickedTrigger(Lead $lead, string $url)
    {
        $rules = AutomationRule::where('is_active', true)
            ->where('trigger_type', 'link_clicked')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->trigger_value && str_contains($url, $rule->trigger_value)) {
                $this->executeRule($rule, $lead, ['clicked_url' => $url]);
            }
        }
    }

    public function processTagAddedTrigger(Lead $lead, Tag $tag)
    {
        $rules = AutomationRule::where('is_active', true)
            ->where('trigger_type', 'tag_added')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->trigger_value === $tag->name) {
                $this->executeRule($rule, $lead, ['tag' => $tag]);
            }
        }
    }

    public function processScoreReachedTrigger(Lead $lead)
    {
        $rules = AutomationRule::where('is_active', true)
            ->where('trigger_type', 'score_reached')
            ->get();

        foreach ($rules as $rule) {
            $targetScore = (int) $rule->trigger_value;
            if ($lead->score >= $targetScore) {
                $this->executeRule($rule, $lead);
            }
        }
    }

    public function processNewLeadTrigger(Lead $lead)
    {
        $rules = AutomationRule::where('is_active', true)
            ->where('trigger_type', 'lead_created')
            ->get();

        foreach ($rules as $rule) {
            $this->executeRule($rule, $lead);
        }
    }

    private function executeRule(AutomationRule $rule, Lead $lead, array $context = [])
    {
        try {
            switch ($rule->action_type) {
                case 'enroll_sequence':
                    $this->enrollInSequence($rule, $lead);
                    break;
                case 'add_tag':
                    $this->addTag($rule, $lead);
                    break;
                case 'remove_tag':
                    $this->removeTag($rule, $lead);
                    break;
                case 'send_email':
                    $this->sendEmail($rule, $lead, $context);
                    break;
                case 'update_score':
                    $this->updateScore($rule, $lead);
                    break;
                case 'notify_admin':
                    $this->notifyAdmin($rule, $lead, $context);
                    break;
                case 'webhook':
                    $this->triggerWebhook($rule, $lead, $context);
                    break;
            }

            $rule->increment('times_triggered');
        } catch (\Exception $e) {
            Log::error('Automation rule failed: ' . $e->getMessage(), [
                'rule_id' => $rule->id,
                'lead_id' => $lead->id,
            ]);
        }
    }

    private function enrollInSequence(AutomationRule $rule, Lead $lead)
    {
        if (!$rule->action_sequence_id) return;

        $sequence = EmailSequence::find($rule->action_sequence_id);
        if (!$sequence || !$sequence->is_active) return;

        $steps = $sequence->steps()->orderBy('step_order')->get();
        
        foreach ($steps as $index => $step) {
            $scheduledTime = now()->addDays($step->delay_days);
            
            EmailQueue::create([
                'lead_id' => $lead->id,
                'sequence_step_id' => $step->id,
                'scheduled_send_time' => $scheduledTime,
                'status' => 'pending',
            ]);
        }
    }

    private function addTag(AutomationRule $rule, Lead $lead)
    {
        $tagName = $rule->action_config['tag'] ?? null;
        if (!$tagName) return;

        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $lead->tags()->syncWithoutDetaching([$tag->id]);
    }

    private function removeTag(AutomationRule $rule, Lead $lead)
    {
        $tagName = $rule->action_config['tag'] ?? null;
        if (!$tagName) return;

        $tag = Tag::where('name', $tagName)->first();
        if ($tag) {
            $lead->tags()->detach($tag->id);
        }
    }

    private function sendEmail(AutomationRule $rule, Lead $lead, array $context)
    {
        $subject = $rule->action_config['subject'] ?? 'Automated Email';
        $body = $rule->action_config['body'] ?? 'Hello {{name}}';

        $replacements = [
            '{{name}}' => $lead->name ?? 'there',
            '{{email}}' => $lead->email,
            '{{date}}' => now()->format('F j, Y'),
        ];

        $processedBody = str_replace(array_keys($replacements), array_values($replacements), $body);
        $processedSubject = str_replace(array_keys($replacements), array_values($replacements), $subject);

        $marketingService = app(\App\Services\Marketing\MarketingService::class);
        $marketingService->sendEmail($lead->email, $processedSubject, $processedBody);
    }

    private function updateScore(AutomationRule $rule, Lead $lead)
    {
        $config = $rule->action_config;
        $change = (int) ($config['score_change'] ?? 0);
        $operation = $config['operation'] ?? 'add';

        $currentScore = $lead->score ?? 0;
        
        switch ($operation) {
            case 'add':
                $newScore = $currentScore + $change;
                break;
            case 'subtract':
                $newScore = max(0, $currentScore - $change);
                break;
            case 'set':
                $newScore = $change;
                break;
            default:
                $newScore = $currentScore + $change;
        }

        $lead->update(['score' => $newScore]);

        $this->processScoreReachedTrigger($lead);
    }

    private function notifyAdmin(AutomationRule $rule, Lead $lead, array $context)
    {
        $settings = Setting::pluck('value', 'key');
        $adminEmail = $settings['contact_email'] ?? $settings['mail_from_address'] ?? null;
        
        if (!$adminEmail) return;

        $subject = "Automation Triggered: {$rule->name}";
        $body = "Lead {$lead->email} triggered the rule '{$rule->name}'.\n\n";
        $body .= "Lead Details:\n";
        $body .= "- Name: {$lead->name}\n";
        $body .= "- Email: {$lead->email}\n";
        $score = $lead->score ?? 0;
        $body .= "- Score: {$score}\n";
        
        $marketingService = app(\App\Services\Marketing\MarketingService::class);
        $marketingService->sendEmail($adminEmail, $subject, nl2br($body));
    }

    private function triggerWebhook(AutomationRule $rule, Lead $lead, array $context)
    {
        $webhookUrl = $rule->action_config['webhook_url'] ?? null;
        if (!$webhookUrl) return;

        $payload = [
            'event' => 'automation_triggered',
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'trigger_type' => $rule->trigger_type,
            'lead' => [
                'id' => $lead->id,
                'email' => $lead->email,
                'name' => $lead->name,
                'score' => $lead->score,
            ],
            'triggered_at' => now()->toIso8601String(),
        ];

        $startTime = microtime(true);
        try {
            $response = Http::timeout(30)->post($webhookUrl, $payload);
            $responseCode = $response->status();
            $responseBody = substr($response->body(), 0, 1000);
            $status = $response->successful() ? 'success' : 'failed';
            $errorMessage = null;
        } catch (\Exception $e) {
            $responseCode = null;
            $responseBody = null;
            $status = 'failed';
            $errorMessage = $e->getMessage();
            Log::error('Webhook failed: ' . $e->getMessage());
        }
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        WebhookFiringHistory::create([
            'automation_rule_id' => $rule->id,
            'lead_id' => $lead->id,
            'event_type' => 'automation_triggered',
            'webhook_url' => $webhookUrl,
            'payload' => $payload,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'status' => $status,
            'error_message' => $errorMessage,
            'response_time_ms' => $responseTime,
        ]);
    }
}