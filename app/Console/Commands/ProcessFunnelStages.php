<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funnel;
use App\Models\FunnelLead;
use App\Models\FunnelStage;
use App\Models\Lead;
use App\Services\Marketing\MarketingService;
use Illuminate\Support\Facades\Log;

class ProcessFunnelStages extends Command
{
    protected $signature = 'funnel:process-stages {--funnel= : Process specific funnel ID} {--limit=500 : Max leads to process per run}';
    protected $description = 'Process funnel leads and advance them through stages based on conditions';

    public function handle(): int
    {
        $funnelId = $this->option('funnel');
        $limit = (int) $this->option('limit');

        $query = Funnel::where('is_active', true);
        if ($funnelId) {
            $query->where('id', $funnelId);
        }

        $funnels = $query->get();

        if ($funnels->isEmpty()) {
            $this->info('No active funnels found.');
            return 0;
        }

        $this->info("Processing {$funnels->count()} active funnel(s)...");

        $totalAdvanced = 0;
        $totalWait = 0;

        foreach ($funnels as $funnel) {
            $result = $this->processFunnel($funnel, $limit);
            $totalAdvanced += $result['advanced'];
            $totalWait += $result['waiting'];
        }

        $this->info("Done. {$totalAdvanced} lead(s) advanced, {$totalWait} lead(s) waiting.");
        return 0;
    }

    protected function processFunnel(Funnel $funnel, int $limit): array
    {
        $advanced = 0;
        $waiting = 0;

        $stages = $funnel->stages()->orderBy('order')->get();
        if ($stages->isEmpty()) {
            return ['advanced' => 0, 'waiting' => 0];
        }

        $leads = FunnelLead::where('funnel_id', $funnel->id)
            ->whereNull('exited_at')
            ->limit($limit)
            ->get();

        $this->info("  Funnel '{$funnel->name}': {$leads->count()} active leads");

        foreach ($leads as $lead) {
            $stageResult = $this->processLeadStage($lead, $funnel, $stages);
            if ($stageResult === 'advanced') {
                $advanced++;
            } elseif ($stageResult === 'waiting') {
                $waiting++;
            }
        }

        return ['advanced' => $advanced, 'waiting' => $waiting];
    }

    protected function processLeadStage(FunnelLead $lead, Funnel $funnel, $stages): string
    {
        $leadRecord = Lead::where('email', $lead->email)->first();
        if (!$leadRecord) {
            return 'skip';
        }

        $currentStage = $lead->stage_id
            ? $stages->firstWhere('id', $lead->stage_id)
            : $stages->first();

        if (!$currentStage) {
            return 'skip';
        }

        if ($currentStage->isWaitStage() || $currentStage->hasCondition()) {
            $canAdvance = $this->evaluateConditions($lead, $leadRecord, $currentStage, $funnel);

            if ($canAdvance) {
                $this->advanceLead($lead, $leadRecord, $funnel, $stages, $currentStage);
                return 'advanced';
            } else {
                $waitDesc = $currentStage->getWaitDescription();
                $this->info("    Lead {$lead->email}: waiting at '{$currentStage->name}' ({$waitDesc})");
                return 'waiting';
            }
        }

        return 'skip';
    }

    protected function evaluateConditions(FunnelLead $funnelLead, Lead $lead, FunnelStage $stage, Funnel $funnel): bool
    {
        $conditionType = $stage->condition_type ?? 'none';

        if ($conditionType === 'none' || empty($conditionType)) {
            return true;
        }

        if ($conditionType === 'wait') {
            $waitHours = $stage->wait_duration_hours ?? 0;
            if ($waitHours > 0) {
                $enteredAt = $funnelLead->entered_at ?? $funnelLead->created_at ?? null;
                if ($enteredAt) {
                    $waitUntil = $enteredAt->copy()->addHours($waitHours);
                    if (now()->lt($waitUntil)) {
                        return false;
                    }
                }
            }
            return true;
        }

        if ($conditionType === 'email_opens') {
            $requiredOpens = $stage->condition_value['min_opens'] ?? 1;
            $actualOpens = $funnelLead->email_opens ?? 0;
            return $actualOpens >= $requiredOpens;
        }

        if ($conditionType === 'clicks') {
            $requiredClicks = $stage->condition_value['min_clicks'] ?? 1;
            $actualClicks = $funnelLead->clicks_count ?? 0;
            return $actualClicks >= $requiredClicks;
        }

        if ($conditionType === 'score_above') {
            $threshold = $stage->condition_value['min_score'] ?? $funnel->getHotThreshold();
            return ($funnelLead->score ?? 0) >= $threshold;
        }

        if ($conditionType === 'tag_has') {
            $requiredTag = $stage->condition_value['tag_name'] ?? '';
            if (empty($requiredTag)) return true;
            $leadTags = $lead->tags()->pluck('name')->toArray();
            return in_array($requiredTag, $leadTags);
        }

        if ($conditionType === 'converted') {
            return $funnelLead->converted === true;
        }

        if ($conditionType === 'days_inactive') {
            $maxDays = $stage->condition_value['max_days'] ?? 7;
            $lastActivity = $funnelLead->last_activity ?? $funnelLead->entered_at;
            if (!$lastActivity) return true;
            return $lastActivity->diffInDays(now()) >= $maxDays;
        }

        return true;
    }

