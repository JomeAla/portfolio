<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\TweetQueue;
use App\Models\LandingPage;
use App\Models\Lead;
use App\Models\EmailSequence;
use App\Models\SequenceStep;
use App\Models\EmailQueue;
use App\Models\TwitterSetting;
use App\Models\EmailOpen;
use App\Models\Setting;
use App\Models\Funnel;
use App\Models\FunnelStage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\StoreBlogPostRequest;
use App\Http\Requests\UpdateBlogPostRequest;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\StoreAutomationRuleRequest;
use App\Http\Requests\StoreEmailTemplateRequest;
use App\Http\Requests\StoreFunnelRequest;
use App\Http\Requests\StoreSequenceRequest;
use App\Http\Requests\StoreSegmentRequest;
use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Http\Requests\UpdateAutomationRuleRequest;
use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\UpdateSegmentRequest;
use App\Http\Requests\UpdateDealRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use PDO;
use Carbon\Carbon;

function safeCount($model) {
    try {
        return $model->count();
    } catch (\Exception $e) {
        return 0;
    }
}

class MarketingController extends Controller
{
    
    public function dashboard()
    {
        try {
            $emailSentCount = \App\Models\EmailQueue::sent()->count() ?? 0;
            $emailOpenedCount = \App\Models\EmailQueue::sent()->opened()->count() ?? 0;
            $emailClickedCount = \App\Models\EmailQueue::sent()->clicked()->count() ?? 0;

            $stats = [
                'total_leads' => \App\Models\Lead::count() ?? 0,
                'active_leads' => \App\Models\Lead::where('status', 'active')->count() ?? 0,
                'total_blog_posts' => \App\Models\BlogPost::count() ?? 0,
                'published_posts' => \App\Models\BlogPost::where('is_published', true)->count() ?? 0,
                'pending_tweets' => \App\Models\TweetQueue::where('status', 'scheduled')->count() ?? 0,
                'sent_tweets' => \App\Models\TweetQueue::where('status', 'sent')->count() ?? 0,
                'email_queued' => \App\Models\EmailQueue::where('status', 'pending')->count() ?? 0,
                'email_sent' => $emailSentCount,
                'landing_pages' => \App\Models\LandingPage::count() ?? 0,
                'active_sequences' => \App\Models\EmailSequence::where('is_active', true)->count() ?? 0,
                'open_rate' => $emailSentCount > 0 ? round(($emailOpenedCount / $emailSentCount) * 100, 1) : 0,
                'click_rate' => $emailSentCount > 0 ? round(($emailClickedCount / $emailSentCount) * 100, 1) : 0,
            ];
        } catch (\Exception $e) {
            $stats = [
                'total_leads' => 0,
                'active_leads' => 0,
                'total_blog_posts' => 0,
                'published_posts' => 0,
                'pending_tweets' => 0,
                'sent_tweets' => 0,
                'email_queued' => 0,
                'email_sent' => 0,
                'landing_pages' => 0,
                'active_sequences' => 0,
                'open_rate' => 0,
                'click_rate' => 0,
            ];
        }
        
        try {
            $recent_leads = \App\Models\Lead::latest()->limit(10)->get();
        } catch (\Exception $e) {
            $recent_leads = collect([]);
        }
        
        $recent_tweets = collect([]);
        
        return view('admin.marketing.dashboard', compact('stats', 'recent_leads', 'recent_tweets'));
    }

    // Blog Posts
    public function blogIndex()
    {
        $posts = BlogPost::latest()->paginate(10);
        return view('admin.marketing.blog.index', compact('posts'));
    }

    public function blogCreate()
    {
        $funnels = Funnel::where('is_active', true)->get();
        return view('admin.marketing.blog.create', compact('funnels'));
    }

    public function blogStore(StoreBlogPostRequest $request)
    {
        try {
        $slug = $request->slug ?: Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = $request->validated();
        $data['slug'] = $slug;
        $data['is_published'] = $request->has('is_published');
        $data['post_to_twitter'] = $request->has('post_to_twitter');
        $data['show_popup'] = $request->has('show_popup');
        
        // Handle scheduled publishing
        if ($request->filled('published_at')) {
            $data['published_at'] = \Carbon\Carbon::parse($request->published_at);
            // If scheduling for future, mark as published
            if ($data['published_at']->isFuture()) {
                $data['is_published'] = true;
            }
        } elseif ($request->has('is_published')) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('featured_image_file')) {
            try {
                $file = $request->file('featured_image_file');
                if ($file->isValid()) {
                    $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                    // Use root uploads folder (same as products, projects)
                    $uploadDir = '/home/joalacom/public_html/uploads/blog';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if ($file->move($uploadDir, $filename)) {
                        chmod($uploadDir . '/' . $filename, 0644);
                        $data['featured_image'] = '/uploads/blog/' . $filename;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Featured image upload error: ' . $e->getMessage());
            }
        } elseif ($request->filled('featured_image')) {
            $data['featured_image'] = $request->featured_image;
        }

        if ($request->is_published && !$request->old_published) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        if ($request->has('post_to_twitter') && $request->is_published) {
            TweetQueue::create([
                'content' => 'New blog post: ' . $post->title . ' - ' . url('/blog/' . $post->slug),
                'blog_post_id' => $post->id,
                'status' => 'scheduled',
                'scheduled_send_time' => now(),
            ]);
        }

        return redirect('/admin/marketing/blog')->with('success', 'Blog post created.');
        } catch (\Exception $e) {
            \Log::error('BlogStore Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function blogEdit(BlogPost $blog)
    {
        $funnels = Funnel::where('is_active', true)->get();
        return view('admin.marketing.blog.edit', compact('blog', 'funnels'));
    }

    public function blogUpdate(UpdateBlogPostRequest $request, BlogPost $blog)
    {
        $data = $request->validated();
        
        if ($request->slug !== $blog->slug) {
            $slug = $request->slug ?: Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            while (BlogPost::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $data['slug'] = $slug;
        }

        $wasPublished = $blog->is_published;
        $data['is_published'] = $request->has('is_published');
        $data['post_to_twitter'] = $request->has('post_to_twitter');
        
        // Handle scheduled publishing
        if ($request->filled('published_at')) {
            $data['published_at'] = \Carbon\Carbon::parse($request->published_at);
            if ($data['published_at']->isFuture()) {
                $data['is_published'] = true;
            }
        }
        
        // Handle featured image upload
        if ($request->hasFile('featured_image_file')) {
            try {
                $file = $request->file('featured_image_file');
                if ($file->isValid()) {
                    $filename = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
                    // Use root uploads folder (same as products, projects)
                    $uploadDir = '/home/joalacom/public_html/uploads/blog';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if ($file->move($uploadDir, $filename)) {
                        chmod($uploadDir . '/' . $filename, 0644);
                        $data['featured_image'] = '/uploads/blog/' . $filename;
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Featured image upload error: ' . $e->getMessage());
            }
        } elseif ($request->filled('featured_image')) {
            $data['featured_image'] = $request->featured_image;
        }
        
        $blog->update($data);

        return redirect('/admin/marketing/blog')->with('success', 'Blog post updated.');
    }

    public function blogDestroy(BlogPost $blog)
    {
        $blog->delete();
        return redirect('/admin/marketing/blog')->with('success', 'Blog post deleted.');
    }

    // Tweets
    public function tweetsIndex()
    {
        $tweets = TweetQueue::orderBy('scheduled_at', 'desc')->paginate(10);
        return view('admin.marketing.tweets.index', compact('tweets'));
    }

    public function tweetsCreate()
    {
        $posts = BlogPost::where('is_published', true)->get();
        return view('admin.marketing.tweets.create', compact('posts'));
    }

    public function tweetsStore(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:280',
        ]);

        $data = $request->all();
        $data['status'] = $request->scheduled_send_time ? 'scheduled' : 'draft';
        $data['scheduled_send_time'] = $request->scheduled_send_time ? Carbon::parse($request->scheduled_send_time) : null;

        TweetQueue::create($data);

        return redirect('/admin/marketing/tweets')->with('success', 'Tweet queued.');
    }

    public function tweetsEdit(TweetQueue $tweet)
    {
        return view('admin.marketing.tweets.edit', compact('tweet'));
    }

    public function tweetsUpdate(Request $request, TweetQueue $tweet)
    {
        $request->validate([
            'content' => 'required|string|max:280',
        ]);

        $data = $request->all();
        $data['status'] = $request->scheduled_send_time ? 'scheduled' : 'draft';
        $data['scheduled_send_time'] = $request->scheduled_send_time ? Carbon::parse($request->scheduled_send_time) : null;

        $tweet->update($data);

        return redirect('/admin/marketing/tweets')->with('success', 'Tweet updated.');
    }

    public function tweetsDestroy(TweetQueue $tweet)
    {
        $tweet->delete();
        return redirect('/admin/marketing/tweets')->with('success', 'Tweet deleted.');
    }

    public function tweetsSendNow(TweetQueue $tweet)
    {
        $twitterSettings = TwitterSetting::first();
        
        if (!$twitterSettings || !$twitterSettings->oauth_token || !$twitterSettings->oauth_token_secret) {
            return back()->with('error', 'Twitter not configured. Please add API keys in settings.');
        }

        $result = $this->sendTweet($tweet, $twitterSettings);
        
        if ($result['success']) {
            $tweet->update([
                'status' => 'sent',
                'sent_at' => now(),
                'twitter_response' => json_encode($result['response']),
            ]);
            return back()->with('success', 'Tweet sent successfully!');
        }
        
        $tweet->update([
            'status' => 'failed',
            'error_message' => $result['error'],
        ]);
        return back()->with('error', 'Failed to send tweet: ' . $result['error']);
    }

    private function sendTweet($tweet, $settings)
    {
        $apiKey = config('services.twitter.client_id');
        $apiSecret = config('services.twitter.client_secret');
        
        $oauth = new \stdClass();
        $oauth->oauth_token = $settings->oauth_token;
        $oauth->oauth_token_secret = $settings->oauth_token_secret;

        $url = 'https://api.twitter.com/2/tweets';
        
        $headers = [
            'Authorization: OAuth ' . implode(', ', [
                'oauth_token="' . $oauth->oauth_token . '"',
                'oauth_token_secret="' . $oauth->oauth_token_secret . '"',
                'oauth_consumer_key="' . $apiKey . '"',
                'oauth_signature_method="HMAC-SHA1"',
                'oauth_timestamp="' . time() . '"',
                'oauth_nonce="' . bin2hex(random_bytes(16)) . '"',
                'oauth_version="1.0"',
            ]),
            'Content-Type: application/json',
        ];

        $postData = json_encode(['text' => $tweet->content]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return ['success' => true, 'response' => json_decode($response, true)];
        }
        
        return ['success' => false, 'error' => $response];
    }

    // Landing Pages
    public function landingPagesIndex()
    {
        $pages = LandingPage::with(['sequence', 'funnel'])->latest()->paginate(10);
        return view('admin.marketing.landing_pages.index', compact('pages'));
    }

    public function landingPagesCreate()
    {
        try {
            $sequences = \App\Models\EmailSequence::where('is_active', true)->get();
        } catch (\Exception $e) {
            $sequences = [];
        }
        
        try {
            $funnels = \App\Models\Funnel::where('is_active', true)->get();
        } catch (\Exception $e) {
            $funnels = [];
        }
        
        try {
            return view('admin.marketing.landing_pages.create', compact('sequences', 'funnels'));
        } catch (\Exception $e) {
            return 'Error loading view: ' . $e->getMessage() . ' at line ' . $e->getLine();
        }
    }

    public function landingPagesStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'custom_html' => 'required',
        ]);

        $slug = $request->slug ?: Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (LandingPage::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $data = $request->all();
        $data['slug'] = $slug;
        $data['is_active'] = $request->has('is_active');
        $data['show_popup'] = $request->has('show_popup');
        $data['countdown_end'] = $request->countdown_end ?: null;

        LandingPage::create($data);

        return redirect('/admin/marketing/landing-pages')->with('success', 'Landing page created.');
    }

    public function landingPagesEdit(LandingPage $landingPage)
    {
        $sequences = EmailSequence::where('is_active', true)->get();
        $funnels = \App\Models\Funnel::where('is_active', true)->get();
        return view('admin.marketing.landing_pages.edit', compact('landingPage', 'sequences', 'funnels'));
    }

    public function landingPagesUpdate(Request $request, LandingPage $landingPage)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'custom_html' => 'required',
        ]);

        $data = $request->all();
        
        if ($request->slug !== $landingPage->slug) {
            $slug = $request->slug ?: Str::slug($request->title);
            $originalSlug = $slug;
            $count = 1;
            while (LandingPage::where('slug', $slug)->where('id', '!=', $landingPage->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $data['slug'] = $slug;
        }

        $data['is_active'] = $request->has('is_active');
        $data['show_popup'] = $request->has('show_popup');
        $data['countdown_end'] = $request->countdown_end ?: null;

        $landingPage->update($data);

        return redirect('/admin/marketing/landing-pages')->with('success', 'Landing page updated.');
    }

    public function sequencesIndex()
    {
        try {
            $sequences = EmailSequence::with('steps')->orderBy('created_at', 'desc')->paginate(10);
            return view('admin.marketing.sequences.index', compact('sequences'));
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function sequencesCreate()
    {
        return view('admin.marketing.sequences.create');
    }

    public function sequencesStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $seq = EmailSequence::create([
                'name' => $request->name,
                'is_active' => $request->has('is_active'),
            ]);
            \App\Models\Sequence::create([
                'id' => $seq->id,
                'name' => $request->name,
                'is_active' => $request->has('is_active'),
            ]);
        });
        return redirect('/admin/marketing/sequences')->with('success', 'Sequence created.');
    }

    public function sequencesEdit(EmailSequence $sequence)
    {
        return view('admin.marketing.sequences.edit', compact('sequence'));
    }

    public function sequencesUpdate(Request $request, EmailSequence $sequence)
    {
        $request->validate(['name' => 'required|string|max:255']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $sequence) {
            $sequence->update([
                'name' => $request->name,
                'is_active' => $request->has('is_active'),
            ]);
            \App\Models\Sequence::where('id', $sequence->id)->update([
                'name' => $request->name,
                'is_active' => $request->has('is_active'),
            ]);
        });
        return redirect('/admin/marketing/sequences')->with('success', 'Sequence updated.');
    }

