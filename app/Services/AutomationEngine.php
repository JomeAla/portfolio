<?php

namespace App\Services;

use App\Models\AutomationRule;
use App\Models\Lead;
use App\Models\EmailQueue;
use App\Models\Tag;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    public function processTrigger(string $triggerType, $data): void
    {
        $rules = AutomationRule::where('trigger_type', $triggerType)->where('is_active', true)->get();
        foreach ($rules as $rule) {
            $this->executeAction($rule, $data);
        }
    }

    private function executeAction(AutomationRule $rule, array $data): bool
    {
        $leadId = $data['lead_id'] ?? null;
        $lead = $leadId ? Lead::find($leadId) : (isset($data['email']) ? Lead::where('email', $data['email'])->first() : null);
        if (!$lead) return false;
        
        $config = $rule->action_config ?? [];
        
        try {
            switch ($rule->action_type) {
                case 'add_tag':
                    if (!empty($config['tag'])) {
                        $tag = Tag::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($config['tag'])], ['name' => $config['tag'], 'color' => $config['color'] ?? '#6366f1']);
                        $lead->tags()->syncWithoutDetaching([$tag->id]);
                    }
                    break;
                case 'update_score':
                    if (isset($config['score_change'])) {
                        $lead->increment('score', $config['score_change']);
                    }
                    break;
                case 'notify_admin':
                    Log::info("Automation: {$rule->name}", $data);
                    break;
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Automation failed: {$rule->name}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    public static function trigger(string $type, array $data = []): void
    {
        (new self())->processTrigger($type, $data);
    }
}