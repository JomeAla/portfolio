<?php

namespace App\Console\Commands;

use App\Models\Segment;
use App\Services\SegmentService;
use Illuminate\Console\Command;

class SyncSegments extends Command
{
    protected $signature = 'segments:sync {--segment= : Sync specific segment by ID or name}';
    protected $description = 'Sync all active segments with leads based on conditions';

    public function handle(SegmentService $service): int
    {
        $segmentId = $this->option('segment');

        if ($segmentId) {
            $segment = is_numeric($segmentId)
                ? Segment::find((int) $segmentId)
                : Segment::where('name', $segmentId)->first();

            if (!$segment) {
                $this->error("Segment not found: {$segmentId}");
                return 1;
            }

            $this->info("Syncing segment: {$segment->name}...");
            $result = $service->syncSegment($segment);
            $this->info("  Added: {$result['added']}, Removed: {$result['removed']}");

            return 0;
        }

        $segments = Segment::where('is_active', true)->get();
        
        if ($segments->isEmpty()) {
            $this->info("No active segments found.");
            return 0;
        }

        $this->info("Syncing {$segments->count()} active segment(s)...");
        $results = $service->syncAllSegments();
        
        $totalAdded = 0;
        $totalRemoved = 0;

        foreach ($results as $name => $result) {
            if (isset($result['skipped'])) {
                $this->line("  Skipped: {$name}");
                continue;
            }
            $this->info("  {$name}: +{$result['added']} / -{$result['removed']}");
            $totalAdded += $result['added'];
            $totalRemoved += $result['removed'];
        }

        $this->info("Done. Total: +{$totalAdded} leads, -{$totalRemoved} leads.");
        return 0;
    }
}