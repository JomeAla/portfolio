<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\FunnelLead;
use App\Models\EmailOpen;
use App\Models\EmailQueue;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class RecalculateLeadScores extends Command
{
    protected $signature = 'leads:score {--all : Recalculate all leads} {--limit=100 : Limit for batch processing}';
    protected $description = 'Recalculate lead scores based on activity and rules';

    public function handle()
    {
        $all = $this->option('all');
        $limit = (int) $this->option('limit');

        $query = Lead::query();

        if (!$all) {
            $query->where(function($q) {
                $q->whereNotNull('updated_at')
                  ->where('updated_at', '>', now()->subDay());
            });
        }

        $query->limit($limit);

        $leads = $query->get();
        
        $this->info("Recalculating scores for {$leads->count()} leads...");

        foreach ($leads as $lead) {
            $this->calculateScore($lead);
        }

        $this->info("Lead scoring complete!");
        
        return 0;
    }

    protected function calculateScore($lead)
    {
        $totalScore = 0;

        $behavioralScore = $this->calculateBehavioralScore($lead);
        $engagementScore = $this->calculateEngagementScore($lead);

        $totalScore = $behavioralScore + $engagementScore;

        $oldScore = $lead->score ?? 0;
        
        $lead->update([
            'score' => max(0, min(200, $totalScore)),
        ]);

        if ($oldScore !== $lead->score) {
            $this->line("Lead {$lead->id} ({$lead->email}): {$oldScore} -> {$lead->score}");
        }
    }

    protected function calculateBehavioralScore($lead)
    {
        $score = 0;

        if ($lead->emails()->count() > 0) {
            $sentCount = $lead->emails()->count();
            $score += min(20, $sentCount * 2);
        }

        $activitiesCount = $lead->activities()->count();
        if ($activitiesCount > 0) {
            $score += min(30, $activitiesCount * 3);
        }

        $dealsCount = $lead->deals()->count();
        if ($dealsCount > 0) {
            $score += min(25, $dealsCount * 10);
        }

        $lastActivity = $lead->updated_at;
        if ($lastActivity) {
            $daysSinceActivity = $lastActivity->diffInDays(now());
            if ($daysSinceActivity <= 1) {
                $score += 15;
            } elseif ($daysSinceActivity <= 7) {
                $score += 8;
            } elseif ($daysSinceActivity <= 30) {
                $score += 3;
            }
        }

        $funnelLeads = FunnelLead::where('email', $lead->email)->count();
        $score += min(20, $funnelLeads * 5);

        $tagsCount = $lead->tags()->count();
        $score += min(15, $tagsCount * 3);

        return $score;
    }

    protected function calculateEngagementScore($lead)
    {
        $score = 0;

        $tasksCompleted = $lead->tasks()->where('status', 'completed')->count();
        $score += min(20, $tasksCompleted * 5);

        try {
            $recentOrders = Order::where('email', $lead->email)->where('created_at', '>', now()->subDays(90))->count();
            $score += min(30, $recentOrders * 15);
        } catch (\Exception $e) {
        }

        try {
            $openCount = EmailOpen::whereHas('emailQueue', fn($q) => $q->where('lead_id', $lead->id))->count();
            $score += min(25, $openCount * 5);
        } catch (\Exception $e) {
        }

        $funnelConverted = FunnelLead::where('email', $lead->email)->where('converted', true)->count();
        $score += min(40, $funnelConverted * 20);

        return $score;
    }
}