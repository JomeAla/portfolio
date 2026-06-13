<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Funnel;
use App\Models\FunnelLead;
use App\Services\Marketing\MarketingService;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function show($slug)
    {
        $page = LandingPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        return view('front.landing-page', compact('page'));
    }

    public function submitLead(Request $request, MarketingService $marketing)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

        $pageId = $request->input('landing_page_id');
        $page = $pageId ? LandingPage::find($pageId) : null;
        
        $sequenceId = $page?->sequence_id;
        
        $nextUrl = $request->input('next_url');

        $utmData = [
            'utm_source' => $request->input('utm_source') ?? session('utm_source'),
            'utm_medium' => $request->input('utm_medium') ?? session('utm_medium'),
            'utm_campaign' => $request->input('utm_campaign') ?? session('utm_campaign'),
            'utm_term' => $request->input('utm_term') ?? session('utm_term'),
            'utm_content' => $request->input('utm_content') ?? session('utm_content'),
            'referrer_url' => $request->input('referrer_url') ?? request()->headers->get('referer'),
        ];

        foreach ($utmData as $key => $val) {
            if ($val) {
                session([$key => $val]);
            }
        }

        $leadData = array_merge([
            'landing_page_id' => $pageId,
            'sequence_id' => $sequenceId,
        ], array_filter($utmData, fn($v) => $v !== null));

        $lead = $marketing->createLeadWithUtm(
            $request->email,
            $request->name,
            'landing_page',
            $pageId,
            $sequenceId,
            $utmData
        );

        if ($page && $page->funnel_id) {
            $funnel = $page->funnel;
            if ($funnel) {
                $firstStage = $funnel->stages()->orderBy('order')->first();
                
                FunnelLead::updateOrCreate(
                    [
                        'funnel_id' => $funnel->id,
                        'email' => $request->email,
                    ],
                    [
                        'lead_id' => $lead->id,
                        'stage_id' => $firstStage?->id,
                        'entered_at' => now(),
                        'source' => 'landing_page',
                    ]
                );

                if ($funnel->welcome_sequence_id) {
                    $marketing->enrollLeadInFunnel($lead, $funnel);
                }

                if ($nextUrl) {
                    $nextUrl .= (str_contains($nextUrl, '?') ? '&' : '?') . 'email=' . urlencode($request->email);
                    return response()->json([
                        'success' => true,
                        'redirect' => $nextUrl
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you! We\'ll be in touch soon.'
        ]);
    }
}