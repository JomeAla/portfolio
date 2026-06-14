<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\ProjectBrief;
use App\Models\Setting;
use Illuminate\Http\Request;

class BriefController extends Controller
{
    public function create()
    {
        $settings = Setting::getAll();
        return view('front.brief.create', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'project_type' => 'required|string',
            'description' => 'required|string|min:20',
            'budget_range' => 'nullable|string',
            'timeline' => 'nullable|string',
        ]);

        $brief = ProjectBrief::create($request->all());

        $lead = Lead::subscribeToNewsletter($brief->email, $brief->name);
        if (!$lead->confirmed) {
            $lead->confirm();
        }
        $this->enrollInWelcomeSequence($lead);

        $this->sendBrevoNotification('brief', $brief);

        return redirect()->route('brief.create')->with('success', 'Project brief submitted successfully! We will contact you soon.');
    }

    protected function enrollInWelcomeSequence($lead)
    {
        if ($lead->sequence_id) {
            return;
        }
        try {
            $welcomeSequence = \App\Models\EmailSequence::where('name', 'Welcome Sequence')
                ->where('is_active', true)->first();
            if ($welcomeSequence) {
                app(\App\Services\MarketingService::class)->enrollLeadInSequence($lead, $welcomeSequence->id);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Brief: Failed to enroll in welcome sequence: ' . $e->getMessage());
        }
    }

    protected function sendBrevoNotification($type, $record)
    {
        try {
            $apiKey = Setting::get('brevo_api_key');
            if (empty($apiKey)) {
                return;
            }
            $adminEmail = Setting::get('contact_email', 'jomealawuru@hotmail.com');
            $fromEmail = Setting::get('mail_from_address', 'noreply@joala.com.ng');
            $fromName = Setting::get('mail_from_name', 'JoAla');

            if ($type === 'brief') {
                $subject = 'New Project Brief: ' . $record->project_type;
                $html = '<h2>New Project Brief</h2>'
                    . '<p><strong>Name:</strong> ' . e($record->name) . '</p>'
                    . '<p><strong>Email:</strong> ' . e($record->email) . '</p>'
                    . '<p><strong>Phone:</strong> ' . e($record->phone) . '</p>'
                    . '<p><strong>Project Type:</strong> ' . e($record->project_type) . '</p>'
                    . '<p><strong>Description:</strong></p><p>' . nl2br(e($record->description)) . '</p>';
            } else {
                $subject = 'New Contact: ' . $record->subject;
                $html = '<h2>New Support Ticket</h2>'
                    . '<p><strong>Name:</strong> ' . e($record->name) . '</p>'
                    . '<p><strong>Email:</strong> ' . e($record->email) . '</p>'
                    . '<p><strong>Subject:</strong> ' . e($record->subject) . '</p>'
                    . '<p><strong>Message:</strong></p><p>' . nl2br(e($record->message)) . '</p>';
            }

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
                "subject" => $subject,
                "htmlContent" => $html,
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
        }
    }
}