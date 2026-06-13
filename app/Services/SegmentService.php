<?php

namespace App\Services;

use App\Models\Segment;
use App\Models\Lead;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;

class SegmentService
{
    public function syncAllSegments(): array
    {
        $results = [];
        $segments = Segment::where('is_active', true)->get();

        foreach ($segments as $segment) {
            $result = $this->syncSegment($segment);
            $results[$segment->name] = $result;
        }

        return $results;
    }

    public function syncSegment(Segment $segment): array
    {
        if (!$segment->is_active) {
            return ['added' => 0, 'removed' => 0, 'skipped' => true];
        }

        return $segment->syncLeads();
    }

    public function evaluateLeadForSegments(Lead $lead): array
    {
        $results = [];
        $activeSegments = Segment::where('is_active', true)->get();

        foreach ($activeSegments as $segment) {
            $matches = $segment->applyConditions(Lead::where('id', $lead->id))->exists();
            $isMember = $segment->leads()->where('leads.id', $lead->id)->exists();

            if ($matches && !$isMember) {
                $segment->leads()->attach($lead->id, ['synced_at' => now()]);
                if ($segment->auto_tag_key) {
                    $tag = Tag::firstOrCreate(['name' => $segment->auto_tag_key]);
                    $lead->tags()->syncWithoutDetaching([$tag->id]);
                }
                $results[$segment->name] = 'added';
            } elseif (!$matches && $isMember) {
                $segment->leads()->detach($lead->id);
                if ($segment->auto_tag_key) {
                    $tag = Tag::where('name', $segment->auto_tag_key)->first();
                    if ($tag) {
                        $lead->tags()->detach($tag->id);
                    }
                }
                $results[$segment->name] = 'removed';
            }
        }

        return $results;
    }

    public function onLeadCreated(Lead $lead): void
    {
        $results = $this->evaluateLeadForSegments($lead);
        if (!empty($results)) {
            Log::info("Lead #{$lead->id} ({$lead->email}) evaluated against segments", $results);
        }
    }

    public function onLeadUpdated(Lead $lead): void
    {
        $results = $this->evaluateLeadForSegments($lead);
        if (!empty($results)) {
            Log::info("Lead #{$lead->id} ({$lead->email}) re-evaluated segments after update", $results);
        }
    }

    public function onLeadActivity(Lead $lead): void
    {
        $this->evaluateLeadForSegments($lead);
    }

    public function getSegmentsForLead(Lead $lead): array
    {
        return $lead->segments()->get()->toArray();
    }

    public function getSegmentMembers(Segment $segment): array
    {
        return $segment->leads()->get()->toArray();
    }

    public function getSegmentStats(Segment $segment): array
    {
        return [
            'total_members' => $segment->leads()->count(),
            'total_matching' => $segment->applyConditions(Lead::query())->count(),
            'added_today' => $segment->leads()
                ->wherePivot('synced_at', '>=', now()->startOfDay())
                ->count(),
        ];
    }
}