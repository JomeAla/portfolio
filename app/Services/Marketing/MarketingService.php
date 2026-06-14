<?php

namespace App\Services\Marketing;

use App\Models\BlogPost;
use App\Models\Lead;
use App\Models\EmailSequence;
use App\Models\SequenceStep;
use App\Models\EmailQueue;
use App\Models\TweetQueue;
use App\Models\TwitterSetting;
use App\Models\LandingPage;
use App\Models\EmailOpen;
use App\Models\Setting;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MarketingService
{
    private ?TwitterSetting $twitterSettings = null;

    public function __construct()
    {
        $this->twitterSettings = TwitterSetting::first();
    }

    public function getSmtpConfig()
    {
        $settings = \App\Models\Setting::pluck('value', 'key');
        return [
            'host' => $settings['smtp_host'] ?? env('MAIL_HOST'),
            'port' => $settings['smtp_port'] ?? env('MAIL_PORT', 587),
            'username' => $settings['smtp_username'] ?? env('MAIL_USERNAME'),
            'password' => $settings['smtp_password'] ?? env('MAIL_PASSWORD'),
            'encryption' => $settings['smtp_encryption'] ?? env('MAIL_ENCRYPTION', 'tls'),
            'from_email' => $settings['smtp_from_email'] ?? env('MAIL_FROM_ADDRESS'),
            'from_name' => $settings['smtp_from_name'] ?? env('MAIL_FROM_NAME'),
        ];
    }

    public function sendEmail($to, $subject, $body, $emailQueueId = null)
    {
        $config = $this->getSmtpConfig();
        
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = $config['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['port'];
            
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            
            $trackingPixel = '';
            if ($emailQueueId) {
                $body = $this->wrapLinksForTracking($body, $emailQueueId);
                $trackingUrl = url('/m/' . $emailQueueId);
                $trackingPixel = '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" alt="">';
            }
            
            $mail->Body = $body . $trackingPixel;
            
            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            Log::error('Email send failed: ' . $mail->ErrorInfo);
            return ['success' => false, 'error' => $mail->ErrorInfo];
        }
    }

    private function wrapLinksForTracking($html, $emailQueueId)
    {
        return preg_replace_callback('/href=["\']([^"\']+)["\']/i', function ($matches) use ($emailQueueId) {
            $originalUrl = $matches[1];
            $scheme = parse_url($originalUrl, PHP_URL_SCHEME);
            if (in_array($scheme, ['http', 'https']) && !str_contains($originalUrl, '/click/') && !str_contains($originalUrl, '/mc/')) {
                $encodedUrl = urlencode($originalUrl);
                return 'href="' . url('/click/' . $emailQueueId . '/' . $encodedUrl) . '"';
            }
            return $matches[0];
        }, $html);
    }

    public function processEmailQueue($batchSize = 20)
    {
        $pendingEmails = EmailQueue::where('status', 'pending')
            ->where('scheduled_send_time', '<=', now())
            ->with(['lead', 'step'])
            ->limit($batchSize)
            ->get();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($pendingEmails as $queueItem) {
            $lead = $queueItem->lead;
            $step = $queueItem->step;
            
            if (!$lead || !$step) {
                $queueItem->update(['status' => 'failed', 'error_message' => 'Missing lead or step']);
                $failed++;
                continue;
            }
            
            $body = $this->processTemplate($step->body, $lead);
            $result = $this->sendEmail($lead->email, $step->subject, $body, $queueItem->id);
            
            if ($result['success']) {
                $queueItem->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $sent++;
            } else {
                $queueItem->update([
                    'status' => 'failed',
                    'error_message' => $result['error'],
                ]);
                $failed++;
            }
            
            usleep(100000);
        }
        
        return ['sent' => $sent, 'failed' => $failed];
    }

    private function processTemplate($template, $lead)
    {
        $replacements = [
            '{{name}}' => $lead->name ?? 'there',
            '{{email}}' => $lead->email,
            '{{date}}' => now()->format('F j, Y'),
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public function enrollLeadInSequence(Lead $lead, int $sequenceId)
    {
        $sequence = EmailSequence::with('steps')->find($sequenceId);
        if (!$sequence || !$sequence->is_active) {
            return;
        }

        \App\Models\Sequence::firstOrCreate(['id' => $sequenceId], [
            'name' => $sequence->name,
            'is_active' => true,
        ]);
        $lead->sequence_id = $sequenceId;
        $lead->enrolled_at = now();
        $lead->save();

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

    private function isLeadEnrolledInSequence($lead, $sequenceId)
    {
        if (!$lead || !$sequenceId) {
            return false;
        }
        
        try {
            return EmailQueue::where('lead_id', $lead->id)
                ->whereHas('step', function($q) use ($sequenceId) {
                    $q->where('sequence_id', $sequenceId);
                })->exists();
        } catch (\Exception $e) {
            \Log::warning('isLeadEnrolledInSequence error: ' . $e->getMessage());
            return false;
        }
    }

    public function enrollLeadInFunnel($lead, $funnel)
    {
        if (!$lead || !$funnel) {
            return;
        }

        $welcomeSequenceId = $funnel->welcome_sequence_id;
        $followupSequenceId = $funnel->followup_sequence_id;

        $alreadyEnrolled = $this->isLeadEnrolledInSequence($lead, $welcomeSequenceId);

        if (!$alreadyEnrolled) {
            $alreadyEnrolled = $this->isLeadEnrolledInSequence($lead, $followupSequenceId);
        }

        if ($alreadyEnrolled) {
            return;
        }

        if ($welcomeSequenceId) {
            $this->enrollLeadInSequence($lead, $welcomeSequenceId);
        }

        if ($followupSequenceId) {
            $this->enrollLeadInSequence($lead, $followupSequenceId);
        }
    }

    public function createLead($email, $name = null, $source = null, $landingPageId = null, $sequenceId = null)
    {
        return $this->createLeadWithUtm($email, $name, $source, $landingPageId, $sequenceId, []);
    }

    public function createLeadWithUtm($email, $name = null, $source = null, $landingPageId = null, $sequenceId = null, array $utmData = [])
    {
        $lead = Lead::firstOrCreate(
            ['email' => $email],
            array_merge([
                'name' => $name,
                'source' => $source,
                'landing_page_id' => $landingPageId,
                'sequence_id' => $sequenceId,
            ], array_filter($utmData, fn($v) => $v !== null))
        );
        
        if ($sequenceId) {
            $this->enrollLeadInSequence($lead, $sequenceId);
        }
        
        return $lead;
    }

    public function captureLead(string $email, ?string $name = null, ?int $landingPageId = null): Lead
    {
        $lead = Lead::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'landing_page_id' => $landingPageId,
            ]
        );

        if ($landingPageId) {
            $landingPage = LandingPage::find($landingPageId);
            if ($landingPage && $landingPage->sequence_id) {
                $this->enrollLeadInSequence($lead, $landingPage->sequence_id);
            }
        }

        return $lead;
    }

    public static function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $title)));
        $originalSlug = $slug;
        $counter = 0;
        
        while (BlogPost::where('slug', $slug)->exists() || LandingPage::where('slug', $slug)->exists()) {
            $counter++;
            $slug = $originalSlug . '-' . $counter;
        }
        
        return $slug;
    }

    public function getTwitterAuthUrl()
    {
        $settings = $this->twitterSettings;
        if (!$settings || !$settings->client_id || !$settings->client_secret) {
            return null;
        }
        
        $redirectUri = url('/marketing/twitter/callback');
        $state = bin2hex(random_bytes(16));
        
        session(['twitter_oauth_state' => $state]);
        
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $settings->client_id,
            'redirect_uri' => $redirectUri,
            'scope' => 'tweet.read tweet.write users.read offline.access',
            'state' => $state,
        ]);
        
        return 'https://twitter.com/i/oauth2/authorize?' . $params;
    }

    public function handleTwitterCallback($code)
    {
        $settings = $this->twitterSettings;
        if (!$settings) {
            return false;
        }
        
        $redirectUri = url('/marketing/twitter/callback');
        
        $response = $this->makeOAuthRequest('https://api.twitter.com/2/oauth2/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $settings->client_id,
            'client_secret' => $settings->client_secret,
        ]);
        
        if (isset($response['access_token'])) {
            $settings->update([
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'] ?? null,
                'token_type' => $response['token_type'] ?? null,
                'expires_at' => now()->addSeconds($response['expires_in'] ?? 7200),
            ]);
            return true;
        }
        
        return false;
    }

    private function makeOAuthRequest($url, $data)
    {
        $settings = $this->twitterSettings;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        
        if ($settings && $settings->client_id && $settings->client_secret) {
            curl_setopt($ch, CURLOPT_USERPWD, $settings->client_id . ':' . $settings->client_secret);
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true) ?? [];
    }

    public function refreshTwitterToken()
    {
        $settings = $this->twitterSettings;
        if (!$settings || !$settings->refresh_token) {
            return false;
        }
        
        $response = $this->makeOAuthRequest('https://api.twitter.com/2/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $settings->refresh_token,
            'client_id' => $settings->client_id,
            'client_secret' => $settings->client_secret,
        ]);
        
        if (isset($response['access_token'])) {
            $settings->update([
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'] ?? $settings->refresh_token,
                'expires_at' => now()->addSeconds($response['expires_in'] ?? 7200),
            ]);
            return true;
        }
        
        return false;
    }

    public function postTweet($content)
    {
        $settings = $this->twitterSettings;
        
        if (!$settings || !$settings->access_token) {
            return ['success' => false, 'error' => 'Twitter not connected'];
        }
        
        if ($settings->expires_at && $settings->expires_at->isPast()) {
            $this->refreshTwitterToken();
            $settings->refresh();
        }
        
        $ch = curl_init('https://api.twitter.com/2/tweets');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $content]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $settings->access_token,
            'Content-Type: application/json',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode === 201 && isset($result['data']['id'])) {
            return ['success' => true, 'tweet_id' => $result['data']['id']];
        }
        
        return ['success' => false, 'error' => $response];
    }

    public function processTweetQueue()
    {
        $pendingTweets = TweetQueue::where('status', 'scheduled')
            ->where('scheduled_send_time', '<=', now())
            ->get();
        
        $sent = 0;
        $failed = 0;
        
        foreach ($pendingTweets as $tweet) {
            $result = $this->postTweet($tweet->content);
            
            if ($result['success']) {
                $tweet->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'twitter_response' => json_encode($result),
                ]);
                $sent++;
            } else {
                $tweet->update([
                    'status' => 'failed',
                    'error_message' => $result['error'],
                ]);
                $failed++;
            }
            
            usleep(500000);
        }
        
        return ['sent' => $sent, 'failed' => $failed];
    }

    public function queueTweetForBlogPost(BlogPost $post)
    {
        $content = $post->title . "\n\n" . url('/blog/' . $post->slug);
        
        return TweetQueue::create([
            'content' => $content,
            'blog_post_id' => $post->id,
            'status' => 'scheduled',
            'scheduled_send_time' => now(),
        ]);
    }

    public function createTweetFromBlogPost(BlogPost $post): ?TweetQueue
    {
        $content = $post->title . ' - ' . url('/blog/' . $post->slug);
        
        if ($post->post_to_twitter) {
            return TweetQueue::create([
                'content' => $content,
                'blog_post_id' => $post->id,
                'status' => 'scheduled',
                'scheduled_send_time' => $post->published_at ?? now(),
            ]);
        }
        
        return null;
    }

    public function recordEmailOpen(int $emailQueueId, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        EmailOpen::create([
            'email_queue_id' => $emailQueueId,
            'opened_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function getDashboardStats()
    {
        $totalLeads = Lead::count();
        $totalEmailsSent = EmailQueue::where('status', 'sent')->count();
        $totalEmailsOpened = EmailOpen::count();
        $openRate = $totalEmailsSent > 0 ? round(($totalEmailsOpened / $totalEmailsSent) * 100, 1) : 0;
        
        $tweetsPublished = TweetQueue::where('status', 'sent')->count();
        
        return [
            'total_leads' => $totalLeads,
            'emails_sent' => $totalEmailsSent,
            'emails_opened' => $totalEmailsOpened,
            'open_rate' => $openRate,
            'tweets_published' => $tweetsPublished,
            'pending_emails' => EmailQueue::where('status', 'pending')->count(),
            'pending_tweets' => TweetQueue::where('status', 'scheduled')->count(),
        ];
    }

    public function getSequenceStats(int $sequenceId): array
    {
        $leads = Lead::where('sequence_id', $sequenceId)->count();
        $emailsSent = EmailQueue::whereHas('step', function ($query) use ($sequenceId) {
            $query->where('sequence_id', $sequenceId);
        })->where('status', 'sent')->count();
        
        $emailsOpened = EmailQueue::whereHas('step', function ($query) use ($sequenceId) {
            $query->where('sequence_id', $sequenceId);
        })->where('status', 'sent')->with('emailOpens')->get()->sum(function ($eq) {
            return $eq->emailOpens->count();
        });
        
        return [
            'leads' => $leads,
            'emails_sent' => $emailsSent,
            'emails_opened' => $emailsOpened,
        ];
    }
}
