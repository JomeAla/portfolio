<?php

namespace App\Http\Controllers\Admin\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Marketing\Lead;
use App\Models\Marketing\TwitterSetting;
use App\Services\Marketing\MarketingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(MarketingService $marketing)
    {
        $stats = $marketing->getDashboardStats();
        $recentLeads = Lead::latest()->limit(10)->get();
        $twitterConnected = TwitterSetting::first() && TwitterSetting::first()->access_token;
        
        return view('admin.marketing.dashboard', compact('stats', 'recentLeads', 'twitterConnected'));
    }

    public function settings()
    {
        $twitterSettings = TwitterSetting::first();
        return view('admin.marketing.settings', compact('twitterSettings'));
    }

    public function updateTwitterSettings(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
        ]);

        $settings = TwitterSetting::first() ?? new TwitterSetting();
        $settings->client_id = $request->client_id;
        $settings->client_secret = $request->client_secret;
        $settings->save();

        return back()->with('success', 'Twitter settings updated.');
    }

    public function connectTwitter(MarketingService $marketing)
    {
        $authUrl = $marketing->getTwitterAuthUrl();
        if ($authUrl) {
            return redirect($authUrl);
        }
        return back()->with('error', 'Twitter OAuth not configured. Add Client ID and Secret first.');
    }

    public function twitterCallback(Request $request, MarketingService $marketing)
    {
        if ($request->has('error')) {
            return redirect()->route('admin.marketing.settings')->with('error', 'Twitter authorization failed.');
        }

        if ($request->has('code')) {
            $state = session('twitter_oauth_state');
            if ($request->state !== $state) {
                return redirect()->route('admin.marketing.settings')->with('error', 'Invalid state parameter.');
            }

            $success = $marketing->handleTwitterCallback($request->code);
            if ($success) {
                return redirect()->route('admin.marketing.settings')->with('success', 'Twitter connected successfully!');
            }
        }

        return redirect()->route('admin.marketing.settings')->with('error', 'Failed to connect Twitter.');
    }

    public function disconnectTwitter()
    {
        $settings = TwitterSetting::first();
        if ($settings) {
            $settings->update([
                'access_token' => null,
                'refresh_token' => null,
                'expires_at' => null,
            ]);
        }
        return back()->with('success', 'Twitter disconnected.');
    }

    public function leads()
    {
        $leads = Lead::with(['landingPage', 'sequence'])->latest()->paginate(20);
        return view('admin.marketing.leads', compact('leads'));
    }

    public function exportLeads()
    {
        $leads = Lead::with(['landingPage', 'sequence'])->get();
        
        $csv = "Email,Name,Source,Page,Sequence,Created At\n";
        foreach ($leads as $lead) {
            $csv .= sprintf('%s,%s,%s,%s,%s,%s\n',
                $lead->email,
                $lead->name ?? '',
                $lead->source ?? '',
                $lead->landingPage->title ?? '',
                $lead->sequence->name ?? '',
                $lead->created_at->toDateTimeString()
            );
        }
        
        return response($csv)->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=leads.csv');
    }
}