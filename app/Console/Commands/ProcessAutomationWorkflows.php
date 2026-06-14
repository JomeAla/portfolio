<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funnel;
use App\Models\FunnelLead;
use App\Models\Lead;
use App\Models\EmailSequence;
use App\Models\EmailQueue;
use App\Models\Tag;
use App\Models\Setting;
use Carbon\Carbon;

class ProcessAutomationWorkflows extends Command
{
    protected $signature = 'automation:process-workflows {--limit=50 : Max workflows to process}';
    protected $description = 'Process visual automation builder workflows for funnel leads';

    public function handle()
    {
        $funnels = Funnel::whereNotNull('automation_workflows')
            ->where('automation_workflows', '!=', '[]')
            ->where('automation_workflows', '!=', '{}')
            ->where('is_active', true)
            ->get();

        $this->info("Found {$funnels->count()} funnels with automation workflows.");

        $limit = (int) $this->option('limit');
        $processed = 0;

        foreach ($funnels as $funnel) {
            $workflow = $funnel->automation_workflows;
            if (empty($workflow['nodes']) || empty($workflow['connections'])) {
                continue;
            }

            $funnelLeads = FunnelLead::where('funnel_id', $funnel->id)
                ->whereNull('exited_at')
                ->where(function ($q) {
                    $q->whereNull('wait_until')
                      ->orWhere('wait_until', '<=', now());
                })
                ->limit($limit)
                ->get();

            foreach ($funnelLeads as $funnelLead) {
                if ($processed >= $limit) {
                    break 2;
                }
                $this->processLeadWorkflow($funnelLead, $workflow);
                $processed++;
            }
        }

        $this->info("Processed {$processed} leads through automation workflows.");
        return 0;
    }

    protected function processLeadWorkflow(FunnelLead $funnelLead, array $workflow)
    {
        $nodes = $workflow['nodes'];
        $connections = $workflow['connections'];
        $state = $funnelLead->workflow_state ?? [];
        $executedIds = $state['executed'] ?? [];
        $waitingNodeId = $state['waiting_node'] ?? null;

        $nodeMap = [];
        foreach ($nodes as $node) {
            $nodeMap[$node['id']] = $node;
        }

        $outgoing = [];
        foreach ($connections as $conn) {
            $outgoing[$conn['from']][] = $conn['to'];
        }

        if ($waitingNodeId) {
            $node = $nodeMap[$waitingNodeId] ?? null;
            if ($node && $node['kind'] === 'action' && $node['type'] === 'wait') {
                $state['waiting_node'] = null;
                $executedIds[] = $waitingNodeId;
                $state['executed'] = $executedIds;
                $funnelLead->workflow_state = $state;
                $funnelLead->wait_until = null;
                $funnelLead->save();
            }
        }

        $entryNodeIds = $this->findEntryNodes($nodes, $connections, $executedIds);
        if (empty($entryNodeIds)) {
            return;
        }

        foreach ($entryNodeIds as $nodeId) {
            $this->traverseNode($nodeId, $nodeMap, $outgoing, $funnelLead, $executedIds);
            $funnelLead->workflow_state = array_merge(
                $funnelLead->workflow_state ?? [],
                ['executed' => $executedIds]
            );
            $funnelLead->save();
        }
    }

    protected function findEntryNodes(array $nodes, array $connections, array $executedIds): array
    {
        $connectedTo = [];
        foreach ($connections as $conn) {
            $connectedTo[$conn['to']] = true;
        }

        $entryIds = [];
        foreach ($nodes as $node) {
            if (in_array($node['id'], $executedIds)) {
                continue;
            }
            if ($node['kind'] === 'trigger' && $node['type'] === 'lead_enters_funnel') {
                if (!isset($connectedTo[$node['id']])) {
                    continue;
                }
                $entryIds[] = $node['id'];
            }
            if ($node['kind'] === 'action' && $node['type'] === 'wait') {
                if (in_array($node['id'], $executedIds)) {
                    $nextIds = $this->getNextNodes($node['id'], $connections);
                    foreach ($nextIds as $nid) {
                        if (!in_array($nid, $executedIds)) {
                            $entryIds[] = $nid;
                        }
                    }
                }
            }
        }

        return $entryIds;
    }

