<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funnel;
use App\Models\FunnelLead;
use Illuminate\Support\Facades\Log;

class ProcessAbTests extends Command
{
    protected $signature = 'abtests:process {--funnel= : Process specific funnel ID}';
    protected $description = 'Evaluate A/B test results and auto-declare winners when statistically significant';

    public function handle(): int
    {
        $funnelId = $this->option('funnel');

        $query = Funnel::where('ab_testing_enabled', true);
        if ($funnelId) {
            $query->where('id', $funnelId);
        }

        $funnels = $query->get();

        if ($funnels->isEmpty()) {
            $this->info('No active A/B tests found.');
            return 0;
        }

        $this->info("Processing {$funnels->count()} A/B test(s)...");

        foreach ($funnels as $funnel) {
            $this->processFunnel($funnel);
        }

        return 0;
    }

    protected function processFunnel(Funnel $funnel): void
    {
        $this->info("Evaluating: {$funnel->name} (ID: {$funnel->id})");

        $variants = $funnel->ab_variants ?? [];
        if (empty($variants)) {
            $this->warn("  No variants defined for funnel {$funnel->id}");
            return;
        }

        $minSample = $funnel->ab_min_sample_size ?? 100;
        $confidenceLevel = $funnel->ab_confidence_level ?? 95;

        $variantStats = [];
        $allVisitors = 0;
        $totalConversions = 0;

        foreach ($variants as $key => $data) {
            $visitors = FunnelLead::where('funnel_id', $funnel->id)
                ->where('ab_variant', $key)
                ->count();

            $conversions = FunnelLead::where('funnel_id', $funnel->id)
                ->where('ab_variant', $key)
                ->where('converted', true)
                ->count();

            $rate = $visitors > 0 ? ($conversions / $visitors) * 100 : 0;

            $variantStats[$key] = [
                'visitors' => $visitors,
                'conversions' => $conversions,
                'rate' => $rate,
                'name' => $data['name'] ?? ucfirst($key),
            ];

            $allVisitors += $visitors;
            $totalConversions += $conversions;
        }

        $minVisitors = min(array_column($variantStats, 'visitors'));

        if ($allVisitors < 10) {
            $this->warn("  Not enough data yet ({$allVisitors} total visitors)");
            return;
        }

        if ($minVisitors < $minSample) {
            $this->warn("  Minimum sample size not reached. Variant '{$this->getLowestVariant($variantStats)}' has {$minVisitors} (need {$minSample})");
            return;
        }

        $result = $this->calculateSignificance($variantStats, $confidenceLevel);

        if ($result['significant']) {
            $winner = $result['winner'];
            $confidence = $result['confidence_level'];

            $this->info("  🎉 WINNER: Variant " . strtoupper($winner) . " ({$variantStats[$winner]['rate']}%) with {$confidence}% confidence");
            $this->info("  Visitors: {$variantStats[$winner]['visitors']}, Conversions: {$variantStats[$winner]['conversions']}");
            $this->info("  Z-score: {$result['z_score']}");

            $funnel->update([
                'ab_winner' => $winner,
                'ab_testing_enabled' => false,
            ]);

            Log::info("A/B test auto-winner declared for funnel {$funnel->id}", [
                'winner' => $winner,
                'confidence' => $confidence,
                'z_score' => $result['z_score'],
                'variant_a' => $variantStats['a'] ?? null,
                'variant_b' => $variantStats['b'] ?? null,
            ]);

            $this->info("  ✅ Funnel '{$funnel->name}' updated: winner={$winner}, test disabled");
        } else {
            $this->info("  ⏳ Not yet significant: {$result['reason']}");
            $this->info("  Best so far: " . ucfirst($result['best_variant']) . " at " . round($variantStats[$result['best_variant']]['rate'], 2) . "%");
        }
    }

    protected function calculateSignificance(array $variants, float $confidenceLevel): array
    {
        $variantKeys = array_keys($variants);

        if (count($variantKeys) < 2) {
            return ['significant' => false, 'reason' => 'Need at least 2 variants'];
        }

        $v1 = $variants[$variantKeys[0]];
        $v2 = $variants[$variantKeys[1]];

        $n1 = $v1['visitors'];
        $n2 = $v2['visitors'];
        $p1 = $v1['rate'] / 100;
        $p2 = $v2['rate'] / 100;

        if ($n1 < 10 || $n2 < 10) {
            return [
                'significant' => false,
                'reason' => 'Sample size too small for statistical test',
                'best_variant' => $v1['rate'] >= $v2['rate'] ? $variantKeys[0] : $variantKeys[1],
            ];
        }

        $pPooled = (($v1['conversions'] + $v2['conversions']) / ($n1 + $n2));
        if ($pPooled == 0 || $pPooled == 1) {
            $pPooled = 0.5;
        }

        $se = sqrt($pPooled * (1 - $pPooled) * (1 / $n1 + 1 / $n2));
        if ($se == 0) {
            return [
                'significant' => false,
                'reason' => 'Standard error is zero (no variation)',
                'best_variant' => $v1['rate'] >= $v2['rate'] ? $variantKeys[0] : $variantKeys[1],
            ];
        }

        $zScore = ($p1 - $p2) / $se;
        $absZ = abs($zScore);

        $zThresholds = [
            90 => 1.645,
            95 => 1.96,
            99 => 2.576,
        ];
        $zThreshold = $zThresholds[$confidenceLevel] ?? 1.96;

        $isSignificant = $absZ >= $zThreshold;

        $confidencePercent = $this->zToConfidence($absZ);

        $winner = $p1 > $p2 ? $variantKeys[0] : $variantKeys[1];

        if (!$isSignificant) {
            return [
                'significant' => false,
                'reason' => "Z-score {$absZ} below threshold {$zThreshold} ({$confidencePercent}% vs {$confidenceLevel}% needed)",
                'z_score' => round($zScore, 4),
                'best_variant' => $winner,
            ];
        }

        return [
            'significant' => true,
            'winner' => $winner,
            'confidence_level' => round($confidencePercent, 1),
            'z_score' => round($zScore, 4),
        ];
    }

    protected function zToConfidence(float $z): float
    {
        $z = abs($z);
        if ($z >= 3.3) return 99.9;
        if ($z >= 2.88) return 99.6;
        if ($z >= 2.58) return 99.0;
        if ($z >= 2.33) return 98.0;
        if ($z >= 1.96) return 95.0;
        if ($z >= 1.64) return 90.0;
        if ($z >= 1.28) return 80.0;
        if ($z >= 1.0) return 68.0;
        return $z * 68;
    }

    protected function getLowestVariant(array $variants): string
    {
        $lowest = null;
        $minVisitors = PHP_INT_MAX;
        foreach ($variants as $key => $data) {
            if ($data['visitors'] < $minVisitors) {
                $minVisitors = $data['visitors'];
                $lowest = $key;
            }
        }
        return $lowest;
    }
}