<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Funnel;
use App\Models\FunnelStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FunnelDeployController extends Controller
{
    public function showDeployForm(Funnel $funnel)
    {
        $stagingFunnel = $funnel;
        $productionFunnel = Funnel::where('name', $funnel->name)
            ->where('environment', 'production')
            ->where('id', '!=', $funnel->id)
            ->first();
        
        return view('admin.marketing.funnels.deploy', compact('stagingFunnel', 'productionFunnel'));
    }

    public function deployToProduction(Request $request, Funnel $funnel)
    {
        $request->validate([
            'mode' => 'required|in:clone,replace,export',
        ]);

        $exportData = $this->exportFunnelData($funnel);

        if ($request->mode === 'export') {
            return $this->downloadExport($exportData);
        }

        if ($request->mode === 'clone') {
            $production = $this->cloneToProduction($funnel);
            return back()->with('success', "Funnel cloned to production! New ID: {$production->id}");
        }

        if ($request->mode === 'replace') {
            $production = $this->replaceProduction($funnel);
            return back()->with('success', "Production funnel updated successfully!");
        }

        return back()->with('error', 'Invalid deployment mode.');
    }

    public function exportFunnelData(Funnel $funnel)
    {
        $funnel->load('stages');
        
        return [
            'name' => $funnel->name,
            'description' => $funnel->description,
            'goal' => $funnel->goal,
            'stages' => $funnel->stages->map(function ($stage) {
                return [
                    'name' => $stage->name,
                    'type' => $stage->type,
                    'content' => $stage->content,
                    'order' => $stage->order,
                    'delay_days' => $stage->delay_days,
                ];
            })->toArray(),
            'automation_enabled' => $funnel->automation_enabled,
            'welcome_sequence_id' => $funnel->welcome_sequence_id,
            'followup_sequence_id' => $funnel->followup_sequence_id,
            'automation_workflows' => $funnel->automation_workflows,
            'upsell_enabled' => $funnel->upsell_enabled,
            'upsell_product_id' => $funnel->upsell_product_id,
            'upsell_discount' => $funnel->upsell_discount,
            'order_bumps_enabled' => $funnel->order_bumps_enabled,
            'order_bumps' => $funnel->order_bumps,
            'facebook_pixel' => $funnel->facebook_pixel,
            'google_pixel' => $funnel->google_pixel,
            'countdown_enabled' => $funnel->countdown_enabled,
            'exported_at' => now()->toIso8601String(),
            'version' => '1.0',
        ];
    }

    protected function downloadExport(array $data)
    {
        $filename = Str::slug($data['name']) . '_' . date('Ymd') . '.json';
        
        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Content-Type' => 'application/json',
        ]);
    }

    protected function cloneToProduction(Funnel $staging)
    {
        $production = $staging->replicate();
        $production->name = $staging->name . ' (Production)';
        $production->environment = 'production';
        $production->deployed_at = now();
        $production->save();

        foreach ($staging->stages as $stage) {
            $newStage = $stage->replicate();
            $newStage->funnel_id = $production->id;
            $newStage->save();
        }

        $history = $staging->deployment_history ?? [];
        $history[] = [
            'action' => 'cloned',
            'deployed_at' => now()->toIso8601String(),
            'from_id' => $staging->id,
            'to_id' => $production->id,
        ];
        $production->deployment_history = $history;
        $production->save();

        return $production;
    }

    protected function replaceProduction(Funnel $staging)
    {
        $production = Funnel::where('name', $staging->name)
            ->where('environment', 'production')
            ->first();

        if (!$production) {
            return $this->cloneToProduction($staging);
        }

        $production->update([
            'description' => $staging->description,
            'goal' => $staging->goal,
            'deployed_at' => now(),
        ]);

        FunnelStage::where('funnel_id', $production->id)->delete();

        foreach ($staging->stages as $stage) {
            $newStage = $stage->replicate();
            $newStage->funnel_id = $production->id;
            $newStage->save();
        }

        $fillable = [
            'automation_enabled', 'welcome_sequence_id', 'followup_sequence_id',
            'automation_workflows', 'upsell_enabled', 'upsell_product_id',
            'upsell_discount', 'order_bumps_enabled', 'order_bumps',
            'facebook_pixel', 'google_pixel', 'countdown_enabled',
        ];

        foreach ($fillable as $field) {
            if ($staging->{$field} !== null) {
                $production->{$field} = $staging->{$field};
            }
        }
        $production->save();

        return $production;
    }

    public function importForm()
    {
        return view('admin.marketing.funnels.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|mimes:json',
            'mode' => 'required|in:new,merge',
        ]);

        $file = $request->file('json_file');
        $data = json_decode(file_get_contents($file->getRealPath()), true);

        if (!$data || !isset($data['name'])) {
            return back()->with('error', 'Invalid funnel data file.');
        }

        $funnelData = [
            'description' => $data['description'] ?? null,
            'goal' => $data['goal'] ?? 'lead_capture',
            'environment' => 'staging',
            'is_active' => false,
        ];

        $exportedFields = [
            'funnel_type', 'product_id', 'service_id',
            'automation_enabled', 'welcome_sequence_id', 'followup_sequence_id',
            'automation_workflows', 'webhook_url', 'webhook_enabled', 'notify_email',
            'upsell_enabled', 'upsell_product_id', 'upsell_discount', 'upsell_timer',
            'order_bumps_enabled', 'order_bumps',
            'facebook_pixel', 'google_pixel',
            'countdown_enabled', 'countdown_hours',
            'thank_you_title', 'thank_you_message', 'thank_you_video',
            'upsell_button_text',
            'exit_popup_enabled', 'exit_popup_offer', 'exit_popup_discount',
            'starts_at', 'ends_at',
            'refund_policy', 'refund_period_days',
            'affiliate_enabled', 'affiliate_commission', 'affiliate_cookie_days',
            'score_per_page', 'score_per_email', 'score_per_checkout', 'score_per_click',
            'score_hot_threshold', 'hot_lead_tag',
            'ab_testing_enabled', 'ab_variants', 'ab_traffic_split', 'ab_winner',
            'ab_started_at', 'ab_min_sample_size', 'ab_confidence_level',
            'is_template', 'template_category',
            'stage_order',
        ];

        foreach ($exportedFields as $field) {
            if (array_key_exists($field, $data)) {
                $funnelData[$field] = $data[$field];
            }
        }

        if ($request->mode === 'new') {
            $funnelData['name'] = $data['name'] . ' (Imported)';
        } else {
            $funnelData['name'] = $data['name'];
        }

        $funnel = Funnel::create($funnelData);
        $this->createStagesFromImport($funnel, $data);

        return redirect("/admin/marketing/funnels/{$funnel->id}/edit")
            ->with('success', 'Funnel imported successfully!');
    }

    protected function createStagesFromImport(Funnel $funnel, array $data)
    {
        if (!isset($data['stages'])) return;

        foreach ($data['stages'] as $index => $stageData) {
            $stageFields = [
                'funnel_id' => $funnel->id,
                'name' => $stageData['name'],
                'type' => $stageData['type'] ?? 'landing',
                'content' => $stageData['content'] ?? [],
                'order' => $index + 1,
                'delay_days' => $stageData['delay_days'] ?? 0,
            ];

            $stageExportedFields = [
                'is_required', 'sequence_id', 'email_template', 'delay_hours',
                'condition_type', 'condition_value', 'is_skippable',
                'action_on_complete', 'action_config', 'points_to_award',
                'wait_duration_hours', 'wait_until_type', 'wait_until_value',
                'redirect_type', 'conditional_stages',
            ];

            foreach ($stageExportedFields as $field) {
                if (array_key_exists($field, $stageData)) {
                    $stageFields[$field] = $stageData[$field];
                }
            }

            FunnelStage::create($stageFields);
        }
    }
}