    protected function traverseNode(string $nodeId, array $nodeMap, array $outgoing, FunnelLead $funnelLead, array &$executedIds)
    {
        if (in_array($nodeId, $executedIds)) {
            return;
        }
        if (!isset($nodeMap[$nodeId])) {
            return;
        }

        $node = $nodeMap[$nodeId];
        $executedIds[] = $nodeId;

        if ($node['kind'] === 'trigger') {
            $executedIds[] = $nodeId;
        } elseif ($node['kind'] === 'action') {
            $this->executeAction($node, $funnelLead);
            if ($node['type'] === 'wait') {
                $funnelLead->workflow_state = array_merge(
                    $funnelLead->workflow_state ?? [],
                    ['executed' => $executedIds, 'waiting_node' => $nodeId]
                );
                $funnelLead->save();
                return;
            }
        } elseif ($node['kind'] === 'logic') {
            $result = $this->evaluateLogic($node, $funnelLead);
            if (!$result) {
                return;
            }
        }

        $funnelLead->workflow_state = array_merge(
            $funnelLead->workflow_state ?? [],
            ['executed' => $executedIds]
        );
        $funnelLead->save();

        $nextIds = $outgoing[$nodeId] ?? [];
        foreach ($nextIds as $nextId) {
            $this->traverseNode($nextId, $nodeMap, $outgoing, $funnelLead, $executedIds);
        }
    }

    protected function getNextNodes(string $nodeId, array $connections): array
    {
        $next = [];
        foreach ($connections as $conn) {
            if ($conn['from'] === $nodeId) {
                $next[] = $conn['to'];
            }
        }
        return $next;
    }

