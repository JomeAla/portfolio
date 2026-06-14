<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutomationRule;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Support\Facades\Log;

class ExecuteAutomationRules extends Command
{
    protected $signature = 'automation:execute {--rule= : Execute specific rule ID}';
    protected $description = 'Execute automation rules for triggers';

    public function handle()
    {
        $ruleId = $this->option('rule');

        $rulesQuery = AutomationRule::where('is_active', true);
        
        if ($ruleId) {
            $rulesQuery->where('id', $ruleId);
        }

        $rules = $rulesQuery->get();

        $this->info("Found {$rules->count()} active automation rules to evaluate.");

        foreach ($rules as $rule) {
            $this->processRule($rule);
        }

        $this->info("Automation execution complete!");
        
        return 0;
    }

    protected function processRule($rule)
    {
        $this->info("Processing rule: {$rule->name}");

        try {
            $triggerType = $rule->trigger_type;
            $triggerValue = $rule->trigger_value;
            
            if (empty($triggerType)) {
                $this->warn("Rule {$rule->id} has no trigger type configured");
                return;
            }

            $leads = $this->getLeadsForTrigger($triggerType, $triggerValue, $rule);
            $this->info("Found {$leads->count()} leads matching trigger");

            foreach ($leads as $lead) {
                $this->executeActions($rule, $lead);
            }

            $rule->update(['last_run_at' => now()]);

        } catch (\Exception $e) {
            $this->error("Rule {$rule->id} failed: " . $e->getMessage());
            
            Log::error('Automation rule failed', [
                'rule_id' => $rule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getLeadsForTrigger($triggerType, $triggerValue, $rule)
    {
        $query = Lead::query();
        $actionConfig = $rule->action_config ?? [];

        switch ($triggerType) {
            case 'form_submitted':
                if ($triggerValue) {
                    $query->where('landing_page_id', $triggerValue);
                }
                break;
            case 'email_opened':
                $leadIds = \App\Models\EmailOpen::pluck('lead_id')->unique();
                $query->whereIn('id', $leadIds);
                break;
            case 'email_clicked':
                $leadIds = \App\Models\EmailClick::pluck('lead_id')->unique();
                $query->whereIn('id', $leadIds);
                break;
            case 'score_reached':
                $threshold = is_numeric($triggerValue) ? (int)$triggerValue : 50;
                $query->where('score', '>=', $threshold);
                break;
            case 'tag_added':
                if ($tagId = $actionConfig['tag_id'] ?? null) {
                    $query->whereHas('tags', fn($q) => $q->where('tag_id', $tagId));
                }
                break;
            case 'page_visited':
                if ($triggerValue) {
                    $query->where('source', 'like', '%' . $triggerValue . '%');
                }
                break;
            case 'lead_created':
                break;
            case 'campaign_enrolled':
                if ($campaignId = $actionConfig['campaign_id'] ?? null) {
                    $query->where('campaign_id', $campaignId);
                }
                break;
            default:
                $query->whereRaw('1 = 0');
        }

        $query->whereDoesntHave('automationLogs', fn($aq) => $aq->where('rule_id', $rule->id));

        return $query->get();
    }

    protected function executeActions($rule, $lead)
    {
        $actionType = $rule->action_type;
        $actionConfig = $rule->action_config ?? [];

        if (empty($actionType)) {
            return;
        }

        try {
            switch ($actionType) {
                case 'add_tag':
                    if (!empty($actionConfig['tag_id'])) {
                        $lead->tags()->syncWithoutDetaching([$actionConfig['tag_id']]);
                    } elseif (!empty($actionConfig['tag_name'])) {
                        $tag = \App\Models\Tag::firstOrCreate(['name' => $actionConfig['tag_name']]);
                        $lead->tags()->syncWithoutDetaching([$tag->id]);
                    }
                    break;
                case 'remove_tag':
                    if (!empty($actionConfig['tag_id'])) {
                        $lead->tags()->detach($actionConfig['tag_id']);
                    }
                    break;
                case 'enroll_sequence':
                    if (!empty($actionConfig['sequence_id'])) {
                        $sequence = \App\Models\EmailSequence::find($actionConfig['sequence_id']);
                        if ($sequence && $sequence->is_active) {
                            $marketingService = app(\App\Services\Marketing\MarketingService::class);
                            $marketingService->enrollLeadInSequence($lead, $sequence->id);
                        }
                    }
                    break;
                case 'update_score':
                    $points = $actionConfig['points'] ?? 0;
                    if ($points > 0) {
                        $lead->addScore((int)$points);
                    } elseif ($points < 0) {
                        $lead->removeScore(abs((int)$points));
                    }
                    break;
                case 'send_email':
                    if (!empty($actionConfig['subject']) && !empty($actionConfig['body'])) {
                        \App\Models\EmailQueue::create([
                            'lead_id' => $lead->id,
                            'subject' => str_replace(['{{name}}', '{{email}}'], [$lead->name ?? 'there', $lead->email], $actionConfig['subject']),
                            'body' => str_replace(['{{name}}', '{{email}}'], [$lead->name ?? 'there', $lead->email], $actionConfig['body']),
                            'status' => 'pending',
                            'scheduled_send_time' => now(),
                        ]);
                    }
                    break;
                case 'notify_admin':
                    $lead->increment('score', 1);
                    break;
                case 'webhook':
                    if (!empty($actionConfig['webhook_url'])) {
                        $this->fireWebhook($lead, $actionConfig['webhook_url'], $rule->name);
                    }
                    break;
            }

            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'type' => 'automation',
                'description' => "Automation '{$rule->name}' executed: {$actionType}",
            ]);

            \App\Models\AutomationLog::create([
                'rule_id' => $rule->id,
                'lead_id' => $lead->id,
                'action' => $actionType,
                'executed_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Automation action failed', [
                'rule_id' => $rule->id,
                'lead_id' => $lead->id,
                'action' => $actionType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function fireWebhook($lead, $url, $ruleName)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'event' => 'automation_triggered',
                'rule' => $ruleName,
                'lead_email' => $lead->email,
                'lead_name' => $lead->name,
                'timestamp' => now()->toIso8601String(),
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            Log::error('Webhook failed: ' . $e->getMessage());
        }
    }

    protected function sendEmail($lead, $action)
    {
    }
}