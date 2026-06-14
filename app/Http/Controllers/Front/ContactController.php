<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $settings = Setting::getAll();
        return view('front.contact', compact('settings'));
    }

    public function support(): View
    {
        return view('front.support');
    }

    public function submitSupport(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
        ]);

        $lead = Lead::subscribeToNewsletter($ticket->email, $ticket->name);
        if (!$lead->confirmed) {
            $lead->confirm();
        }
        $this->enrollInWelcomeSequence($lead);

        $this->sendBrevoNotification($ticket);

        return back()->with('success', 'Your message has been sent! We will get back to you shortly.');
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
            \Illuminate\Support\Facades\Log::warning('Contact: Failed to enroll in welcome sequence: ' . $e->getMessage());
        }
    }

    protected function sendBrevoNotification($ticket)
    {
        try {
            $apiKey = Setting::get('brevo_api_key');
            if (empty($apiKey)) {
                return;
            }
            $adminEmail = Setting::get('contact_email', 'jomealawuru@hotmail.com');
            $fromEmail = Setting::get('mail_from_address', 'noreply@joala.com.ng');
            $fromName = Setting::get('mail_from_name', 'JoAla');

            $html = '<h2>New Support Ticket</h2>'
                . '<p><strong>Ticket:</strong> ' . e($ticket->ticket_number) . '</p>'
                . '<p><strong>Name:</strong> ' . e($ticket->name) . '</p>'
                . '<p><strong>Email:</strong> ' . e($ticket->email) . '</p>'
                . '<p><strong>Subject:</strong> ' . e($ticket->subject) . '</p>'
                . '<p><strong>Message:</strong></p><p>' . nl2br(e($ticket->message)) . '</p>';

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
                "subject" => 'New Contact: ' . $ticket->subject,
                "htmlContent" => $html,
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
        }
    }
}