    public function sequencesDestroy(EmailSequence $sequence)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($sequence) {
            \App\Models\Sequence::where('id', $sequence->id)->delete();
            $sequence->delete();
        });
        return redirect('/admin/marketing/sequences')->with('success', 'Sequence deleted.');
    }

    public function landingPagesDestroy(LandingPage $landingPage)
    {
        $landingPage->delete();
        return redirect('/admin/marketing/landing-pages')->with('success', 'Landing page deleted.');
    }

    public function leadsIndex()
    {
        try {
            $query = Lead::with(['landingPage', 'sequence']);
            
            if (request('search')) {
                $search = request('search');
                $query->where(function($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }
            
            if (request('status')) {
                $query->where('status', request('status'));
            }
            
            if (request('sequence_id')) {
                $query->where('sequence_id', request('sequence_id'));
            }
            
            if (request('source')) {
                $query->where('source', request('source'));
            }
            
            if (request('tag_id')) {
                $query->whereHas('tags', function($q) {
                    $q->where('tags.id', request('tag_id'));
                });
            }
            
            if (request('min_score')) {
                $query->where('score', '>=', request('min_score'));
            }
            
            $leads = $query->orderBy('created_at', 'desc')->paginate(15);
            $sequences = EmailSequence::where('is_active', true)->get();
            $tags = \App\Models\Tag::all();
            return view('admin.marketing.leads.index', compact('leads', 'sequences', 'tags'));
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function leadsCreate()
    {
        $sequences = EmailSequence::where('is_active', true)->get();
        return view('admin.marketing.leads.create', compact('sequences'));
    }

    public function leadsStore(StoreLeadRequest $request)
    {
        $lead = Lead::create([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'sequence_id' => $request->sequence_id,
            'status' => $request->status ?? 'new',
            'source' => 'admin',
            'score' => $request->score ?? 0,
            'confirmed' => true,
        ]);

        if (file_exists(base_path('trigger_webhook.php'))) {
            require base_path('trigger_webhook.php');
            triggerWebhook('lead_created', [
                'id' => $lead->id,
                'email' => $lead->email,
                'name' => $lead->name,
                'status' => $lead->status
            ]);
        }

        if ($request->sequence_id) {
            $lead->update(['enrolled_at' => now()]);
            $this->enrollLeadInSequence($lead, $request->sequence_id);
        }

        return redirect('/admin/marketing/leads')->with('success', 'Lead added successfully.');
    }

    public function leadsUpdate(UpdateLeadRequest $request, Lead $lead)
    {
        try {
            $oldSequenceId = $lead->sequence_id;
            $newSequenceId = $request->sequence_id ?: null;
            
            $lead->update($request->validated());

            if ($newSequenceId && $newSequenceId != $oldSequenceId) {
                $lead->update(['enrolled_at' => now()]);
                EmailQueue::where('lead_id', $lead->id)->delete();
                $this->enrollLeadInSequence($lead, $newSequenceId);
            }

            return back()->with('success', 'Lead updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating lead: ' . $e->getMessage());
        }
    }

    public function leadsDestroy(Lead $lead)
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $lead->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('success', 'Lead deleted.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', 'Error deleting lead: ' . $e->getMessage());
        }
    }

    public function leadTagsUpdate(Request $request, Lead $lead)
    {
        $tagIds = $request->input('tag_ids', []);
        $lead->tags()->sync($tagIds);
        return back()->with('success', 'Tags updated.');
    }

    public function tagsIndex()
    {
        $tags = \App\Models\Tag::withCount('leads')->get();
        return view('admin.marketing.tags.index', compact('tags'));
    }

    public function tagsStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        
        $slug = \Illuminate\Support\Str::slug($request->name);
        
        \App\Models\Tag::create([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color ?? '#6366f1',
        ]);
        
        return back()->with('success', 'Tag created.');
    }

    public function tagsUpdate(Request $request, \App\Models\Tag $tag)
    {
        $tag->update([
            'name' => $request->name,
            'color' => $request->color ?? $tag->color,
        ]);
        return back()->with('success', 'Tag updated.');
    }

    public function tagsDestroy(\App\Models\Tag $tag)
    {
        $tag->delete();
        return back()->with('success', 'Tag deleted.');
    }

    public function campaignsIndex()
    {
        $campaigns = \App\Models\Campaign::withCount('campaignLeads')->orderBy('created_at', 'desc')->get();
        $sequences = EmailSequence::where('is_active', true)->get();
        return view('admin.marketing.campaigns.index', compact('campaigns', 'sequences'));
    }

    public function campaignsCreate()
    {
        $sequences = EmailSequence::where('is_active', true)->get();
        return view('admin.marketing.campaigns.create', compact('sequences'));
    }

    public function campaignsStore(StoreCampaignRequest $request)
    {        
        \App\Models\Campaign::create([
            'name' => $request->name,
            'description' => $request->description,
            'sequence_ids' => $request->sequence_ids ?? [],
            'status' => $request->status ?? 'draft',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        
        return redirect('/admin/marketing/campaigns')->with('success', 'Campaign created.');
    }

    public function campaignsEdit(\App\Models\Campaign $campaign)
    {
        $sequences = EmailSequence::where('is_active', true)->get();
        return view('admin.marketing.campaigns.edit', compact('campaign', 'sequences'));
    }

    public function campaignsUpdate(UpdateCampaignRequest $request, \App\Models\Campaign $campaign)
    {
        $campaign->update($request->validated());
        
        return redirect('/admin/marketing/campaigns')->with('success', 'Campaign updated.');
    }

    public function campaignsDestroy(\App\Models\Campaign $campaign)
    {
        $campaign->delete();
        return back()->with('success', 'Campaign deleted.');
    }

    public function campaignsEnroll(Request $request, \App\Models\Campaign $campaign)
    {
        $leadIds = $request->input('lead_ids', []);
        
        foreach ($leadIds as $leadId) {
            \App\Models\CampaignLead::firstOrCreate([
                'campaign_id' => $campaign->id,
                'lead_id' => $leadId,
            ]);
            
            $lead = Lead::find($leadId);
            if ($lead && !empty($campaign->sequence_ids)) {
                foreach ($campaign->sequence_ids as $seqId) {
                    $this->enrollLeadInSequence($lead, $seqId);
                }
            }
        }
        
        return back()->with('success', 'Leads enrolled in campaign.');
    }

    public function leadsExport()
    {
        $leads = Lead::all(['email', 'name', 'status', 'created_at']);
        $csv = "Email,Name,Status,Subscribed At\n";
        foreach ($leads as $lead) {
            $csv .= "{$lead->email},{$lead->name},{$lead->status},{$lead->created_at}\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ]);
    }

    public function leadsImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            if (isset($data['email'])) {
                Lead::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'] ?? null,
                        'status' => 'new',
                        'confirmed' => true,
                    ]
                );
                $imported++;
            }
        }
        fclose($handle);

        return back()->with('success', "Imported {$imported} leads.");
    }

    public function emailQueueIndex()
    {
        $emails = EmailQueue::whereHas('lead')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.marketing.email_queue.index', compact('emails'));
    }

    public function emailQueueRetry(EmailQueue $emailQueue)
    {
        $emailQueue->update(['status' => 'pending', 'sent_at' => null]);
        return back()->with('success', 'Email queued for retry.');
    }

    public function stepsStore(Request $request, EmailSequence $sequence)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'delay_days' => 'required|integer|min:0',
        ]);

        $stepOrder = $sequence->steps->max('step_number') + 1 ?? 1;

        SequenceStep::create([
            'sequence_id' => $sequence->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'delay_days' => $request->delay_days,
            'step_order' => $stepOrder,
        ]);

        return back()->with('success', 'Step added to sequence.');
    }

    public function stepsEdit(SequenceStep $sequenceStep)
    {
        return view('admin.marketing.sequences.edit-step', compact('sequenceStep'));
    }

    public function stepsUpdate(Request $request, SequenceStep $sequenceStep)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'delay_days' => 'required|integer|min:0',
        ]);

        $sequenceStep->update($request->only(['subject', 'body', 'delay_days']));

        return back()->with('success', 'Step updated.');
    }

    public function stepsDestroy(SequenceStep $sequenceStep)
    {
        $sequenceStep->delete();
        return back()->with('success', 'Step deleted.');
    }

    public function getEmbedCode(LandingPage $landingPage)
    {
        return view('admin.marketing.landing_pages.embed', compact('landingPage'));
    }

    // Public: Landing Page Display
    public function showLandingPage($slug)
    {
        $page = LandingPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('front.landing_page', compact('page'));
    }

    // Public: Lead Submission
    public function submitLead(Request $request, $slug)
    {
        $page = LandingPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

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

        $lead = Lead::firstOrCreate(
            ['email' => $request->email],
            array_merge([
                'name' => $request->name,
                'landing_page_id' => $page->id,
                'sequence_id' => $page->sequence_id,
                'source' => 'landing_page',
            ], array_filter($utmData, fn($v) => $v !== null))
        );

        if ($page->sequence_id && !$lead->sequence_id) {
            $lead->update([
                'sequence_id' => $page->sequence_id,
                'enrolled_at' => now(),
            ]);
            $this->enrollLeadInSequence($lead, $page->sequence_id);
        } elseif ($page->sequence_id && $lead->sequence_id != $page->sequence_id) {
            $lead->update([
                'sequence_id' => $page->sequence_id,
                'enrolled_at' => now(),
            ]);
            EmailQueue::where('lead_id', $lead->id)->delete();
            $this->enrollLeadInSequence($lead, $page->sequence_id);
        }

        if ($page->funnel_id) {
            $funnel = $page->funnel;
            if ($funnel) {
                $firstStage = $funnel->stages()->orderBy('order')->first();
                
                \App\Models\FunnelLead::updateOrCreate(
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
                    $marketingService = app(\App\Services\Marketing\MarketingService::class);
                    $marketingService->enrollLeadInFunnel($lead, $funnel);
                }
            }
        }

        return back()->with('success', 'Thanks for subscribing!');
    }

    private function enrollLeadInSequence($lead, $sequenceId)
    {
        $sequence = EmailSequence::with('steps')->find($sequenceId);
        if (!$sequence || !$sequence->is_active) {
            return;
        }

        foreach ($sequence->steps as $step) {
            $scheduledTime = now()->addDays($step->delay_days);
            
            EmailQueue::create([
                'lead_id' => $lead->id,
                'sequence_step_id' => $step->id,
                'scheduled_send_time' => $scheduledTime,
                'status' => 'pending',
            ]);
        }
    }

    public function trackOpen($emailQueueId)
    {
        $emailQueue = EmailQueue::find($emailQueueId);
        
        if ($emailQueue) {
            EmailOpen::create([
                'email_queue_id' => $emailQueueId,
                'opened_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            $emailQueue->update([
                'opened' => true,
                'opened_at' => now(),
            ]);
            
            if ($emailQueue->lead_id && $emailQueue->sequence_step_id) {
                $step = SequenceStep::find($emailQueue->sequence_step_id);
                if ($step && $step->sequence) {
                    $sequence = $step->sequence;
                    if ($sequence->funnel_id) {
                        $funnel = Funnel::find($sequence->funnel_id);
                        $lead = Lead::find($emailQueue->lead_id);
                        
                        if ($funnel && $lead && $lead->email) {
                            $pointsPerEmail = $funnel->getDefaultScorePerEmail();
                            $funnelLead = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                                ->where('email', $lead->email)
                                ->first();
                            
                            if ($funnelLead) {
                                $newScore = ($funnelLead->score ?? 0) + $pointsPerEmail;
                                $funnelLead->update([
                                    'score' => $newScore,
                                    'email_opens' => ($funnelLead->email_opens ?? 0) + 1,
                                    'last_activity' => now(),
                                ]);
                                if ($funnel->isLeadHot($newScore)) {
                                    $funnelLead->update(['is_tagged_hot' => true]);
                                }
                            } else {
                                $newScore = $pointsPerEmail;
                                \App\Models\FunnelLead::create([
                                    'funnel_id' => $funnel->id,
                                    'lead_id' => $lead->id,
                                    'email' => $lead->email,
                                    'score' => $newScore,
                                    'email_opens' => 1,
                                    'last_activity' => now(),
                                    'source' => 'email_sequence',
                                    'is_tagged_hot' => $funnel->isLeadHot($newScore),
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))->header('Content-Type', 'image/gif');
    }

    public function trackClick($emailQueueId)
    {
        $emailQueue = EmailQueue::find($emailQueueId);
        
        if ($emailQueue) {
            $emailQueue->update([
                'clicked' => true,
                'clicked_at' => now(),
            ]);
        }
        
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))->header('Content-Type', 'image/gif');
    }

    public function trackAndRedirect(Request $request, $emailQueueId)
    {
        $emailQueue = EmailQueue::find($emailQueueId);
        
        if ($emailQueue) {
            $emailQueue->update([
                'clicked' => true,
                'clicked_at' => now(),
            ]);
        }
        
        $url = $request->query('url');
        if ($url) {
            return redirect(urldecode($url));
        }
        
        return redirect('/');
    }

    public function twitterSettings()
    {
        $settings = TwitterSetting::first() ?: new TwitterSetting();
        return view('admin.marketing.twitter_settings', compact('settings'));
    }

    public function twitterSettingsUpdate(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'oauth_token' => 'nullable|string',
            'oauth_token_secret' => 'nullable|string',
        ]);

        $settings = TwitterSetting::first() ?: new TwitterSetting();
        $settings->client_id = $request->client_id;
        $settings->client_secret = $request->client_secret;
        $settings->oauth_token = $request->oauth_token;
        $settings->oauth_token_secret = $request->oauth_token_secret;
        $settings->save();

        return redirect()->route('admin.marketing.settings')->with('success', 'Twitter settings updated!');
    }

    public function twitterAuth()
    {
        $settings = TwitterSetting::first();
        if (!$settings || !$settings->client_id || !$settings->client_secret) {
            return redirect()->route('admin.marketing.settings')->with('error', 'Please configure Twitter API credentials first.');
        }
        
        $callbackUrl = route('admin.marketing.twitter.callback');
        $authUrl = 'https://twitter.com/i/oauth2/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $settings->client_id,
            'redirect_uri' => $callbackUrl,
            'scope' => 'tweet.read tweet.write users.read offline.access',
            'state' => csrf_token(),
            'code_challenge' => 'challenge',
            'code_challenge_method' => 'plain',
        ]);
        
        return redirect($authUrl);
    }

    public function twitterCallback(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('admin.marketing.settings')->with('error', 'Authorization failed.');
        }
        
        $settings = TwitterSetting::first();
        
        $response = Http::asForm()->post('https://api.twitter.com/2/oauth2/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $settings->client_id,
            'client_secret' => $settings->client_secret,
            'redirect_uri' => route('admin.marketing.twitter.callback'),
            'code_verifier' => 'challenge',
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            $settings->access_token = $data['access_token'];
            $settings->refresh_token = $data['refresh_token'] ?? null;
            $settings->token_type = $data['token_type'] ?? 'Bearer';
            $settings->expires_at = time() + ($data['expires_in'] ?? 7200);
            $settings->save();
            
            return redirect()->route('admin.marketing.settings')->with('success', 'Twitter connected successfully!');
        }
        
        return redirect()->route('admin.marketing.settings')->with('error', 'Failed to connect Twitter.');
    }

    public function newsletterSubscribe(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $lead = Lead::subscribeToNewsletter($request->email, $request->name);
        
        if (!$lead->confirmed) {
            $lead->confirm();
        }
        
        $this->enrollInWelcomeSequence($lead);
        
        if ($request->funnel_id) {
            $funnel = Funnel::find($request->funnel_id);
            if ($funnel) {
                $firstStage = $funnel->stages()->orderBy('order')->first();
                
                \App\Models\FunnelLead::updateOrCreate(
                    [
                        'funnel_id' => $funnel->id,
                        'email' => $request->email,
                    ],
                    [
                        'lead_id' => $lead->id,
                        'stage_id' => $firstStage?->id,
                        'entered_at' => now(),
                        'source' => 'blog_popup',
                    ]
                );

                if ($funnel->welcome_sequence_id) {
                    $marketingService = app(\App\Services\Marketing\MarketingService::class);
                    $marketingService->enrollLeadInFunnel($lead, $funnel);
                }
            }
        }
        
        return back()->with('success', 'Welcome! You are subscribed to our newsletter.');
    }

    protected function sendConfirmationEmail(Lead $lead)
    {
        $apiKey = \App\Models\Setting::get('brevo_api_key');
        if (empty($apiKey) || empty($lead->confirmation_token)) {
            return;
        }

        $confirmUrl = route('newsletter.confirm', $lead->confirmation_token);

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>';
        $html .= '<body style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;">';
        $html .= '<div style="background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:30px;">';
        $html .= '<h1 style="font-size:24px;margin-bottom:20px;">Confirm Your Subscription</h1>';
        $html .= '<p>Thanks for subscribing to our newsletter! Please confirm your email address by clicking the button below:</p>';
        $html .= '<div style="margin:30px 0;text-align:center;">';
        $html .= '<a href="' . $confirmUrl . '" style="display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:600;">Confirm Subscription</a>';
        $html .= '</div>';
        $html .= '<p style="color:#666;font-size:14px;">Or copy and paste this link into your browser:</p>';
        $html .= '<p style="color:#2563eb;font-size:14px;word-break:break-all;">' . $confirmUrl . '</p>';
        $html .= '<hr style="border:none;border-top:1px solid #e5e5e5;margin:30px 0;">';
        $html .= '<p style="color:#666;font-size:12px;">If you didn\'t subscribe, you can ignore this email.</p>';
        $html .= '</div></body></html>';

        $fromEmail = \App\Models\Setting::get('mail_from_address', 'campaigns@joala.com.ng');
        $fromName = \App\Models\Setting::get('mail_from_name', 'JoAla');

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
            "to" => [["email" => $lead->email, "name" => $lead->name ?? '']],
            "subject" => "Confirm your newsletter subscription",
            "htmlContent" => $html,
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_exec($ch);
        curl_close($ch);
    }

    public function newsletterConfirm(string $token)
    {
        $lead = Lead::where('confirmation_token', $token)->first();
        
        if (!$lead) {
            return redirect('/')->with('error', 'Invalid confirmation token.');
        }
        
        $lead->confirm();

        $this->enrollInWelcomeSequence($lead);
        
        return redirect('/')->with('success', 'You are now subscribed to the newsletter!');
    }

    private function enrollInWelcomeSequence(Lead $lead)
    {
        if ($lead->sequence_id) {
            return;
        }

        try {
            $welcomeSequence = EmailSequence::where('name', 'Welcome Sequence')
                ->where('is_active', true)
                ->first();
            if ($welcomeSequence) {
                app(\App\Services\MarketingService::class)->enrollLeadInSequence($lead, $welcomeSequence->id);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to enroll in welcome sequence: ' . $e->getMessage());
        }
    }

    public function newsletterUnsubscribe(string $token)
    {
        $lead = Lead::where('confirmation_token', $token)->first();
        
        if (!$lead) {
            return redirect('/')->with('error', 'Invalid unsubscribe token.');
        }
        
        $lead->unsubscribe();
        
        return redirect('/')->with('success', 'You have been unsubscribed.');
    }

    public function newsletterIndex()
    {
        $subscribers = Lead::where('is_newsletter', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.marketing.newsletter.index', compact('subscribers'));
    }

    public function newsletterExport()
    {
        $subscribers = Lead::where('is_newsletter', true)
            ->where('confirmed', true)
            ->get(['email', 'name', 'created_at']);
            
        $csv = "Email,Name,Subscribed At\n";
        foreach ($subscribers as $sub) {
            $csv .= "{$sub->email},{$sub->name},{$sub->created_at}\n";
        }
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="newsletter_subscribers.csv"',
        ]);
    }

    public function newsletterDestroy(Lead $lead)
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $lead->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('success', 'Subscriber removed.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', 'Error removing subscriber: ' . $e->getMessage());
        }
    }

    public function newsletterSend(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'scope' => 'required|in:all,confirmed,leads'
        ]);

        $pdo = db_pdo();
        
        if ($request->scope === 'all') {
            $stmt = $pdo->query("SELECT email FROM leads WHERE is_newsletter = 1 AND confirmed = 1");
        } elseif ($request->scope === 'confirmed') {
            $stmt = $pdo->query("SELECT email FROM leads WHERE is_newsletter = 1 AND confirmed = 1");
        } else {
            $stmt = $pdo->query("SELECT email FROM leads WHERE is_newsletter = 1");
        }
        
        $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $insertStmt = $pdo->prepare("INSERT INTO email_queue (lead_id, subject, body, status, scheduled_at, created_at) 
            SELECT id, ?, ?, 'pending', NOW(), NOW() FROM leads WHERE is_newsletter = 1 AND confirmed = 1");
        $insertStmt->execute([$request->subject, $request->body]);
        
        $count = count($emails);
        
        return back()->with('success', "Newsletter queued for $count subscribers!");
    }

    // Automation Rules
    public function automationIndex()
    {
        $rules = \App\Models\AutomationRule::with('sequence')->latest()->paginate(15);
        $sequences = \App\Models\EmailSequence::where('is_active', true)->get();
        return view('admin.marketing.automation.index', compact('rules', 'sequences'));
    }

    public function automationStore(StoreAutomationRuleRequest $request)
    {
        $actionConfig = [];
        if ($request->action_type === 'add_tag' || $request->action_type === 'remove_tag') {
            $actionConfig['tag'] = $request->tag_name;
        } elseif ($request->action_type === 'send_email') {
            $actionConfig['subject'] = $request->email_subject;
            $actionConfig['body'] = $request->email_body;
        } elseif ($request->action_type === 'update_score') {
            $actionConfig['score_change'] = $request->score_change;
            $actionConfig['operation'] = $request->score_operation;
        } elseif ($request->action_type === 'webhook') {
            $actionConfig['webhook_url'] = $request->webhook_url;
        }

        \App\Models\AutomationRule::create([
            'name' => $request->name,
            'trigger_type' => $request->trigger_type,
            'trigger_value' => $request->trigger_value,
            'action_type' => $request->action_type,
            'action_sequence_id' => $request->action_sequence_id,
            'action_config' => $actionConfig,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Automation rule created.');
    }

    public function automationUpdate(UpdateAutomationRuleRequest $request, \App\Models\AutomationRule $rule)
    {
        $validated = $request->validated();

        $actionConfig = [];
        if ($request->action_type === 'add_tag' || $request->action_type === 'remove_tag') {
            $actionConfig['tag'] = $request->tag_name;
        } elseif ($request->action_type === 'send_email') {
            $actionConfig['subject'] = $request->email_subject;
            $actionConfig['body'] = $request->email_body;
        } elseif ($request->action_type === 'update_score') {
            $actionConfig['score_change'] = $request->score_change;
            $actionConfig['operation'] = $request->score_operation;
        } elseif ($request->action_type === 'webhook') {
            $actionConfig['webhook_url'] = $request->webhook_url;
        }

        $rule->update(array_merge($validated, ['action_config' => $actionConfig]));

        return back()->with('success', 'Automation rule updated.');
    }

    public function automationDestroy(\App\Models\AutomationRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Automation rule deleted.');
    }

    public function automationToggle(\App\Models\AutomationRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);
        return back()->with('success', 'Rule ' . ($rule->is_active ? 'activated' : 'deactivated') . '.');
    }

    // A/B Testing
    public function abTestsIndex()
    {
        $tests = \App\Models\AbTest::with('step')->latest()->paginate(15);
        $sequences = \App\Models\EmailSequence::where('is_active', true)->get();
        return view('admin.marketing.ab_tests.index', compact('tests', 'sequences'));
    }

    public function abTestsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject_a' => 'required|string|max:500',
            'subject_b' => 'required|string|max:500',
        ]);

        \App\Models\AbTest::create([
            'name' => $request->name,
            'subject_a' => $request->subject_a,
            'subject_b' => $request->subject_b,
            'body_a' => $request->body_a,
            'body_b' => $request->body_b,
            'sequence_step_id' => $request->sequence_step_id,
            'status' => 'draft',
        ]);

        return back()->with('success', 'A/B Test created.');
    }

    public function abTestsStart(\App\Models\AbTest $test)
    {
        $test->update(['status' => 'running']);
        return back()->with('success', 'Test started.');
    }

    public function abTestsStop(\App\Models\AbTest $test)
    {
        $winner = $test->getWinner();
        $test->update([
            'status' => 'completed',
            'winner' => $winner,
        ]);
        return back()->with('success', 'Test completed. Winner: ' . strtoupper($winner ?? 'tie'));
    }

    public function abTestsDestroy(\App\Models\AbTest $test)
    {
        $test->delete();
        return back()->with('success', 'Test deleted.');
    }

    public function abTestsRecordOpen(\App\Models\AbTest $test, string $variant)
    {
        $column = 'opens_' . $variant;
        $test->increment($column);
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))->header('Content-Type', 'image/gif');
    }

    public function abTestsRecordClick(\App\Models\AbTest $test, string $variant)
    {
        $column = 'clicks_' . $variant;
        $test->increment($column);
        return response(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'))->header('Content-Type', 'image/gif');
    }

    // Webhooks
    public function webhooksIndex()
    {
        $webhooks = \App\Models\Webhook::latest()->paginate(15);
        return view('admin.marketing.webhooks.index', compact('webhooks'));
    }

    public function webhooksStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
        ]);

        \App\Models\Webhook::create([
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events ?? ['lead_created'],
            'secret' => $request->secret,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Webhook created.');
    }

    public function webhooksUpdate(Request $request, \App\Models\Webhook $webhook)
    {
        $webhook->update([
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events ?? ['lead_created'],
            'secret' => $request->secret,
        ]);

        return back()->with('success', 'Webhook updated.');
    }

    public function webhooksDestroy(\App\Models\Webhook $webhook)
    {
        $webhook->delete();
        return back()->with('success', 'Webhook deleted.');
    }

    public function webhooksToggle(\App\Models\Webhook $webhook)
    {
        $webhook->update(['is_active' => !$webhook->is_active]);
        return back()->with('success', 'Webhook ' . ($webhook->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function webhooksTest(\App\Models\Webhook $webhook)
    {
        $result = $webhook->fire('test', [
            'test' => true,
            'message' => 'This is a test webhook from JoAla Portfolio',
        ]);

        return back()->with($result ? 'success' : 'error', 
            $result ? 'Test webhook sent successfully!' : 'Test webhook failed');
    }

    // Email Templates
    public function emailTemplatesIndex()
    {
        $templates = \App\Models\EmailTemplate::latest()->paginate(15);
        return view('admin.marketing.email_templates.index', compact('templates'));
    }

    public function emailTemplatesCreate()
    {
        return view('admin.marketing.email_templates.create');
    }

    public function emailTemplatesStore(StoreEmailTemplateRequest $request)
    {
        \App\Models\EmailTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'description' => $request->description,
            'category' => $request->category ?? 'general',
            'is_active' => $request->has('is_active'),
        ]);

        return redirect('/admin/marketing/email-templates')->with('success', 'Template created.');
    }

    public function emailTemplatesEdit(\App\Models\EmailTemplate $template)
    {
        return view('admin.marketing.email_templates.edit', compact('template'));
    }

    public function emailTemplatesUpdate(UpdateEmailTemplateRequest $request, \App\Models\EmailTemplate $template)
    {
        $template->update($request->validated());

        return redirect('/admin/marketing/email-templates')->with('success', 'Template updated.');
    }

    public function emailTemplatesDestroy(\App\Models\EmailTemplate $template)
    {
        $template->delete();
        return redirect('/admin/marketing/email-templates')->with('success', 'Template deleted.');
    }

    public function emailTemplatesToggle(\App\Models\EmailTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', 'Template ' . ($template->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function emailTemplatesDuplicate(\App\Models\EmailTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->save();

        return back()->with('success', 'Template duplicated.');
    }

    public function emailTemplatesPreview(\App\Models\EmailTemplate $template)
    {
        $replacements = [
            '{{name}}' => 'John Doe',
            '{{email}}' => 'john@example.com',
            '{{site_url}}' => url('/'),
            '{{year}}' => date('Y'),
            '{{date}}' => date('F j, Y'),
        ];

        $subject = str_replace(array_keys($replacements), array_values($replacements), $template->subject);
        $body = str_replace(array_keys($replacements), array_values($replacements), $template->body);

        return response($body)->withHeaders(['Content-Type', 'text/html']);
    }

public function emailTemplatesSeedDefaults()
    {
        $defaults = \App\Models\EmailTemplate::getDefaultTemplates();
        
        foreach ($defaults as $template) {
            \App\Models\EmailTemplate::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }

        return back()->with('success', 'Default templates created.');
    }

    // Visual Email Builder
    public function emailBuilderIndex()
    {
        $templates = \App\Models\EmailTemplate::where('is_active', true)->get();
        return view('admin.marketing.email_builder.index', compact('templates'));
    }

    public function emailBuilderCreate()
    {
        $templates = \App\Models\EmailTemplate::where('is_active', true)->get();
        return view('admin.marketing.email_builder.create', compact('templates'));
    }

    public function emailBuilderStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'template_data' => 'required|json',
        ]);

        $html = $this->generateEmailHtmlFromTemplate($request->template_data);

        \App\Models\EmailTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $html,
            'description' => $request->description ?? 'Created with builder',
            'category' => 'builder',
            'is_active' => true,
        ]);

        return redirect('/admin/marketing/email-templates')->with('success', 'Template created from builder!');
    }

    protected function generateEmailHtmlFromTemplate(string $templateData): string
    {
        $data = json_decode($templateData, true);
        $blocks = $data['blocks'] ?? [];

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .email-container { background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; overflow: hidden; }
            .header { background: #2563eb; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .button { display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 0; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .image-block { max-width: 100%; height: auto; }
            .divider { border-top: 1px solid #e5e5e5; margin: 20px 0; }
        </style></head><body><div class="email-container">';

        foreach ($blocks as $block) {
            switch ($block['type']) {
                case 'header':
                    $html .= '<div class="header"><h1>' . e($block['content'] ?? 'Welcome') . '</h1></div>';
                    break;
                case 'text':
                    $html .= '<div class="content"><p>' . e($block['content'] ?? '') . '</p></div>';
                    break;
                case 'image':
                    $html .= '<div class="content"><img src="' . e($block['url'] ?? '') . '" class="image-block" alt=""></div>';
                    break;
                case 'button':
                    $html .= '<div class="content"><a href="' . e($block['url'] ?? '#') . '" class="button">' . e($block['text'] ?? 'Click Here') . '</a></div>';
                    break;
                case 'divider':
                    $html .= '<div class="divider"></div>';
                    break;
                case 'columns':
                    $html .= '<div class="content" style="display: flex; gap: 10px;">';
                    foreach ($block['columns'] ?? [] as $col) {
                        $html .= '<div style="flex:1;">' . e($col) . '</div>';
                    }
                    $html .= '</div>';
                    break;
            }
        }

        $html .= '<div class="footer"><p>&copy; ' . date('Y') . ' Joala Ventures</p><p><a href="{{unsubscribe_url}}">Unsubscribe</a></p></div></div></body></html>';

        return $html;
    }

public function emailBuilderPreview(Request $request)
    {
        $html = $this->generateEmailHtmlFromTemplate($request->template_data);
        return response($html)->withHeaders(['Content-Type' => 'text/html']);
    }

    // Lead Scoring System
    public function leadScoringIndex()
    {
        $leads = \App\Models\Lead::orderBy('score', 'desc')->paginate(20);
        $rules = \App\Models\LeadScore::scoringRules();
        return view('admin.marketing.lead_scoring.index', compact('leads', 'rules'));
    }

    public function leadScoringRecalculate(Request $request)
    {
        try {
            $leads = \App\Models\Lead::with(['emails'])->get();
            
            foreach ($leads as $lead) {
                $score = 0;
                
                // Score from email opens
                $opens = \App\Models\EmailOpen::where('lead_id', $lead->id)->count();
                $score += $opens * 5;
                
                // Score from email clicks
                $clicks = \App\Models\EmailQueue::where('lead_id', $lead->id)->where('clicked', true)->count();
                $score += $clicks * 10;
                
                // Score from orders
                if ($lead->orders && $lead->orders->count() > 0) {
                    $score += $lead->orders->count() * 50;
                }
                
                $lead->update(['score' => $score]);
                
                // Log score history
                \App\Models\LeadScore::create([
                    'lead_id' => $lead->id,
                    'event_type' => 'recalculation',
                    'points' => $score,
                    'description' => 'Score recalculated',
                ]);
            }
            
            return back()->with('success', 'Scores recalculated for ' . $leads->count() . ' leads');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

// Segments
    public function segmentsIndex()
    {
        try {
            $segments = \App\Models\Segment::orderBy('created_at', 'desc')->paginate(15);
            return view('admin.marketing.segments.index', compact('segments'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading segments: ' . $e->getMessage());
        }
    }

    public function segmentsCreate()
    {
        return view('admin.marketing.segments.create');
    }

    public function segmentsStore(StoreSegmentRequest $request)
    {
        \App\Models\Segment::create($request->validated());
        return redirect('/admin/marketing/segments')->with('success', 'Segment created.');
    }

    public function segmentsEdit(\App\Models\Segment $segment)
    {
        return view('admin.marketing.segments.edit', compact('segment'));
    }

    public function segmentsUpdate(UpdateSegmentRequest $request, \App\Models\Segment $segment)
    {
        $segment->update($request->validated());
        return redirect('/admin/marketing/segments')->with('success', 'Segment updated.');
    }

    public function segmentsDestroy(\App\Models\Segment $segment)
    {
        $segment->delete();
        return redirect('/admin/marketing/segments')->with('success', 'Segment deleted.');
    }

    public function segmentsSync(\App\Models\Segment $segment)
    {
        $service = app(\App\Services\SegmentService::class);
        $result = $service->syncSegment($segment);
        return back()->with('success', "Segment synced. Added: {$result['added']}, Removed: {$result['removed']}");
    }

    // Analytics Dashboard
    public function analyticsIndex()
    {
        try {
            // Funnel Stats
            $funnel = [
                'total_leads' => \App\Models\Lead::count() ?? 0,
                'active_leads' => \App\Models\Lead::where('status', 'active')->count() ?? 0,
                'newsletter_subs' => \App\Models\Lead::where('is_newsletter', true)->count() ?? 0,
                'orders' => \App\Models\Order::count() ?? 0,
                'revenue' => \App\Models\Order::sum('final_amount') ?? 0,
            ];

            // Email Stats
            $emailStats = [
                'sent' => \App\Models\EmailQueue::where('status', 'sent')->count() ?? 0,
                'delivered' => \App\Models\EmailQueue::whereIn('status', ['sent', 'delivered'])->count() ?? 0,
                'opened' => \App\Models\EmailQueue::where('opened', true)->count() ?? 0,
                'clicked' => \App\Models\EmailQueue::where('clicked', true)->count() ?? 0,
            ];

            $emailStats['open_rate'] = $emailStats['delivered'] > 0 ? round(($emailStats['opened'] / $emailStats['delivered']) * 100, 1) : 0;
            $emailStats['click_rate'] = $emailStats['delivered'] > 0 ? round(($emailStats['clicked'] / $emailStats['delivered']) * 100, 1) : 0;

            // Campaign Stats
            try {
                $campaigns = \App\Models\Campaign::withCount('leads')->get();
            } catch (\Exception $e) {
                $campaigns = collect();
            }
            
            // Lead Sources
            $sources = \App\Models\Lead::selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray();

            return view('admin.marketing.analytics.index', compact('funnel', 'emailStats', 'campaigns', 'sources'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading analytics: ' . $e->getMessage());
        }
    }

    public function analyticsFunnel()
    {
        try {
            $stages = [
                ['name' => 'Total Leads', 'count' => \App\Models\Lead::count() ?? 0],
                ['name' => 'Active Leads', 'count' => \App\Models\Lead::where('status', 'active')->count() ?? 0],
                ['name' => 'Newsletter Subscribers', 'count' => \App\Models\Lead::where('is_newsletter', true)->count() ?? 0],
                ['name' => 'Enrolled in Sequences', 'count' => \App\Models\Lead::whereNotNull('sequence_id')->count() ?? 0],
                ['name' => 'Opened Email (30 days)', 'count' => \App\Models\EmailQueue::where('opened', true)->where('sent_at', '>', now()->subDays(30))->distinct('lead_id')->count('lead_id') ?? 0],
                ['name' => 'Clicked Email (30 days)', 'count' => \App\Models\EmailQueue::where('clicked', true)->where('sent_at', '>', now()->subDays(30))->distinct('lead_id')->count('lead_id') ?? 0],
                ['name' => 'Made Purchase', 'count' => \App\Models\Order::distinct('customer_email')->count('customer_email') ?? 0],
            ];

            return view('admin.marketing.analytics.funnel', compact('stages'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading funnel: ' . $e->getMessage());
        }
    }

    public function analyticsRevenue()
    {
        try {
            $startDate = now()->subDays(90);
            
            // Revenue by source (simplified - just show counts)
            $bySource = [];
            
            // Revenue by campaign
            $campaignRevenue = [];
            try {
                $campaigns = \App\Models\Campaign::all();
                foreach ($campaigns as $campaign) {
                    $revenue = \App\Models\Order::where('campaign_id', $campaign->id)
                        ->where('payment_status', 'success')
                        ->where('created_at', '>', $startDate)
                        ->sum('final_amount') ?? 0;
                    $campaignRevenue[] = [
                        'name' => $campaign->name,
                        'leads' => $campaign->leads_count ?? 0,
                        'revenue' => $revenue,
                    ];
                }
            } catch (\Exception $e) {
                $campaignRevenue = [];
            }

            // Revenue by lead source
            $bySource = [];
            try {
                $leadSources = \App\Models\Lead::where('created_at', '>', $startDate)
                    ->selectRaw('source, COUNT(*) as count')
                    ->groupBy('source')
                    ->get();
                foreach ($leadSources as $source) {
                    $sourceEmails = \App\Models\Lead::where('source', $source->source)
                        ->where('created_at', '>', $startDate)
                        ->pluck('email');
                    $revenue = \App\Models\Order::whereIn('customer_email', $sourceEmails)
                        ->where('payment_status', 'success')
                        ->where('created_at', '>', $startDate)
                        ->sum('final_amount') ?? 0;
                    $bySource[$source->source ?: 'Direct'] = $revenue;
                }
            } catch (\Exception $e) {
                $bySource = [];
            }

            $totalRevenue = \App\Models\Order::where('created_at', '>', $startDate)
                ->where('payment_status', 'success')
                ->sum('final_amount') ?? 0;
            $totalOrders = \App\Models\Order::where('created_at', '>', $startDate)
                ->where('payment_status', 'success')
                ->count() ?? 0;
            $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

            return view('admin.marketing.analytics.revenue', compact('bySource', 'campaignRevenue', 'totalRevenue', 'totalOrders', 'avgOrderValue'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading revenue analytics: ' . $e->getMessage());
        }
    }

    public function analyticsCampaigns()
    {
        try {
            $campaigns = \App\Models\Campaign::withCount('leads')->get();

            $campaignData = [];
            foreach ($campaigns as $campaign) {
                $sent = \App\Models\EmailQueue::where('campaign_id', $campaign->id)->where('status', 'sent')->count() ?? 0;
                $opened = \App\Models\EmailQueue::where('campaign_id', $campaign->id)->where('opened', true)->count() ?? 0;
                $clicked = \App\Models\EmailQueue::where('campaign_id', $campaign->id)->where('clicked', true)->count() ?? 0;
                $revenue = \App\Models\Order::where('campaign_id', $campaign->id)
                    ->where('payment_status', 'success')
                    ->sum('final_amount') ?? 0;

                $campaignData[] = [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'leads' => $campaign->leads_count ?? 0,
                    'sent' => $sent,
                    'opened' => $opened,
                    'clicked' => $clicked,
                    'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 1) : 0,
                    'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 1) : 0,
                    'revenue' => $revenue,
                    'roi' => $sent > 0 ? round(($revenue / $sent) * 100, 2) : 0,
                ];
            }

            return view('admin.marketing.analytics.campaigns', compact('campaignData'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading campaigns analytics: ' . $e->getMessage());
        }
    }

    // CRM - Lead Timeline
    public function leadTimeline(\App\Models\Lead $lead)
    {
        $activities = \App\Models\LeadActivity::where('lead_id', $lead->id)->latest()->paginate(20);
        return view('admin.marketing.crm.timeline', compact('lead', 'activities'));
    }

    public function leadActivityStore(Request $request, \App\Models\Lead $lead)
    {
        $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
        ]);

        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'type' => $request->type,
            'description' => $request->description,
            'metadata' => $request->metadata ? json_decode($request->metadata) : null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Activity added!');
    }

    // CRM - Deals Pipeline
    public function dealsIndex()
    {
        $deals = \App\Models\Deal::with('lead')->latest()->paginate(20);
        
        $stages = \App\Models\Deal::stages();
        $pipeline = [];
        
        foreach ($stages as $stage => $label) {
            $stageDeals = \App\Models\Deal::where('stage', $stage)->get();
            $pipeline[$stage] = [
                'label' => $label,
                'count' => $stageDeals->count(),
                'value' => $stageDeals->sum('value'),
            ];
        }

        return view('admin.marketing.crm.deals', compact('deals', 'pipeline'));
    }

    public function dealsStore(StoreDealRequest $request)
    {
        \App\Models\Deal::create([
            'title' => $request->title,
            'value' => $request->value ?? 0,
            'stage' => $request->stage ?? 'lead',
            'probability' => $request->probability ?? 10,
            'expected_close_date' => $request->expected_close_date,
            'notes' => $request->notes,
            'lead_id' => $request->lead_id,
        ]);

        return back()->with('success', 'Deal created!');
    }

    public function dealsUpdate(UpdateDealRequest $request, \App\Models\Deal $deal)
    {
        $deal->update($request->validated());
        return back()->with('success', 'Deal updated!');
    }

    public function dealsDestroy(\App\Models\Deal $deal)
    {
        $deal->delete();
        return back()->with('success', 'Deal deleted!');
    }

    // CRM - Tasks
    public function tasksIndex()
    {
        $tasks = \App\Models\LeadTask::with('lead')->latest()->paginate(20);
        $overdue = \App\Models\LeadTask::overdue()->count();
        return view('admin.marketing.crm.tasks', compact('tasks', 'overdue'));
    }

    public function tasksStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        \App\Models\LeadTask::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority ?? 'medium',
            'lead_id' => $request->lead_id,
        ]);

        return back()->with('success', 'Task created!');
    }

    public function tasksUpdate(Request $request, \App\Models\LeadTask $task)
    {
        $task->update($request->only(['title', 'description', 'due_date', 'status', 'priority']));
        return back()->with('success', 'Task updated!');
    }

    public function tasksDestroy(\App\Models\LeadTask $task)
    {
        $task->delete();
        return back()->with('success', 'Task deleted!');
    }

    // NotebookLM Content Generation
    public function notebookLmIndex()
    {
        return view('admin.marketing.notebooklm.index');
    }

    public function notebookLmGenerate(Request $request)
    {
        $request->validate([
            'content_type' => 'required|string',
            'topic' => 'required|string',
        ]);

        try {
            $notebookLm = new \App\Services\NotebookLMService(
                new \App\Services\GuardrailsManager(),
                new \App\Services\DNABibleManager()
            );

            $options = [
                'title' => $request->title ?? '',
                'max_length' => $request->max_length ?? 2000,
            ];

            $result = $notebookLm->generateBlogPost($request->topic, $options);

            if ($result['success']) {
                return back()->with('success', 'Content generated successfully!')->with('generated_content', $result['content']);
            }

            return back()->with('error', 'Failed to generate content: ' . ($result['error'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function notebookLmGenerateTweets(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'count' => 'integer|min:1|max:10',
        ]);

        try {
            $notebookLm = new \App\Services\NotebookLMService(
                new \App\Services\GuardrailsManager(),
                new \App\Services\DNABibleManager()
            );

            $result = $notebookLm->generateTweets($request->content, $request->count ?? 5);

            if ($result['success']) {
                return back()->with('success', 'Tweets generated successfully!')->with('generated_tweets', $result['tweets']);
            }

            return back()->with('error', 'Failed to generate tweets');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function notebookLmGenerateSequence(Request $request)
    {
        $request->validate([
            'sequence_name' => 'required|string',
            'steps' => 'required|integer|min:1|max:10',
        ]);

        try {
            $notebookLm = new \App\Services\NotebookLMService(
                new \App\Services\GuardrailsManager(),
                new \App\Services\DNABibleManager()
            );

            $result = $notebookLm->generateEmailSequence($request->sequence_name, $request->steps);

            if ($result['success']) {
                return back()->with('success', 'Sequence generated successfully!')->with('generated_sequence', $result['emails']);
            }

            return back()->with('error', 'Failed to generate sequence');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function notebookLmChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            $notebookLm = new \App\Services\NotebookLMService(
                new \App\Services\GuardrailsManager(),
                new \App\Services\DNABibleManager()
            );

            $result = $notebookLm->chat($request->message, $request->context ?? '');

            if ($result['success']) {
                return back()->with('success', 'Response generated!')->with('chat_response', $result['content']);
            }

            return back()->with('error', 'Failed to get response');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function runMigrations(Request $request)
    {
        if ($request->isMethod('GET')) {
            return view('admin.tools.migrate');
        }

        $secretKey = 'migrate-2026';
        if ($request->input('key') !== $secretKey) {
            return back()->with('error', 'Invalid security key');
        }

        return back()->with('info', 'Migration disabled - run tables manually from cPanel');
    }
    
    public function createFunnelTables()
    {
        // Funnels table
        try {
            \Illuminate\Support\Facades\DB::statement('CREATE TABLE IF NOT EXISTS funnels (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255),
                description TEXT,
                funnel_type VARCHAR(50),
                goal VARCHAR(50),
                is_active TINYINT(1) DEFAULT 1,
                starts_at TIMESTAMP NULL,
                ends_at TIMESTAMP NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )');
        } catch (\Exception $e) { }

        // Funnel stages table
        try {
            \Illuminate\Support\Facades\DB::statement('CREATE TABLE IF NOT EXISTS funnel_stages (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                funnel_id BIGINT UNSIGNED,
                name VARCHAR(255),
                type VARCHAR(50),
                content TEXT,
                `order` INT DEFAULT 0,
                delay_days INT DEFAULT 0,
                is_required TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )');
        } catch (\Exception $e) { }

        return back()->with('success', 'Funnel tables created!');
    }
    
    public function seedProduct(Request $request)
    {
        $secretKey = 'migrate-2026';
        if ($request->input('key') !== $secretKey) {
            return back()->with('error', 'Invalid security key');
        }

        try {
            $exists = \Illuminate\Support\Facades\DB::table('products')
                ->where('title', 'LIKE', '%Email Sequence Templates%')
                ->first();
            
            if ($exists) {
                return back()->with('info', 'Product already exists!');
            }

            \Illuminate\Support\Facades\DB::table('products')->insert([
                'title' => 'Email Sequence Templates Pack',
                'slug' => 'email-sequence-templates-pack',
                'short_description' => '6 ready-to-use email sequences with 24 tested templates for maximum conversions',
                'description' => "# Email Sequence Templates Pack\n\nStop writing emails from scratch. This comprehensive pack gives you 6 complete email sequences with 24 tested, high-converting templates.\n\n## What's Inside:\n\n**6 Email Sequences:**\n1. Welcome Series (5 emails) - Build relationships from day one\n2. Abandoned Cart (3 emails) - Recover lost sales\n3. Re-engagement (4 emails) - Win back inactive subscribers\n4. Webinar Follow-up (5 emails) - Convert webinar attendees to customers\n5. Product Launch (4 emails) - Launch new products with maximum impact\n6. Thank You & Upsell (3 emails) - Maximize customer lifetime value\n\n## Features:\n- Copy & paste ready templates\n- Easy customization with [placeholders]\n- Industry best practices embedded\n- Pro tips for maximum results\n- Tested subject lines included",
                'type' => 'ebook',
                'price' => 15000.00,
                'sale_price' => 12000.00,
                'file_path' => 'uploads/products/files/email-sequence-templates-pack.html',
                'image' => 'uploads/products/email-templates-cover.svg',
                'is_active' => 1,
                'is_featured' => 1,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Email Sequence Templates Pack product created!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Sales Funnels
    public function funnelsIndex()
    {
        $funnels = Funnel::with('stages')->latest()->paginate(15);
        return view('admin.marketing.funnels.index', compact('funnels'));
    }

    public function funnelsCreate()
    {
        $products = \App\Models\Product::where('is_active', true)->get();
        $services = \App\Models\Service::where('is_active', true)->get();
        return view('admin.marketing.funnels.create', compact('products', 'services'));
    }

    public function funnelsHealthAll()
    {
        $funnels = Funnel::all();
        $results = [];
        foreach ($funnels as $funnel) {
            $issues = [];
            $score = 100;
            if ($funnel->stages->isEmpty()) {
                $issues[] = 'No stages defined';
                $score -= 30;
            }
            if (!$funnel->product_id) {
                $issues[] = 'No product linked';
                $score -= 20;
            }
            if (!$funnel->is_active) {
                $issues[] = 'Funnel is inactive';
                $score -= 10;
            }
            if ($funnel->goal === 'sale' && !$funnel->upsell_enabled) {
                $issues[] = 'Sale funnel without upsell';
                $score -= 15;
            }
            $funnel->update(['health_score' => max(0, $score), 'health_issues' => $issues, 'last_health_check' => now()]);
            $results[] = ['funnel' => $funnel->name, 'score' => $score, 'issues' => $issues];
        }
        return back()->with('success', 'Health check complete for ' . count($funnels) . ' funnels.');
    }

    public function funnelOverview()
    {
        $id = request()->get('id');
        if (!$id) {
            return redirect('/admin/marketing/funnels');
        }
        $funnel = Funnel::with(['stages', 'product'])->find($id);
        if (!$funnel) {
            return redirect('/admin/marketing/funnels');
        }
        return view('admin.marketing.funnels.overview', compact('funnel'));
    }

    public function funnelsStore(StoreFunnelRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = $request->has('is_active');

        $funnel = Funnel::create($data);

        // Auto-create stages based on template type
        $templateStages = $this->getTemplateStages($request->funnel_type);
        foreach ($templateStages as $index => $stage) {
            FunnelStage::create([
                'funnel_id' => $funnel->id,
                'name' => $stage['name'],
                'type' => $stage['type'],
                'order' => $index,
                'delay_days' => $stage['delay_days'] ?? 0,
            ]);
        }

        return redirect('/admin/marketing/funnels/' . $funnel->id . '/edit')->with('success', 'Funnel created with template stages!');
    }

    private function getTemplateStages($type)
    {
        $templates = [
            'lead_magnet' => [
                ['name' => 'Landing Page', 'type' => 'landing'],
                ['name' => 'Free Download', 'type' => 'landing'],
                ['name' => 'Welcome Email', 'type' => 'email', 'delay_days' => 0],
                ['name' => 'Follow-up Sequence', 'type' => 'email', 'delay_days' => 3],
            ],
            'webinar' => [
                ['name' => 'Registration Page', 'type' => 'landing'],
                ['name' => 'Reminder Email 1', 'type' => 'email', 'delay_days' => 1],
                ['name' => 'Reminder Email 2', 'type' => 'email', 'delay_days' => 0],
                ['name' => 'Webinar Page', 'type' => 'sales_page'],
                ['name' => 'Replay + Offer', 'type' => 'sales_page', 'delay_days' => 1],
            ],
            'product_launch' => [
                ['name' => 'Teaser Page', 'type' => 'landing'],
                ['name' => 'Preview Page', 'type' => 'landing'],
                ['name' => 'Launch Email', 'type' => 'email', 'delay_days' => 0],
                ['name' => 'Sales Page', 'type' => 'sales_page'],
                ['name' => 'Thank You', 'type' => 'thank_you'],
            ],
            'tripwire' => [
                ['name' => 'Landing Page', 'type' => 'landing'],
                ['name' => 'Checkout', 'type' => 'checkout'],
                ['name' => 'Upsell Page', 'type' => 'upsell', 'delay_days' => 300],
                ['name' => 'Thank You', 'type' => 'thank_you'],
            ],
            'free_shipping' => [
                ['name' => 'Landing Page', 'type' => 'landing'],
                ['name' => 'Checkout', 'type' => 'checkout'],
                ['name' => 'Shipping Email', 'type' => 'email', 'delay_days' => 1],
                ['name' => 'Upsell', 'type' => 'upsell', 'delay_days' => 1],
            ],
            'vsl_sales' => [
                ['name' => 'Video Sales Letter', 'type' => 'sales_page'],
                ['name' => 'Checkout', 'type' => 'checkout'],
                ['name' => 'Thank You', 'type' => 'thank_you'],
            ],
        ];

        return $templates[$type] ?? [];
    }
    
public function updateFunnelProduct(Request $request, Funnel $funnel)
    {
        $funnel->update([
            'product_id' => $request->product_id,
            'service_id' => $request->service_id,
            'goal' => $request->goal,
        ]);
        return back()->with('success', 'Product linked to funnel!');
    }

    public function trackFunnelStage(Request $request, Funnel $funnel, FunnelStage $stage)
    {
        $email = $request->query('email');
        
        $pointsPerPage = $funnel->score_per_page ?? 5;
        
$existingLead = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->where('email', $email)->first();

        $isNew = !$existingLead;

        $pointsPerPage = $funnel->score_per_page ?? 5;
        $newScore = $pointsPerPage;
        if ($existingLead) {
            $newScore = ($existingLead->score ?? 0) + $pointsPerPage;
        }
        
        $leadData = [
            'entered_at' => $existingLead ? $existingLead->entered_at : now(),
            'last_activity' => now(),
            'score' => $newScore,
            'stage_id' => $stage->id,
            'times_visited' => ($existingLead->times_visited ?? 0) + 1,
            'pages_viewed' => ($existingLead->pages_viewed ?? 0) + 1,
        ];

        $funnelLead = \App\Models\FunnelLead::updateOrCreate(
            ['funnel_id' => $funnel->id, 'email' => $email],
            $leadData
        );

        if ($isNew && $email) {
            $lead = Lead::where('email', $email)->first();
            if ($lead && ($funnel->welcome_sequence_id || $funnel->followup_sequence_id)) {
                $marketingService = app(\App\Services\Marketing\MarketingService::class);
                $marketingService->enrollLeadInFunnel($lead, $funnel);
            }
        }
        
        // Check if lead should be tagged as hot
        if ($email && $funnel->isLeadHot($newScore)) {
            \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                ->where('email', $email)
                ->update(['is_tagged_hot' => true]);
        }
        
        if ($stage->type === 'checkout' && $funnel->product_id) {
            return redirect('/store/' . $funnel->product->slug . '?funnel=' . $funnel->id);
        }
        
        if ($stage->type === 'upsell' && $funnel->product_id) {
            return redirect('/store/' . $funnel->product->slug . '?upsell=1&funnel=' . $funnel->id);
        }
        
        if ($stage->content && isset($stage->content['url'])) {
            return redirect($stage->content['url']);
        }
        
        return back();
    }

    public function trackFunnelConversion(Request $request, Funnel $funnel)
    {
        $email = $request->query('email');
        
        if ($email) {
            $pointsPerCheckout = $funnel->score_per_checkout ?? 20;
            
            $lead = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                ->where('email', $email)
                ->first();
            
            if ($lead) {
                $lead->update([
                    'converted' => true,
                    'exited_at' => now(),
                    'score' => ($lead->score ?? 0) + $pointsPerCheckout,
                    'last_activity' => now(),
                ]);
            }
            
            $this->sendConversionNotification($funnel, $email);
        }
        
        return view('front.thank-you');
    }

    public function showFunnel(Funnel $funnel)
    {
        $funnel->load('stages', 'product');
        
        $firstStage = $funnel->stages()->orderBy('order')->first();
        
        if (!$firstStage) {
            return redirect('/');
        }
        
        if ($firstStage->type === 'landing' && !empty($firstStage->content['url'])) {
            return redirect($firstStage->content['url'] . '?funnel=' . $funnel->id);
        }
        
        return view('front.funnel.show', compact('funnel'));
    }
    
    public function showFunnelById($id)
    {
        $funnel = Funnel::findOrFail($id);
        $funnel->load('stages', 'product');
        
        $firstStage = $funnel->stages()->orderBy('order')->first();
        
        if (!$firstStage) {
            return redirect('/');
        }
        
        if ($firstStage->type === 'landing' && !empty($firstStage->content['url'])) {
            return redirect($firstStage->content['url'] . '?funnel=' . $funnel->id);
        }
        
        return view('front.funnel.show', compact('funnel'));
    }

    public function showFunnelThankYou(Request $request, Funnel $funnel)
    {
        $funnel->load('product', 'upsellProduct');
        
        $data = [
            'funnel' => $funnel,
            'title' => $funnel->thank_you_title ?? 'Thank You!',
            'message' => $funnel->thank_you_message ?? 'Your order has been confirmed.',
            'video' => $funnel->thank_you_video,
            'show_upsell' => $funnel->upsell_enabled && $funnel->upsellProduct,
            'upsell_product' => $funnel->upsellProduct,
            'exit_popup' => $funnel->exit_popup_enabled,
            'exit_offer' => $funnel->exit_popup_offer,
            'exit_discount' => $funnel->exit_popup_discount,
        ];
        
        return view('front.funnel.thank-you', $data);
    }

    public function showUpsell(Request $request, Funnel $funnel)
    {
        $funnel->load('product', 'upsellProduct');
        
        if (!$funnel->upsell_enabled || !$funnel->upsellProduct) {
            return redirect('/thank-you');
        }
        
        return view('front.funnel.upsell', compact('funnel'));
    }

    public function acceptUpsell(Request $request, Funnel $funnel)
    {
        $funnel->load('upsellProduct');
        
        // Track upsell conversion
        $email = $request->query('email');
        if ($email) {
            \App\Models\FunnelLead::updateOrCreate(
                ['funnel_id' => $funnel->id, 'email' => $email],
                ['converted' => true, 'exited_at' => now()]
            );
            
            $this->sendConversionNotification($funnel, $email, 'upsell');
        }
        
        // Redirect to upsell product checkout
        if ($funnel->upsellProduct) {
            return redirect('/store/' . $funnel->upsellProduct->slug . '?upsell=1&funnel=' . $funnel->id);
        }
        
        return redirect('/thank-you');
    }

    public function getFunnelPixels(Funnel $funnel)
    {
        return response()->json([
            'facebook' => $funnel->facebook_pixel,
            'google' => $funnel->google_pixel,
        ]);
    }

    private function sendConversionNotification(Funnel $funnel, string $email, string $type = 'sale')
    {
        $convertTime = now();
        $revenue = $type === 'upsell' ? ($funnel->upsellProduct?->price ?? 0) : ($funnel->product?->price ?? 0);
        
        // Send webhook URL if enabled
        if ($funnel->webhook_enabled && $funnel->webhook_url) {
            try {
                \Illuminate\Support\Facades\Http::post($funnel->webhook_url, [
                    'event' => 'funnel_conversion',
                    'funnel' => $funnel->name,
                    'email' => $email,
                    'product' => $funnel->product?->title,
                    'revenue' => $revenue,
                    'time' => $convertTime->toIso8601String(),
                ]);
            } catch (\Exception $e) { }
        }
        
        // Send email notification if enabled OR fallback to default
        $notifyEmail = $funnel->notify_email ?: 'support@joala.com.ng';
        if ($funnel->notify_email || true) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "🎉 New Funnel Conversion!\n\n" .
                    "Funnel: {$funnel->name}\n" .
                    "Email: {$email}\n" .
                    "Product: {$funnel->product?->title}\n" .
                    "Revenue: N" . number_format($revenue) . "\n" .
                    "Time: {$convertTime}"
                , function($message) use ($funnel) {
                    $message->to($funnel->notify_email)
                        ->subject('🎉 New Sale - ' . $funnel->name);
                });
            } catch (\Exception $e) { }
        }
    }

    private function checkAndTagHotLead(Funnel $funnel, string $email, int $newScore): void
    {
        $tagName = $funnel->hot_lead_tag ?? '';
        if (empty($tagName)) {
            return;
        }
        
        $threshold = $funnel->score_hot_threshold ?? 100;
        if ($newScore < $threshold) {
            return;
        }
        
        $funnelLead = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->where('email', $email)
            ->where('is_tagged_hot', '!=', true)
            ->first();
        
        if (!$funnelLead) {
            return;
        }
        
        $lead = \App\Models\Lead::where('email', $email)->first();
        if (!$lead) {
            return;
        }
        
        $tags = array_map('trim', explode(',', $tagName));
        foreach ($tags as $tagName) {
            if (empty($tagName)) {
                continue;
            }
            
            $tag = \App\Models\Tag::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($tagName)],
                ['name' => $tagName, 'color' => 'red']
            );
            
            if (!$lead->tags()->where('tags.id', $tag->id)->exists()) {
                $lead->tags()->attach($tag->id);
                
                \App\Models\LeadActivity::create([
                    'lead_id' => $lead->id,
                    'type' => 'tag_added',
                    'description' => 'Added tag: ' . $tagName . ' (funnel: ' . $funnel->name . ')',
                ]);
            }
        }
        
        $funnelLead->update(['is_tagged_hot' => true]);
    }

public function getFunnelLeads(Request $request, Funnel $funnel)
    {
        $query = \App\Models\FunnelLead::where('funnel_id', $funnel->id);
        
        if ($request->filled('status')) {
            if ($request->status === 'hot') {
                $threshold = $funnel->score_hot_threshold ?? 100;
                $query->where('score', '>=', $threshold);
            } elseif ($request->status === 'warm') {
                $threshold = $funnel->score_hot_threshold ?? 100;
                $query->where('score', '>=', 50)->where('score', '<', $threshold);
            } elseif ($request->status === 'cold') {
                $query->where('score', '<', 50);
            } elseif ($request->status === 'converted') {
                $query->where('converted', true);
            }
        }
        
        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }
        
        $leads = $query->orderBy('last_activity', 'desc')->paginate(20);
        $threshold = $funnel->score_hot_threshold ?? 100;
        
        return view('admin.marketing.funnels.leads', compact('funnel', 'leads', 'threshold'));
    }

    public function getFunnelAnalytics(Funnel $funnel)
    {
        try {
        $totalLeads = \App\Models\FunnelLead::where('funnel_id', $funnel->id)->count();
        $converted = \App\Models\FunnelLead::where('funnel_id', $funnel->id)->where('converted', true)->count();
        $hotThreshold = $funnel->score_hot_threshold ?? 100;

        $productPrice = $funnel->product?->price ?? 0;
        $upsellPrice = $funnel->upsellProduct?->price ?? 0;
        $totalRevenue = $converted * $productPrice;

        $upsellConversions = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->where('converted', true)
            ->where('source', 'upsell')
            ->count();
        $totalRevenue += $upsellConversions * $upsellPrice;

        $avgOrderValue = $converted > 0 ? ($totalRevenue / $converted) : 0;

        $hotLeads = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->where('score', '>=', $hotThreshold)->count();
        $warmLeads = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->where('score', '>=', 50)->where('score', '<', $hotThreshold)->count();
        $coldLeads = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->where('score', '<', 50)->count();

        $avgScore = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->where('score', '>', 0)->avg('score') ?? 0;

        $stagesAnalytics = [];
        foreach ($funnel->stages as $index => $stage) {
            $entered = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                ->where('stage_id', $stage->id)->count();
            $conversionRate = $totalLeads > 0 ? round(($entered / $totalLeads) * 100, 1) : 0;
            $dropoff = $index > 0 && $stagesAnalytics[$index - 1]['entered'] > 0
                ? round((($stagesAnalytics[$index - 1]['entered'] - $entered) / $stagesAnalytics[$index - 1]['entered']) * 100, 1)
                : 0;
            $stagesAnalytics[] = [
                'id' => $stage->id,
                'name' => $stage->name,
                'type' => $stage->type,
                'entered' => $entered,
                'conversion_rate' => $conversionRate,
                'dropoff' => $dropoff,
            ];
        }

        $topSources = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $trendLabels = [];
        $trendLeads = [];
        $trendConversions = [];
        $trendRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('M d');
            $dateStart = now()->subDays($i)->startOfDay();
            $dateEnd = now()->subDays($i)->endOfDay();

            $dayLeads = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                ->whereBetween('entered_at', [$dateStart, $dateEnd])->count();
            $dayConversions = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                ->whereBetween('exited_at', [$dateStart, $dateEnd])->where('converted', true)->count();

            $trendLabels[] = $date;
            $trendLeads[] = $dayLeads;
            $trendConversions[] = $dayConversions;
            $trendRevenue[] = $dayConversions * $productPrice;
        }

return view('admin.marketing.funnels.analytics',
            compact('funnel', 'totalLeads', 'converted', 'stagesAnalytics', 'hotLeads', 'warmLeads', 'coldLeads', 'avgScore', 'hotThreshold', 'trendLabels', 'trendLeads', 'trendConversions', 'trendRevenue', 'totalRevenue', 'avgOrderValue', 'upsellConversions', 'topSources', 'productPrice', 'upsellPrice'));
        } catch (\Throwable $e) {
            return 'ERROR in getFunnelAnalytics: ' . $e->getMessage();
        }
    }

    public function exportFunnelLeads(Funnel $funnel)
    {
        $leads = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
            ->orderBy('entered_at', 'desc')
            ->get();
        
        $filename = $funnel->slug . '-leads-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Status', 'Score', 'Entered At', 'Converted At', 'Source']);
            
            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->email,
                    $lead->converted ? 'Converted' : 'Active',
                    $lead->score ?? 0,
                    $lead->entered_at?->format('Y-m-d H:i'),
                    $lead->exited_at?->format('Y-m-d H:i'),
                    $lead->source ?? 'Direct',
                ]);
            }
            
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    public function runMigration()
    {
        $host = 'localhost';
        $dbname = 'joalacom_joala';
        $user = 'joalacom_joala';
        $pass = 'joala@2025@';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "Connected to database.\n\n";
            
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('funnel_leads', $tables)) {
                echo "Creating funnel_leads table...\n";
                $pdo->exec("CREATE TABLE IF NOT EXISTS `funnel_leads` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `funnel_id` bigint(20) UNSIGNED NULL,
                    `lead_id` bigint(20) UNSIGNED NULL,
                    `stage_id` bigint(20) UNSIGNED NULL,
                    `email` varchar(255) NULL,
                    `source` varchar(255) NULL,
                    `converted` tinyint(1) DEFAULT 0,
                    `entered_at` datetime NULL,
                    `exited_at` datetime NULL,
                    `score` int DEFAULT 0,
                    `last_activity` datetime NULL,
                    `times_visited` int DEFAULT 0,
                    `pages_viewed` int DEFAULT 0,
                    `email_opens` int DEFAULT 0,
                    PRIMARY KEY (`id`),
                    KEY `funnel_id` (`funnel_id`),
                    KEY `lead_id` (`lead_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                echo "Created funnel_leads table.\n";
            } else {
                echo "funnel_leads table already exists.\n";
            }
            
            echo "\nAdding columns to funnels table...\n";
            
            $funnelColumns = [
                'order_bumps' => 'JSON NULL',
                'refund_policy' => "VARCHAR(50) DEFAULT 'days'",
                'refund_period_days' => 'INT DEFAULT 30',
                'affiliate_enabled' => 'TINYINT(1) DEFAULT 0',
                'affiliate_commission' => 'DECIMAL(5,2) DEFAULT 20.00',
                'affiliate_cookie_days' => 'INT DEFAULT 30',
                'score_per_page' => 'INT DEFAULT 5',
                'score_per_email' => 'INT DEFAULT 10',
                'score_per_checkout' => 'INT DEFAULT 20',
                'score_hot_threshold' => 'INT DEFAULT 100',
            ];
            
            foreach ($funnelColumns as $col => $def) {
                try {
                    $pdo->exec("ALTER TABLE `funnels` ADD COLUMN `$col` $def");
                    echo "Added $col\n";
                } catch (PDOException $e) {
                    if (str_contains($e->getMessage(), 'Duplicate')) {
                        echo "Column $col already exists, skipping.\n";
                    } else {
                        echo "Error adding $col: " . $e->getMessage() . "\n";
                    }
                }
            }
            
            echo "\nAdding columns to funnel_leads table...\n";
            
            $leadColumns = [
                'score' => 'INT DEFAULT 0',
                'last_activity' => 'DATETIME NULL',
                'times_visited' => 'INT DEFAULT 0',
                'pages_viewed' => 'INT DEFAULT 0',
                'email_opens' => 'INT DEFAULT 0',
            ];
            
            foreach ($leadColumns as $col => $def) {
                try {
                    $pdo->exec("ALTER TABLE `funnel_leads` ADD COLUMN `$col` $def");
                    echo "Added $col\n";
                } catch (PDOException $e) {
                    if (str_contains($e->getMessage(), 'Duplicate')) {
                        echo "Column $col already exists, skipping.\n";
                    } else {
                        echo "Error adding $col: " . $e->getMessage() . "\n";
                    }
                }
            }
            
            echo "\nDone! Migration complete.\n";
            
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage() . "\n";
        }
    }

    public function funnelsEdit(Funnel $funnel)
    {
        $funnel->load('stages');
        $sequences = EmailSequence::where('is_active', true)->get();
        $products = \App\Models\Product::where('is_active', true)->get();
        $services = \App\Models\Service::where('is_active', true)->get();
        return view('admin.marketing.funnels.edit', compact('funnel', 'sequences', 'products', 'services'));
    }

    public function funnelsUpdate(Request $request, Funnel $funnel)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['webhook_enabled'] = $request->has('webhook_enabled') ? 1 : 0;
        $data['upsell_enabled'] = $request->has('upsell_enabled') ? 1 : 0;
        $data['countdown_enabled'] = $request->has('countdown_enabled') ? 1 : 0;
        $data['exit_popup_enabled'] = $request->has('exit_popup_enabled') ? 1 : 0;
        $data['order_bumps_enabled'] = $request->has('order_bumps_enabled') ? 1 : 0;
        
        if (isset($data['order_bumps']) && is_array($data['order_bumps'])) {
            $data['order_bumps'] = json_encode(array_filter($data['order_bumps'], function($b) {
                return !empty($b['product_id']);
            }));
        }
if (isset($data['automation_workflows'])) {
            if (is_string($data['automation_workflows'])) {
                $decoded = json_decode($data['automation_workflows'], true);
                $data['automation_workflows'] = is_array($decoded) ? $decoded : [];
            } elseif (is_array($data['automation_workflows'])) {
                $data['automation_workflows'] = array_values(array_filter($data['automation_workflows'], function($w) {
                    return !empty($w['type']);
                }));
            }
        } else {
            $data['automation_workflows'] = [];
        }
        
        $funnel->update($data);

        return back()->with('success', 'Funnel updated.');
    }

    public function funnelsDestroy(Funnel $funnel)
    {
        $funnel->stages()->delete();
        $funnel->delete();
        return redirect('/admin/marketing/funnels')->with('success', 'Funnel deleted.');
    }

    public function funnelAbTest(Funnel $funnel)
    {
        $stats = [];
        if ($funnel->ab_testing_enabled && $funnel->ab_variants) {
            foreach ($funnel->ab_variants as $variant => $data) {
                $leads = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                    ->where('ab_variant', $variant)->count();
                $conversions = \App\Models\FunnelLead::where('funnel_id', $funnel->id)
                    ->where('ab_variant', $variant)->where('converted', true)->count();
                $stats[$variant] = [
                    'name' => $data['name'] ?? ucfirst($variant),
                    'visitors' => $leads,
                    'conversions' => $conversions,
                    'conversion_rate' => $leads > 0 ? round(($conversions / $leads) * 100, 1) : 0,
                ];
            }
        }
        return view('admin.marketing.funnels.ab-test', compact('funnel', 'stats'));
    }

    public function funnelAbTestStore(Request $request, Funnel $funnel)
    {
        $funnel->update([
            'ab_testing_enabled' => $request->has('ab_testing_enabled'),
            'ab_variants' => [
                'a' => ['name' => $request->input('variant_a_name', 'Variant A'), 'traffic' => $request->input('variant_a_traffic', 50)],
                'b' => ['name' => $request->input('variant_b_name', 'Variant B'), 'traffic' => $request->input('variant_b_traffic', 50)],
            ],
            'ab_traffic_split' => [
                'a' => (int)$request->input('variant_a_traffic', 50),
                'b' => (int)$request->input('variant_b_traffic', 50),
            ],
            'ab_min_sample_size' => $request->input('ab_min_sample_size', 100),
            'ab_confidence_level' => $request->input('ab_confidence_level', 95),
        ]);
        return back()->with('success', 'A/B test settings saved.');
    }

    public function funnelStagesStore(Request $request, Funnel $funnel)
    {
        $stages = $request->stages ?? [];
        
        $funnel->stages()->delete();
        
        foreach ($stages as $index => $stage) {
            if (!empty($stage['name'])) {
                FunnelStage::create([
                    'funnel_id' => $funnel->id,
                    'name' => $stage['name'],
                    'type' => $stage['type'] ?? 'page',
                    'content' => json_encode(['url' => $stage['content'] ?? '']),
                    'order' => $index,
                    'delay_days' => $stage['delay_days'] ?? 0,
                    'is_required' => $stage['is_required'] ?? false,
                ]);
            }
        }

        return back()->with('success', 'Funnel stages saved.');
    }

    public function funnelsClone(Funnel $funnel)
    {
        $newFunnel = $funnel->replicate();
        $newFunnel->name = $funnel->name . ' (Copy)';
        $newFunnel->slug = Str::slug($newFunnel->name);
        $newFunnel->is_active = false;
        $newFunnel->save();

        foreach ($funnel->stages as $stage) {
            FunnelStage::create([
                'funnel_id' => $newFunnel->id,
                'name' => $stage->name,
                'type' => $stage->type,
                'content' => $stage->content,
                'order' => $stage->order,
                'delay_days' => $stage->delay_days,
                'is_required' => $stage->is_required,
            ]);
        }

        return redirect('/admin/marketing/funnels')->with('success', 'Funnel cloned successfully!');
    }
public function migrateAutomation()
    {
        try {
            // Add automation_workflows to funnels
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE funnels ADD COLUMN automation_workflows JSON NULL');
        } catch (\Exception $e) {}
        
        try {
            // Add workflow_state to funnel_leads
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE funnel_leads ADD COLUMN workflow_state JSON NULL');
        } catch (\Exception $e) {}
        
        try {
            // Add status to funnel_leads
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE funnel_leads ADD COLUMN status VARCHAR(50) DEFAULT 'active'");
        } catch (\Exception $e) {}
        
        return back()->with('success', 'Automation migrations completed!');
    }

    // Health Score
    public function calculateFunnelHealth(Funnel $funnel)
    {
        
        try {
            // Add workflow_state to funnel_leads
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE funnel_leads ADD COLUMN workflow_state JSON NULL');
        } catch (\Exception $e) {}
        
        try {
            // Add status to funnel_leads
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE funnel_leads ADD COLUMN status VARCHAR(50) DEFAULT 'active'");
        } catch (\Exception $e) {}
        
        return back()->with('success', 'Automation migrations completed!');
    }
}