    protected function executeAction(array $node, FunnelLead $funnelLead)
    {
        $config = $node['config'] ?? [];
        $lead = $funnelLead->lead;
        if (!$lead) {
            return;
        }

        switch ($node['type']) {
            case 'send_email':
            case 'enroll_sequence':
                $sequenceId = $config['sequence'] ?? null;
                if ($sequenceId) {
                    $sequence = EmailSequence::with('steps')->find($sequenceId);
                    if ($sequence && $sequence->is_active) {
                        if (!$lead->sequence_id) {
                            $lead->sequence_id = $sequence->id;
                            $lead->enrolled_at = now();
                            $lead->save();
                        }
                        foreach ($sequence->steps as $step) {
                            $exists = EmailQueue::where('lead_id', $lead->id)
                                ->where('sequence_step_id', $step->id)
                                ->exists();
                            if (!$exists) {
                                $delayDays = $step->delay_days ?? 0;
                                $delayHours = $step->delay_hours ?? 0;
                                EmailQueue::create([
                                    'lead_id' => $lead->id,
                                    'sequence_step_id' => $step->id,
                                    'subject' => $step->subject,
                                    'scheduled_send_time' => Carbon::now()->addDays($delayDays)->addHours($delayHours),
                                    'status' => 'pending',
                                ]);
                            }
                        }
                    }
                }
                break;

            case 'add_tag':
                $tagId = $config['tag'] ?? null;
                if ($tagId) {
                    $tag = Tag::find($tagId);
                    if ($tag && !$lead->tags()->where('tag_id', $tag->id)->exists()) {
                        $lead->tags()->attach($tag->id);
                    }
                }
                break;

            case 'remove_tag':
                $tagId = $config['tag'] ?? null;
                if ($tagId) {
                    $lead->tags()->detach($tagId);
                }
                break;

            case 'update_score':
                $action = $config['action'] ?? 'add';
                $points = (int) ($config['value'] ?? 0);
                if ($action === 'add') {
                    $lead->addScore($points);
                } elseif ($action === 'subtract') {
                    $lead->removeScore($points);
                } elseif ($action === 'set') {
                    $lead->update(['score' => $points]);
                }
                break;

            case 'wait':
                $value = (int) ($config['value'] ?? 1);
                $unit = $config['unit'] ?? 'days';
                if ($unit === 'minutes') {
                    $funnelLead->wait_until = Carbon::now()->addMinutes($value);
                } elseif ($unit === 'hours') {
                    $funnelLead->wait_until = Carbon::now()->addHours($value);
                } else {
                    $funnelLead->wait_until = Carbon::now()->addDays($value);
                }
                $funnelLead->save();
                break;

            case 'webhook':
                $webhookUrl = $config['webhook'] ?? null;
                if ($webhookUrl) {
                    try {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                            'event' => 'automation_workflow',
                            'funnel_id' => $funnelLead->funnel_id,
                            'lead_id' => $lead->id,
                            'email' => $lead->email,
                            'name' => $lead->name,
                            'timestamp' => now()->toIso8601String(),
                        ]));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        curl_exec($ch);
                        curl_close($ch);
                    } catch (\Exception $e) {
                    }
                }
                break;

            case 'notify':
                $message = $config['message'] ?? 'Automation triggered';
                $subject = $config['subject'] ?? 'Automation Notification';
                $this->sendNotificationEmail($lead, $subject, $message);
                break;
        }
    }

    protected function evaluateLogic(array $node, FunnelLead $funnelLead): bool
    {
        $config = $node['config'] ?? [];
        $type = $node['type'];

        if ($type === 'split') {
            $percentage = (int) ($config['percentage'] ?? 50);
            $variant = crc32($funnelLead->id . '_' . $node['id']) % 100;
            $funnelLead->ab_variant = $variant < $percentage ? 'A' : 'B';
            $funnelLead->save();
            return true;
        }

        if ($type === 'if_condition') {
            $field = $config['field'] ?? '';
            $operator = $config['operator'] ?? 'equals';
            $value = $config['value'] ?? '';

            $actualValue = $this->getFieldValue($field, $funnelLead);

            return $this->compareValues($actualValue, $operator, $value);
        }

        return true;
    }

    protected function getFieldValue(string $field, FunnelLead $funnelLead)
    {
        $lead = $funnelLead->lead;
        switch ($field) {
            case 'score':
                return $funnelLead->score ?? 0;
            case 'lead_score':
                return $lead ? $lead->score : 0;
            case 'email_opens':
                return $funnelLead->email_opens ?? 0;
            case 'clicks':
                return $funnelLead->clicks_count ?? 0;
            case 'times_visited':
                return $funnelLead->times_visited ?? 0;
            case 'converted':
                return $funnelLead->converted ? 1 : 0;
            default:
                return $lead ? $lead->{$field} : null;
        }
    }

    protected function compareValues($actual, string $operator, $expected): bool
    {
        switch ($operator) {
            case 'equals':
            case '==':
            case '=':
                return (string) $actual === (string) $expected;
            case '!=':
            case 'not_equals':
                return (string) $actual !== (string) $expected;
            case '>':
            case 'greater':
                return (float) $actual > (float) $expected;
            case '>=':
            case 'greater_or_equal':
                return (float) $actual >= (float) $expected;
            case '<':
            case 'less':
                return (float) $actual < (float) $expected;
            case '<=':
            case 'less_or_equal':
                return (float) $actual <= (float) $expected;
            case 'contains':
                return str_contains((string) $actual, (string) $expected);
            case 'not_contains':
                return !str_contains((string) $actual, (string) $expected);
            default:
                return (string) $actual === (string) $expected;
        }
    }

    protected function sendNotificationEmail(Lead $lead, string $subject, string $message)
    {
        try {
            $apiKey = Setting::get('brevo_api_key');
            if (empty($apiKey)) return;

            $fromEmail = Setting::get('mail_from_address', 'campaigns@joala.com.ng');
            $fromName = Setting::get('mail_from_name', 'JoAla');
            $adminEmail = Setting::get('contact_email', 'jomealawuru@hotmail.com');

            $html = '<h2>' . e($subject) . '</h2>'
                . '<p><strong>Lead:</strong> ' . e($lead->name ?? $lead->email) . '</p>'
                . '<p><strong>Email:</strong> ' . e($lead->email) . '</p>'
                . '<p>' . nl2br(e($message)) . '</p>';

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
                "to" => [["email" => $adminEmail, "name" => "Admin"]],
                "subject" => $subject,
                "htmlContent" => $html,
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
        }
    }
}
