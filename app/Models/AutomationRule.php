<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\SlugGenerator;

class AutomationRule extends Model
{
    use SlugGenerator;

    protected static $slugSourceField = 'name';
    
    protected $fillable = [
        'name',
        'trigger_type',
        'trigger_value',
        'action_type',
        'action_config',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'action_config' => 'array',
    ];

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(EmailSequence::class, 'action_sequence_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTrigger($query, string $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    public function executeForLead(Lead $lead): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $config = $this->action_config ?? [];

        switch ($this->action_type) {
            case 'enroll_sequence':
                if ($sequenceId = $config['sequence_id'] ?? null) {
                    $seq = EmailSequence::find($sequenceId);
                    if ($seq) {
                        \App\Models\Sequence::firstOrCreate(['id' => $sequenceId], [
                            'name' => $seq->name,
                            'is_active' => true,
                        ]);
                    }
                    $lead->update(['sequence_id' => $sequenceId]);
                }
                break;
            case 'add_tag':
                if ($tagId = $config['tag_id'] ?? null) {
                    $lead->tags()->syncWithoutDetaching([$tagId]);
                }
                break;
            case 'remove_tag':
                if ($tagId = $config['tag_id'] ?? null) {
                    $lead->tags()->detach($tagId);
                }
                break;
            case 'update_score':
                $points = $config['points'] ?? 0;
                if ($points > 0) {
                    $lead->addScore($points);
                } else {
                    $lead->removeScore(abs($points));
                }
                break;
            case 'notify_admin':
                $lead->increment('score', 1);
                $this->sendAdminNotification($lead);
                break;
        }

        return true;
    }

    public static function triggerTypes(): array
    {
        return [
            'email_opened' => 'Email Opened',
            'email_clicked' => 'Email Clicked',
            'link_clicked' => 'Specific Link Clicked',
            'score_reached' => 'Lead Score Reached',
            'tag_added' => 'Tag Added',
            'page_visited' => 'Page Visited',
            'form_submitted' => 'Form Submitted',
            'campaign_enrolled' => 'Campaign Enrolled',
            'lead_created' => 'New Lead Created',
        ];
    }

    public static function actionTypes(): array
    {
        return [
            'enroll_sequence' => 'Enroll in Sequence',
            'add_tag' => 'Add Tag',
            'remove_tag' => 'Remove Tag',
            'send_email' => 'Send Immediate Email',
            'update_score' => 'Update Lead Score',
            'notify_admin' => 'Notify Admin',
            'webhook' => 'Trigger Webhook',
        ];
    }

    private function sendAdminNotification(Lead $lead): void
    {
        try {
            $apiKey = \App\Models\Setting::get('brevo_api_key');
            if (empty($apiKey)) {
                return;
            }
            $fromEmail = \App\Models\Setting::get('mail_from_address', 'campaigns@joala.com.ng');
            $fromName = \App\Models\Setting::get('mail_from_name', 'JoAla');
            $adminEmail = \App\Models\Setting::get('contact_email', 'jomealawuru@hotmail.com');
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
                "to" => [["email" => $adminEmail, "name" => "Admin"]],
                "subject" => "Automation Rule Triggered: {$this->name}",
                "htmlContent" => "<h2>Automation Rule Triggered</h2>
                    <p><strong>Rule:</strong> {$this->name}</p>
                    <p><strong>Lead:</strong> " . e($lead->name ?? $lead->email) . "</p>
                    <p><strong>Email:</strong> {$lead->email}</p>
                    <p><strong>Action:</strong> {$this->action_type}</p>
                    <p><strong>Time:</strong> " . now() . "</p>",
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Admin notification failed: ' . $e->getMessage());
        }
    }
}