    protected function advanceLead(FunnelLead $funnelLead, Lead $lead, Funnel $funnel, $stages, FunnelStage $currentStage): void
    {
        $currentIndex = $stages->search(fn($s) => $s->id === $currentStage->id);
        if ($currentIndex === false || ($currentIndex + 1) >= $stages->count()) {
            return;
        }

        $nextStage = $stages[$currentIndex + 1];

        $funnelLead->update([
            'stage_id' => $nextStage->id,
            'last_activity' => now(),
        ]);

        $this->info("    Lead {$lead->email}: advanced from '{$currentStage->name}' to '{$nextStage->name}'");

        if ($nextStage->action_on_complete === 'email' && !empty($nextStage->email_template)) {
            $this->sendStageEmail($funnelLead, $lead, $nextStage);
        }

        if ($nextStage->action_on_complete === 'tag' && !empty($nextStage->action_config['tag_name'])) {
            $tagName = $nextStage->action_config['tag_name'];
            $lead->tags()->syncWithoutDetaching(
                \App\Models\Tag::firstOrCreate(['name' => $tagName])->id
            );
        }

        if ($nextStage->action_on_complete === 'notify' && !empty($nextStage->action_config['webhook_url'])) {
            $this->sendWebhookNotification($funnelLead, $lead, $nextStage);
        }

        if ($nextStage->type === 'email') {
            $this->enrollLeadInSequence($lead, $nextStage);
        }

        if ($nextStage->type === 'checkout' || $nextStage->type === 'sales_page') {
            if ($funnel->product_id) {
                Log::info("Lead {$lead->email} reached checkout stage in funnel {$funnel->id}");
            }
        }

        Log::info("Lead {$lead->email} advanced to stage '{$nextStage->name}' in funnel {$funnel->id}");
    }

    protected function sendStageEmail(FunnelLead $funnelLead, Lead $lead, FunnelStage $stage): void
    {
        try {
            $template = $stage->email_template;
            $subject = $stage->action_config['subject'] ?? "Your next step in {$stage->name}";

            $body = str_replace(
                ['{{name}}', '{{email}}', '{{stage_name}}'],
                [$lead->name ?? 'there', $lead->email, $stage->name],
                $template
            );

            \App\Models\EmailQueue::create([
                'lead_id' => $lead->id,
                'subject' => $subject,
                'body' => $body,
                'status' => 'pending',
                'scheduled_send_time' => now(),
            ]);

            $this->info("      Queued email for stage '{$stage->name}'");
        } catch (\Exception $e) {
            Log::error("Failed to queue stage email: " . $e->getMessage());
        }
    }

    protected function enrollLeadInSequence(Lead $lead, FunnelStage $stage): void
    {
        if (!$stage->sequence_id) {
            return;
        }

        $sequence = \App\Models\EmailSequence::find($stage->sequence_id);
        if (!$sequence || !$sequence->is_active) {
            return;
        }

        $marketingService = app(MarketingService::class);
        $marketingService->enrollLeadInSequence($lead, $sequence->id);
        $this->info("      Enrolled in sequence '{$sequence->name}'");
    }

    protected function sendWebhookNotification(FunnelLead $funnelLead, Lead $lead, FunnelStage $stage): void
    {
        try {
            $webhookUrl = $stage->action_config['webhook_url'];
            if (empty($webhookUrl)) return;

            $payload = json_encode([
                'event' => 'funnel_stage_advance',
                'lead_email' => $lead->email,
                'lead_name' => $lead->name,
                'stage_name' => $stage->name,
                'funnel_id' => $funnelLead->funnel_id,
                'score' => $funnelLead->score,
                'timestamp' => now()->toIso8601String(),
            ]);

            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);

            Log::info("Webhook sent for lead {$lead->email} advancing to '{$stage->name}'");
        } catch (\Exception $e) {
            Log::error("Webhook failed: " . $e->getMessage());
        }
    }
}