<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Lead;
use App\Models\LandingPage;
use App\Models\EmailSequence;
use App\Models\SequenceStep;
use App\Models\EmailQueue;
use App\Models\TweetQueue;
use App\Models\EmailOpen;
use App\Models\TwitterSetting;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Carbon\Carbon;

class MarketingService
{
    private $smtpConfig;

    public function __construct()
    {
        $this->smtpConfig = [
            'host' => Setting::get('smtp_host', env('MAIL_HOST')),
            'port' => Setting::get('smtp_port', env('MAIL_PORT', 587)),
            'username' => Setting::get('smtp_username', env('MAIL_USERNAME')),
            'password' => Setting::get('smtp_password', env('MAIL_PASSWORD')),
            'encryption' => Setting::get('smtp_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'from_email' => Setting::get('smtp_from_email', env('MAIL_FROM_ADDRESS')),
            'from_name' => Setting::get('smtp_from_name', env('MAIL_FROM_NAME')),
        ];
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

    public function enrollLeadInSequence(Lead $lead, int $sequenceId): void
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
            $delayDays = $step->delay_days ?? 0;
            $delayHours = $step->delay_hours ?? 0;
            $scheduledTime = Carbon::now()->addDays($delayDays)->addHours($delayHours);
            
            EmailQueue::create([
                'lead_id' => $lead->id,
                'sequence_step_id' => $step->id,
                'scheduled_send_time' => $scheduledTime,
                'status' => 'pending',
            ]);
        }
    }

    public function processEmailQueue(int $batchSize = 20): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];
        
        $emails = EmailQueue::where('status', 'pending')
            ->where('scheduled_send_time', '<=', now())
            ->limit($batchSize)
            ->get();

        foreach ($emails as $email) {
            try {
                $this->sendEmail($email);
                $email->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $results['sent']++;
            } catch (\Exception $e) {
                $email->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
            }
        }

        return $results;
    }

    private function sendEmail(EmailQueue $emailQueue): void
    {
        $lead = $emailQueue->lead;
        $step = $emailQueue->step;
        $sequence = $step->sequence;

        $body = $this->processEmailTemplate($step->body, $lead, $emailQueue->id);
        
        $trackingPixel = '<img src="' . url('/track_email.php?type=open&id=' . $emailQueue->id) . '" width="1" height="1" style="display:none;" />';
        $fullBody = $body . $trackingPixel;

        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = $this->smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpConfig['username'];
            $mail->Password = $this->smtpConfig['password'];
            $mail->SMTPSecure = $this->smtpConfig['encryption'];
            $mail->Port = $this->smtpConfig['port'];
            $mail->setFrom($this->smtpConfig['from_email'], $this->smtpConfig['from_name']);
            $mail->addAddress($lead->email, $lead->name ?? '');
            $mail->isHTML(true);
            $mail->Subject = $step->subject;
            $mail->Body = $fullBody;
            $mail->send();
        } catch (Exception $e) {
            throw new \Exception('SMTP Error: ' . $mail->ErrorInfo);
        }
    }

    private function processEmailTemplate(string $template, Lead $lead, ?int $emailQueueId = null): string
    {
        $replacements = [
            '{{name}}' => $lead->name ?? 'there',
            '{{email}}' => $lead->email,
            '{{unsubscribe}}' => url('/marketing/unsubscribe/' . $lead->id),
        ];

        $body = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        if ($emailQueueId) {
            $clickTrackingPixel = '<img src="' . url('/track_email.php?type=open&id=' . $emailQueueId) . '" width="1" height="1" style="display:none;" />';
            $body .= $clickTrackingPixel;
            
            $body = preg_replace('/href="([^"]+)"/', 'href="' . url('/click_track.php?q=' . $emailQueueId . '&url=') . '" data-track-url="$1"', $body);
            $body = str_replace('data-track-url="$1"', 'data-track-url="' . base64_encode('$1') . '"', $body);
            
            $body = preg_replace_callback(
                '/href="[^"]*\/click_track\.php\?q=' . $emailQueueId . '\&url=([^"]*)"/',
                function($matches) use ($emailQueueId) {
                    return 'href="' . url('/click_track.php?q=' . $emailQueueId . '&url=' . $matches[1]) . '"';
                },
                $body
            );
        }
        
        return $body;
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

    public function processTweetQueue(): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'errors' => []];
        
        $tweets = TweetQueue::where('status', 'scheduled')
            ->where('scheduled_send_time', '<=', now())
            ->get();

        foreach ($tweets as $tweet) {
            try {
                $response = $this->postToTwitter($tweet->content);
                $tweet->update([
                    'status' => 'sent',
                    'twitter_response' => json_encode($response),
                    'sent_at' => now(),
                ]);
                $results['sent']++;
            } catch (\Exception $e) {
                $tweet->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
            }
        }

        return $results;
    }

    public function postToTwitter(string $content): array
    {
        $settings = TwitterSetting::first();
        
        if (!$settings || !$settings->access_token) {
            throw new \Exception('Twitter API not configured');
        }

        $client = new \GuzzleHttp\Client();
        
        $response = $client->post('https://api.twitter.com/2/tweets', [
            'headers' => [
                'Authorization' => 'Bearer ' . $settings->access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => ['text' => $content],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function createTweetFromBlogPost(BlogPost $post): TweetQueue
    {
        $content = $post->title . ' - ' . url('/blog/' . $post->slug);
        
        if ($post->post_to_twitter) {
            return TweetQueue::create([
                'content' => $content,
                'blog_post_id' => $post->id,
                'status' => 'scheduled',
                'scheduled_send_time' => $post->published_at,
            ]);
        }
        
        return null;
    }

    public function getDashboardStats(): array
    {
        $totalLeads = Lead::count();
        $totalEmailsSent = EmailQueue::where('status', 'sent')->count();
        $emailsOpened = EmailOpen::count();
        $openRate = $totalEmailsSent > 0 ? round(($emailsOpened / $totalEmailsSent) * 100, 2) : 0;
        
        $tweetsSent = TweetQueue::where('status', 'sent')->count();
        
        return [
            'total_leads' => $totalLeads,
            'total_emails_sent' => $totalEmailsSent,
            'emails_opened' => $emailsOpened,
            'open_rate' => $openRate,
            'tweets_sent' => $tweetsSent,
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